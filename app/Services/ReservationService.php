<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class ReservationService
{
    /**
     * Process a payment for a reservation using a payment method ID
     *
     * @param  array  $data
     * @param  Room   $room
     * @return PaymentIntent
     */
    public function processPayment(array $data, Room $room)
    {
        // Verify room is available
        if ($room->state !== 'available') {
            throw new \Exception('Room is not available for reservation');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // Set room to being_reserved state
            $room->update(['state' => 'being_reserved']);

            // Get the client ID from the authenticated user's profile
            $user = Auth::user();
            $client = $user->profile; // Assuming the client profile is accessible via 'profile' relation

            if (!$client) {
                throw new \Exception('Client profile not found for the authenticated user');
            }

            // Create payment intent parameters
            $paymentIntentParams = [
                'amount' => $room->room_price, // Room price is already in cents
                'currency' => 'usd',
                'payment_method' => $data['payment_method_id'],
                'confirm' => true, // Confirm the payment immediately
                'description' => "Room {$room->number} Reservation",
                'metadata' => [
                    'room_number' => $room->number,
                    'accompany_number' => $data['accompany_number'],
                    'client_id' => $client->id // Use client ID from profile instead of user ID
                ],
                // Always set automatic payment methods to force card-only payments
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ]
            ];

            // Always add a return URL (use the app URL as fallback)
            $paymentIntentParams['return_url'] = !empty($data['return_url'])
                ? $data['return_url']
                : config('app.url');

            // Create a payment intent with the updated parameters
            $paymentIntent = PaymentIntent::create($paymentIntentParams);

            // If payment succeeded, create the reservation
            if ($paymentIntent->status === 'succeeded') {
                $this->createReservation([
                    'payment_id' => $paymentIntent->id,
                    'accompany_number' => $data['accompany_number'],
                    'client_id' => $client->id, // Use client ID from profile instead of user ID
                    'room_number' => $room->number,
                    'reservation_date' => now(), // Add current date for the reservation
                    'reservation_price' => $room->room_price, // Also include the price
                ], $room);
            }

            return $paymentIntent;
        } catch (\Exception $e) {
            // If payment processing fails, revert room state
            $room->update(['state' => 'available']);

            Log::error('Stripe payment processing failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a reservation record
     *
     * @param  array  $data
     * @param  Room   $room
     * @return Reservation
     */
    public function createReservation(array $data, Room $room)
    {
        return DB::transaction(function () use ($data, $room) {
            // Create the reservation
            $reservation = Reservation::create([
                'payment_id' => $data['payment_id'],
                'accompany_number' => $data['accompany_number'],
                'client_id' => $data['client_id'],
                'room_number' => $data['room_number'],
                'reservation_date' => $data['reservation_date'] ?? now(), // Use provided date or current date
                'reservation_price' => $data['reservation_price'] ?? $room->room_price, // Use provided price or room price
                'payment_status' => 'completed', // Add payment status
            ]);

            // Update room status to reserved
            $room->update(['state' => 'occupied']);

            return $reservation;
        });
    }

    /**
     * Handle payment cancellation
     *
     * @param  Room  $room
     * @return void
     */
}
