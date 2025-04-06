<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Floor;
use App\Models\Phone;
use App\Models\Reservation;
use App\Models\Room;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(Role_PermissionSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(UserSeeder::class);
        Floor::factory(10)->create();
        Room::factory(100)->create();
        Reservation::factory(50)->create();
        $clients = Client::all();
        foreach ($clients as $client) {
            Phone::factory()->count(rand(2, 3))->create([
                'client_id' => $client->id
            ]);
        }
    }
}
