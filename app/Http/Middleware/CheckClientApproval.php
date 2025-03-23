<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckClientApproval
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is authenticated and is a client
        if (Auth::check() && Auth::user()->user_type === 'client') {
            $client = Auth::user()->profile;

            // If the client exists but isn't approved (approved_by is null)
            if ($client && $client->approved_by === null) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Use withErrors instead of with
                return to_route('login')
                    ->withErrors(['approval' => 'Your account is pending approval. You will be notified once your account is approved.']);
            }
        }

        return $next($request);
    }
}
