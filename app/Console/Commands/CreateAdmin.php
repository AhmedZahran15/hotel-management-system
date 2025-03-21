<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin {--name=} {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin account';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            // Get or prompt for inputs
            $name = $this->option('name') ?? $this->ask('Enter admin name');
            $email = $this->option('email') ?? $this->ask('Enter admin email');
            $password = $this->option('password') ?? $this->secret('Enter admin password');

            // Validate the inputs
            if (!$this->validateInput($name, $email, $password)) {
                return 1;
            }

            // Create the admin user
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ])->assignRole('admin');

            $this->info("Admin account created successfully with email: {$email}");
            return 0;
        } catch (Exception $e) {
            $this->error("Failed to create admin: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Validate the admin inputs.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @return bool
     */
    private function validateInput($name, $email, $password)
    {
        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password
            ],
            [
                'name' => 'required|string|min:2',
                'email' => 'required|email|unique:users,email',
                'password' => [
                    'required',
                    'min:6',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
                ]
            ],
            [
                'email.unique' => 'The email has already been taken.',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.'
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return false;
        }

        return true;
    }
}
