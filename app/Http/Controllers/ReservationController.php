<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
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

        //load all reservations for admin
        if ($user->hasRole('admin')) {
            $query->with('client.approvedBy:id,name,email');
        } elseif ($user->hasRole('manager')) {
            //for manager load the reservations by the receptionist created by the manager 
            $query->whereHas('client.approvedBy', function ($q) use ($user) {
                $q->where('creator_user_id', $user->id);
            });
        } elseif ($user->hasRole('client')) {
            //retrive all client reservations if logged is a client
            $client = $user->profile;
            if ($client) {
                $query->where('client_id', $client->id);
            } else {
                $query->whereRaw('0=1');
            }
        }

        $reservations = $query->orderBy('created_at', 'desc')->paginate(10);

        // return Inertia::render('Reservations/Index', [
        //     'reservations' => $reservations,
        // ]);
        return $reservations;
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
