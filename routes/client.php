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
Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

Route::middleware(['auth'])->prefix("dashboard")->group(function () {
    Route::middleware([CheckForAnyPermission::class . ":manage clients create clients"])->get('/clients/create', [ClientController::class, 'create']);
    Route::get('/clients', [ClientController::class, 'index'])->middleware([CheckForAnyPermission::class . ":view clients,manage clients"]);

    // Add routes for client approval - placing it BEFORE the {client} routes to avoid conflicts
    Route::middleware([CheckForAnyPermission::class . ":approve clients,manage clients"])->group(function () {
        Route::get('/clients/pending', [ClientController::class, 'pending'])->name('clients.pending');
        Route::post('/clients/{client}/approve', [ClientController::class, 'approve'])->name('clients.approve');
    });

    Route::middleware(EnsureAdminOrOwnerUser::class)->group(function () {
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit']);
        Route::put('/clients/{client}', [ClientController::class, 'update']);
        Route::get('/clients/{client}', [ClientController::class, 'show']);
        Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
    });
});


