<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "number" => fake()->unique()->numberBetween(1000, 9999),
            "capacity" => fake()->numberBetween(1, 5),
            "state" => fake()->randomElement(['available', 'maintenance']),
            "floor_number" => Floor::inRandomOrder()->first()->number,
            "creator_user_id" => User::role(['manager', 'receptionist'])->inRandomOrder()->value('id') ?? 1,
        ];
    }
}
