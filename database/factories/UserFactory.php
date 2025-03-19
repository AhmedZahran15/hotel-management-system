<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'            => $this->faker->name(),
            'email'           => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'        => static::$password ??= Hash::make('password'),
            'remember_token'  => Str::random(10),
            'creator_user_id' => null,
            'user_type'       => 'client', 
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State for an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'admin',
        ]);
    }

    /**
     * State for a client user.
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'client',
        ]);
    }

    /**
     * State for an employee (manager or receptionist).
     */
    public function employee(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'employee',
        ]);
    }
}
