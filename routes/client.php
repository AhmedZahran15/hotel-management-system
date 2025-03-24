<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReservationController;
use App\Http\Middleware\CheckClientApproval;
use App\Http\Middleware\CheckForAnyPermission;
use App\Http\Middleware\EnsureAdminOrOwnerUser;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

//no authentication needed to register or create account

Route::middleware(['auth'])->prefix("dashboard")->group(function () {

    Route::resource("/clients", ClientController::class) ->only("index","store","create",)->
    middleware([CheckForAnyPermission::class.":create clients,manage clients,view clients"]);

    Route::resource("/clients", ClientController::class) ->only("edit","update","show",)->
    middleware(EnsureAdminOrOwnerUser::class);

});

Route::middleware(['auth', 'verified', 'role:client', CheckClientApproval::class])->prefix('dashboard')->group(function () {
    Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create_client');
});
