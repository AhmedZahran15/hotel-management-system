<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phone>
 */
class PhoneFactory extends Factory
{
    protected $model = Phone::class;
    public function definition(): array
    {
        return [
            'client_id' => Client::inRandomOrder()->first()->id,
            'phone_number'  => $this->faker->phoneNumber,
        ];
    }
}
