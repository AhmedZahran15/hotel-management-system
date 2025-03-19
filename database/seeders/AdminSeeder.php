<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Check if the admin user already exists
        // If the admin user already exists, do not create a new one
        if (User::where('email', '=', 'admin@admin.com')->exists()) {
            $this->command->info('Admin user already exists.');
            return;
        }

        # Create the admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            //  'is_admin' => true,
        ]);
    }
}
