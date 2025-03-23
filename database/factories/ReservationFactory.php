<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Client;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        $room = Room::inRandomOrder()->first() ?? Room::factory()->create();
        $client = Client::inRandomOrder()->first() ?? Client::factory()->create();

        return [
            'reservation_date'  => $this->faker->dateTimeBetween('-1 year', 'now'),
            'reservation_price' => $this->faker->numberBetween(5000, 20000),
            'client_id'         => $client->id,
            'room_number'       => $room->number,
        ];
    }
}
