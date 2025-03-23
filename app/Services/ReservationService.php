<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Create a new reservation and handle associated logic (like payment).
     *
     * @param  array  $data
     * @param  Room   $room
     * @return Reservation
     */
    public function createReservation(array $data, Room $room)
    {
        // Here you can integrate will be added here

        // Create the reservation record
        $reservation = Reservation::create([
            'reservation_date'  => $data['reservation_date'],
            'reservation_price' => $data['reservation_price'],
            'client_id'         => Auth::id(), 
            'room_number'       => $room->number,
        ]);

        //here update roome status and maybe notify the client
        return $reservation;
    }
}
