<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class ReservationService
{
    /**
     * Create a Stripe checkout session for the reservation
     *
     * @param  array  $data
     * @param  Room   $room
     * @return Session
     */
    public function createCheckoutSession(array $data, Room $room)
    {
        // Verify room is available
        if ($room->state !== 'available') {
            throw new \Exception('Room is not available for reservation');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Set room to being_reserved state
            $room->update(['state' => 'being_reserved']);

            $session = Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Room {$room->number} Reservation",
                            'description' => "Reservation for {$data['reservation_date']}"
                        ],
                        'unit_amount' => $room->room_price, // Use room_price directly as it's already in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('reservations.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('reservations.payment.cancel'),
                'metadata' => [
                    'room_number' => $room->number,
                    'reservation_date' => $data['reservation_date'],
                    'accompany_number' => $data['accompany_number'],
                    'client_id' => Auth::id()
                ],
                'expires_at' => time() + (30 * 60) // Session expires in 30 minutes
            ]);

            return $session;
        } catch (\Exception $e) {
            // If session creation fails, revert room state
            $room->update(['state' => 'available']);
            
            Log::error('Stripe session creation failed: ' . $e->getMessage());
            throw new \Exception('Payment session creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a reservation after successful payment
     *
     * @param  array  $data
     * @param  Room   $room
     * @return Reservation
     */
    public function createReservation(array $data, Room $room)
    {
        return DB::transaction(function () use ($data, $room) {
            // Verify room state
            if ($room->state !== 'being_reserved') {
                throw new \Exception('Room is no longer available for reservation');
            }

            // Create the reservation record
            $reservation = Reservation::create([
                'reservation_date' => $data['reservation_date'],
                'reservation_price' => $room->room_price, // Use room price from room
                'client_id' => Auth::id(),
                'room_number' => $room->number,
                'payment_status' => 'paid',
                'payment_id' => $data['payment_id'] ?? null,
                'accompany_number' => $data['accompany_number']
            ]);

            // Update room state to occupied
            $room->update([
                'state' => 'occupied'
            ]);

            return $reservation;
        });
    }

    /**
     * Handle payment cancellation
     *
     * @param  Room  $room
     * @return void
     */
    public function handlePaymentCancellation(Room $room)
    {
        if ($room->state === 'being_reserved') {
            $room->update(['state' => 'available']);
        }
    }
}
