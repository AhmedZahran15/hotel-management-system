<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $action = World::countries();
        if ($action->success) {
            $countries = $action->data;
        }
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'gender' => 'required|in:male,female',
            'country' => 'required|string',
            'profile_picture' => 'required|image|mimes:jpg,jpeg|max:2048',
        ]);

        // Create the user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'country' => $request->country,
            'user_type' => 'client',
        ]);

        // Handle profile picture upload using Spatie Media Library
        if ($request->hasFile('profile_picture')) {
            $user->addMediaFromRequest('profile_picture')
                ->toMediaCollection('profile_picture');
        }

        // Assign the client role to the user if using role-based permissions
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('client');
        }

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
