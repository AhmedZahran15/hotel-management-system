<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Apply the client approval check right after successful authentication
        if (Auth::user()->user_type === 'client') {
            $client = Auth::user()->profile;

            if ($client && $client->approved_by === null) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Flash the error message
                return to_route('login')
                    ->withErrors(['approval' => 'Your account is pending approval. You will be notified once your account is approved.']);
            }
        }
        $user = Auth::user();
        $user->last_login_at = Carbon::now();
        $user->save();
        $request->session()->regenerate();
        // Use the route name directly instead of RouteServiceProvider::HOME
        if(Auth::user()->user_type === 'client') {
            return redirect()->intended(default: route('home'));
        }
        return redirect()->intended(default: route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return to_route('home');
    }
}
