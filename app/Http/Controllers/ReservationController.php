<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\RoomClientResource;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
// use Stripe\Session;
use Stripe\Checkout\Session;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index()
    {
        $user = Auth::user();
        $query = Reservation::with(['room', 'client']);
        if ($user->hasRole('admin')) {
            $query->with('client.approvedBy:id,name,email');
        } elseif ($user->hasRole('manager')) {
            $query->whereHas('client.approvedBy', function ($q) use ($user) {
                $q->where('creator_user_id', $user->id)
                    ->orWhereHas('createdUsers', function ($subQuery) use ($user) {
                        $subQuery->where('creator_user_id', $user->id);
                    });
            });
        } elseif ($user->hasRole('receptionist')) {
            $query->whereHas('client', function ($q) use ($user) {
                $q->where('approved_by', $user->id);
            });
        } elseif ($user->hasRole('client')) {
            $client = $user->profile;
            if ($client) {
                $query->where('client_id', $client->id);
            } else {
                $query->whereRaw('0=1');
            }
        }

        $reservations = $query->orderBy('id', 'asc')->paginate(10);
        return Inertia::render('HotelManagement/ManageReservations', [
            'reservations' => $reservations,
        ]);
        // return $reservations;
    }

    /**
     * Display a list of available rooms for making a reservation.
     * This is a public-facing method that doesn't require authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function makeReservation(Request $request): Response
    {
        // Get original filters for passing to the view
        $filters = $request->only(['search', 'capacity', 'price_min', 'price_max']);

        // Start with a base query for available rooms
        $query = Room::where('state', 'available');

        // Apply filters manually instead of relying on QueryBuilder's default behavior
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->has('capacity') && !empty($request->capacity)) {
            $query->where('capacity', $request->capacity);
        }
        if ($request->has('price_min') && !empty($request->price_min)) {
            $query->where('room_price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && !empty($request->price_max)) {
            $query->where('room_price', '<=', $request->price_max);
        }
        // Use QueryBuilder just for sorting
        $rooms = QueryBuilder::for($query)
            ->allowedSorts(['room_price', 'capacity', 'name'])
            ->defaultSort('room_price')
            ->with(['media'])
            ->paginate(9);

        // Transform the rooms using the new client resource
        $formattedRooms = RoomClientResource::collection($rooms);

        return Inertia::render('Reservations/MakeReservation', [
            'rooms' => $formattedRooms,
            'filters' => $filters,
        ]);
    }

    public function create($roomId): Response
{
    $room = Room::findOrFail($roomId);
    return Inertia::render('Reservations/Create', [
        'room' => $room,
    ]);
}

    public function store(StoreReservationRequest $request)
    {
        try {
            $room = Room::findOrFail($request->room_number);
            $session = $this->reservationService->createCheckoutSession(
                $request->validated(),
                $room
            );
            return response()->json(['sessionId' => $session->id], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        try {
            // Verify payment with Stripe
            $session = Session::retrieve($request->session_id);

            // Create reservation
            $reservation = $this->reservationService->createReservation([
                'reservation_date' => $session->metadata->reservation_date,
                'payment_id' => $session->payment_intent,
                'accompany_number' => $session->metadata->accompany_number,
                'client_id' => $session->metadata->client_id,
                'room_number' => $session->metadata->room_number,
            ], Room::findOrFail($session->metadata->room_number));

            return redirect()->route('reservations.show', $reservation->id)
                ->with('success', 'Reservation confirmed!');
        } catch (\Exception $e) {
            return redirect()->route('reservations.index')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $reservation = Reservation::with(['room', 'client'])->findOrFail($id);

        //load the reservation made by the client only
        if ($user->hasRole('client')) {
            $client = $user->profile;
            if (!$client || $reservation->client_id !== $client->id) {
                abort(403, 'Unauthorized access.');
            }
        } elseif ($user->hasRole('manager')) {
            $reservation->loadMissing('client.approvedBy');
            if (
                !$reservation->client ||
                !$reservation->client->approvedBy ||
                $reservation->client->approvedBy->creator_user_id !== $user->id
            ) {
                abort(403, 'Unauthorized access.');
            }
        }

        // return Inertia::render('Reservations/Show', [
        //     'reservation' => $reservation,
        // ]);
        return $reservation;
    }

    // List available rooms using an Inertia response
    public function availableRooms()
    {
        $rooms = Room::available()->paginate(10);

        // return Inertia::render('Reservations/Available', [
        //     'rooms' => $rooms,
        // ]);
        return $rooms;
    }

    public function paymentCancel(Request $request)
    {
        try {
            if ($request->session_id) {
                $session = Session::retrieve($request->session_id);
                $room = Room::findOrFail($session->metadata->room_number);
                $this->reservationService->handlePaymentCancellation($room);
            }
            return redirect()->route('reservations.available')
                ->with('info', 'Reservation has been cancelled.');
        } catch (\Exception $e) {
            return redirect()->route('reservations.available')
                ->with('error', 'Something went wrong with cancellation.');
        }
    }
}
