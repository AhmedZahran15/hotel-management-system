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
        $roomTypes = ['Deluxe', 'Standard', 'Suite', 'Premium', 'Executive', 'Family'];
        $roomFeatures = ['Ocean View', 'Mountain View', 'City View', 'Garden View'];

        return [
            "number" => fake()->unique()->numberBetween(1000, 9999),
            "capacity" => fake()->numberBetween(1, 5),
            "room_price" =>fake()->randomElement([40,45,50,55,60,65,70,75,80,85,90,95,100])*100,
            "state" => fake()->randomElement(['available', 'maintenance']),
            "floor_number" => Floor::inRandomOrder()->first()->number,
            "creator_user_id" => User::role(['manager', 'admin'])->inRandomOrder()->value('id') ?? 1,
            "title" => fake()->randomElement($roomTypes) . ' ' . fake()->randomElement($roomFeatures) . ' Room',
            "description" => fake()->paragraph(3),
        ];
    }
}
