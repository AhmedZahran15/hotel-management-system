<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Floor;
use App\Models\Room;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // // Ensure roles exist
        // Role::firstOrCreate(['name' => 'admin']);
        // Role::firstOrCreate(['name' => 'manager']);
        // Role::firstOrCreate(['name' => 'receptionist']);
        // Role::firstOrCreate(['name' => 'client']);

        // Create an admin user
        $this->call(Role_PermissionSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(UserSeeder::class);
        Floor::factory(10)->create();
        Room::factory(100)->create();

        // // Create 5 managers
        // User::factory()->employee()->count(5)->create()->each(function ($user) use ($admin) {
        //     $user->assignRole('manager');
        //     Employee::factory()->create([
        //         'user_id'         => $user->id,
        //         'creator_user_id' => $admin->id,
        //     ]);
        // });

        // // Create 5 receptionists
        // User::factory()->employee()->count(5)->create()->each(function ($user) use ($admin) {
        //     $user->assignRole('receptionist');
        //     Employee::factory()->create([
        //         'user_id'         => $user->id,
        //         'creator_user_id' => $admin->id,
        //     ]);
        // });

        // // Create 10 clients
        // User::factory()->client()->count(10)->create()->each(function ($user) {
        //     $user->assignRole('client');
        //     Client::factory()->create([
        //         'user_id' => $user->id,
        //     ]);
        // });
    }
}
