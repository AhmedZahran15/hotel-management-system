<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'name'            => $this->faker->name(),
            'national_id'     => $this->faker->unique()->numerify('###########'),
            'img_name'        => 'default.jpg',
            'user_id'         => User::factory(), 
            'creator_user_id' => null,
            'manager_id'      => null,
        ];
    }
}
