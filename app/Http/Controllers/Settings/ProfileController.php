<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Nnjeim\World\World;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $countries = Cache::remember('countries', now()->addMonth(), function () {
            $response = World::countries();
            return $response->success ? $response->data : [];
        });
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'countries' => $countries,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if (Auth::check() && Auth::user()->user_type == 'client') {
            $request->validate([
                'avatar_image' => 'sometimes|image|mimes:jpg,jpeg|max:2048',
                'phone_number' => 'required|string|regex:/^\+?[0-9]{8,15}$/',
                'country' => 'required|string|exists:countries,id',
                'gender' => 'required|string|in:male,female',
            ]);
            $request->user()->profile->phones()->where('client_id', $request->user()->profile->id)->delete();
            $request->user()->profile->phones()->create([
                'phone_number' => $request->phone_number,
            ]);
            $request->user()->profile->country = $request->country;
            $request->user()->profile->gender = $request->gender;
            $request->user()->profile->save();
        }
        if ($request->hasFile('avatar_image')) {
            $request->user()->updateAvatar($request->file('avatar_image'));
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
