<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Floor>
 */
class FloorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => fake()->unique()->randomElement(['Ground','First','Second','third','fourth','fifth','sixth','seventh','eighth','ninth']),
            "creator_user_id"=> User::role("manager")->inRandomOrder()->value("id"),
        ];
    }
}
