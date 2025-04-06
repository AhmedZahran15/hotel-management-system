<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Client;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        $room = Room::inRandomOrder()->first() ?? Room::factory()->create();
        $client = Client::inRandomOrder()->first() ?? Client::factory()->create();
        
        $year = $this->faker->numberBetween(2015, 2025);
        $month = $this->faker->numberBetween(1, 12);
        $date = Carbon::create($year, $month, $this->faker->dayOfMonth);
        $maxGuests = max(0, $room->capacity - 1);
        $accompanyingGuests = $this->faker->numberBetween(0, $maxGuests);
        return [
            'reservation_date' => $date,
            'reservation_price' => $this->faker->numberBetween(5000, 20000),
            'client_id' => $client->id,
            'room_number' => $room->number,
            'accompany_number' => $accompanyingGuests,
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'payment_id' => $this->faker->uuid(), 
        ];
    }
}
