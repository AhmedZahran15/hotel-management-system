<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Support\Str;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //$users =  User::factory(20)->create();
        for($i =0; $i<50 ; $i++ ){
            if($i<5){
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    "creator_user_id" => 1, //admin
                    "user_type" =>"employee"
                ]);
                $user->assignRole("manager");
                Employee::create([
                    "name"=>$user->name,
                    "national_id"=>  fake()->unique()->numerify(str_repeat('#', 14)),
                    "user_id"=>$user->id,
                    "creator_user_id"=> 1,
                    "manager_id"=> null,
                ]);
            }
            else if($i<14){
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    "creator_user_id" => User::role('manager')->inRandomOrder()->value('id'),
                    "user_type" =>"employee"
                ]);
                $user->assignRole("receptionist");
                Employee::create([
                    "name"=>$user->name,
                    "national_id"=>  fake()->unique()->numerify(str_repeat('#', 14)),
                    "user_id"=>$user->id,
                    "creator_user_id"=> User::role('manager')->inRandomOrder()->value('id'),
                    "manager_id"=> Employee::where("manager_id",null)->inRandomOrder()->first()->id,
                ]);
            }
            else{
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    "creator_user_id" => fake()->randomElement([
                        null,
                        User::role(['manager',"receptionist"])->inRandomOrder()->value('id')
                    ]),
                    "user_type" =>"client"
                ]);
                $user->assignRole("client");
                Client::create([
                    "name"=>$user->name,
                    "country"=> fake()->numberBetween(1, 250),
                    "gender"=> fake()->randomElement(["male","female"]),
                    "approved_by"=>fake()->randomElement([
                        null,
                        User::role("admin")->inRandomOrder()->value("id"),
                        User::role("manager")->inRandomOrder()->value("id"),
                        User::role("receptionist")->inRandomOrder()->value("id")
                    ]),
                    "user_id" =>$user->id,
                ]);
            }
        }
    }
}
