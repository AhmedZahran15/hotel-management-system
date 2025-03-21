<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class EnsureAdminOrOwnerUser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $client = Client::find($request->route('client')); // Make sure your route model binding uses {client}

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the user is an admin/manager or the owner of the client record
        if ($user->hasAnyRole(['admin', 'manager']) || $user->id === $client->user_id) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
