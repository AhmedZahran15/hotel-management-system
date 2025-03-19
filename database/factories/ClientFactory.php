<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'name'      => $this->faker->name(),
            'country'   => $this->faker->country(),
            'gender'    => $this->faker->randomElement(['Male', 'Female']),
            'approved_by' => null,
            'user_id'   => User::factory(),
        ];
    }
}
