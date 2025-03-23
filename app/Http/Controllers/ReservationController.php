<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    return $reservations;
}

    
    public function create($roomId)
    {
        $room = Room::findOrFail($roomId);
        return view('reservations.create', compact('room'));
    }

    public function store(StoreReservationRequest $request, $roomId)
    {
        $room = Room::findOrFail($roomId);

        try {
            DB::beginTransaction();
            $reservation = $this->reservationService->createReservation($request->validated(), $room);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Reservation failed: ' . $e->getMessage());
        }

        return redirect()->route('reservations.show', $reservation->id)
            ->with('success', 'Reservation created successfully.');
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

    return $reservation;
}

}
