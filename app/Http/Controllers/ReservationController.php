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
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
// use Stripe\Session;

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

        // Start the query builder
        $query = QueryBuilder::for(Reservation::class)
            ->with('client')
            ->allowedIncludes(['room', 'client', 'client.approvedBy'])
            ->allowedSorts([
                'id',
                'created_at',
                'updated_at',
                'reservation_price',   // Sort by room price
                'room_number',  // Sort by room number
                'accompany_number',
                AllowedSort::callback('client.name', function ($query, string $direction) {
                    $direction = strtolower($direction) === '1' ? 'desc' : 'asc'; // Ensure it's "asc" or "desc"
                    $query->join('clients', 'reservations.client_id', '=', 'clients.id')
                        ->orderBy('clients.name', $direction);
                }),
            ])
            ->allowedFilters([
                AllowedFilter::partial('room_number'),
                AllowedFilter::partial('reservation_price'),
                AllowedFilter::exact('accompany_number'),
                AllowedFilter::scope('date_between'), // Custom date range filter
                AllowedFilter::callback('client.name', function ($query, $value) {
                    $query->whereHas('client', function ($q) use ($value) {
                        $q->where('name', 'LIKE', "%$value%");
                    });
                }),
                AllowedFilter::callback('room.price', function ($query, $value) {
                    $query->whereHas('room', function ($q) use ($value) {
                        $q->where('price', $value);
                    });
                }),
            ]);

        // Apply role-based restrictions
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

        // Execute the query with pagination
        $reservations = $query->paginate(10);

        // Return response
        return Inertia::render('HotelManagement/ManageReservations', [
            'reservations' => $reservations,
        ]);
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
            $query->where('room_price', '>=', $request->price_min * 100);
        }
        if ($request->has('price_max') && !empty($request->price_max)) {
            $query->where('room_price', '<=', $request->price_max * 100);
        }
        // Use QueryBuilder just for sorting
        $rooms = QueryBuilder::for($query)
            ->allowedSorts(['room_price', 'capacity'])
            ->defaultSort('room_price')
            ->with(['media'])
            ->paginate(9);

        // Transform the rooms using the new client resource
        $formattedRooms = RoomClientResource::collection($rooms);

        return Inertia::render('Reservations/MakeReservation', [
            'rooms' => $formattedRooms,
        ]);
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);
        if ($room->state !== 'available') {
            return redirect()->route('reservations.make')
                ->with('flash.error', 'Room is not available for reservation.');
        }
        return Inertia::render('Reservations/CreateReservation', [
            'room' => (new RoomClientResource($room))->resolve(),
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        try {
            $room = Room::findOrFail($request->room_number);
            $paymentIntent = $this->reservationService->processPayment(
                $request->validated(),
                $room
            );

            if ($paymentIntent->status === 'succeeded') {
                // Return an Inertia redirect response instead of plain JSON
                return redirect()->route('reservations.index')
                    ->with('flash.success', 'Payment processed successfully')
                    ->with('flash.response', [
                        'success' => true,
                        'message' => 'Payment processed successfully',
                        'redirectUrl' => route('reservations.index')
                    ]);
            } else {
                // Return an Inertia response with the payment intent
                return back()->with('flash.response', [
                    'success' => false,
                    'message' => 'Payment requires additional steps',
                    'paymentIntent' => $paymentIntent
                ]);
            }
        } catch (\Exception $e) {
            // Return an Inertia response with the error
            Log::error('Reservation creation failed: ' . $e->getMessage());
            return back()->withErrors([
                'message' => $e->getMessage()
            ]);
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
}
