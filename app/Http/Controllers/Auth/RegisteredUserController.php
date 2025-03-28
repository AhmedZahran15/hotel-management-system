<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Phone;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Nnjeim\World\World;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        $countries = Cache::remember('countries', now()->addMonth(), function () {
            $response = World::countries();
            return $response->success ? $response->data : [];
        });
        return Inertia::render('auth/Register', compact('countries'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'avatar_image' => 'required|image|mimes:jpg,jpeg|max:2048',
            'country' => 'required|string',
            'gender' => 'required|string|in:male,female',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'min:8', 'confirmed', Rules\Password::defaults()],
            'phone_number' => 'required|string|regex:/^\+?[0-9]{8,15}$/',
        ]);

        // Create the user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'client',
        ]);

        $user->assignRole('client');

        // Handle profile picture upload using Spatie Media Library
        $user->updateAvatar($request->file('avatar_image'));

        $client = Client::create([
            "name" => $request->name,
            "country" => $request->country,
            "gender" => $request->gender,
            "user_id" => $user->id,
        ]);

        Phone::create([
            'phone_number' => $request->phone_number,
            'client_id' => $client->id,
        ]);

        event(new Registered($user));

        return to_route('login')
            ->with('status', 'Registration successful! Your account is pending approval. You will be notified once your account is approved.');
    }
}
