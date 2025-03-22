<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
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
        ]);

        // Create the user first
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'client',
        ]);

        $user->assignRole('client');

        // Handle profile picture upload using Spatie Media Library with unique name
        $extension = $request->file('avatar_image')->getClientOriginalExtension();
        $uniqueFileName = time() . '_' . $user->id . '.' . $extension;
        $user->addMediaFromRequest('avatar_image')
            ->usingFileName($uniqueFileName)
            ->toMediaCollection('avatar_image'); // Use 'avatar_image' as the collection name

        Client::create([
            "name" => $request->name,
            "country" => $request->country,
            "gender" => $request->gender,
            "user_id" => $user->id,
        ]);


        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
