<?php

use App\Http\Controllers\ClientController;
use App\Http\Middleware\CheckForAnyPermission;
use App\Http\Middleware\EnsureAdminOrOwnerUser;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

//no authentication needed to register or create account
Route::get('/clients/create', [ClientController::class, 'create']);
Route::post('/clients', [ClientController::class, 'store']);

Route::middleware(['auth'])->group(function () {

    Route::get('/clients', [ClientController::class, 'index'])->middleware([CheckForAnyPermission::class.":view clients,manage clients"]);

    Route::middleware(EnsureAdminOrOwnerUser::class)->group(function () {
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit']);
        Route::put('/clients/{client}', [ClientController::class, 'update']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
        Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
    });



    Route::get('/clients/image/{client_id}', function ($client_id) {

        $client = Client::with("user")->findOrFail( $client_id );
        $authUser = Auth::user();

        if (!$client->img_name) {
            abort(404); // No image found
        }

        if ($authUser->hasRole('admin') ||
            $authUser->hasRole('manager') || $authUser->hasRole('receptionist')||
            $authUser->id === $client->user->id) {
            $path = storage_path("app/private/employees/avatars/{$client->img_name}");
            if (!file_exists($path))
                abort(404);
            return response()->file($path);
        }
        abort(403);
    });
});


