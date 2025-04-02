<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

// Public Reservation Routes - Outside the dashboard
Route::get('reservations/make', [ReservationController::class, 'makeReservation'])->name('reservations.make');

// Client Reservation Routes
Route::middleware(['auth', 'verified', 'role:client'])
    ->prefix('dashboard')->group(function () {
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('reservations/rooms/{roomId}', [ReservationController::class, 'create'])->name('reservations.create');
    });
