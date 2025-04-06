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
        static $clientIds = null;
        
        if ($clientIds === null) {
            $clientIds = Client::pluck('id')->toArray();
        }
        
        return [
            'client_id' => $this->faker->randomElement($clientIds),
            'phone_number' => $this->faker->unique()->phoneNumber,
        ];
    }
}
