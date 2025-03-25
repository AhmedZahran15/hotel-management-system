<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Middleware\CheckClientApproval;

// Client Reservation Routes
Route::middleware(['auth', 'verified', 'role:client', CheckClientApproval::class])
    ->prefix('dashboard')->group(function () {
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store');
        Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create_client');
    });

// Payment Routes
Route::get('/dashboard/reservations/payment/success', [ReservationController::class, 'paymentSuccess'])
    ->name('reservations.payment.success');

Route::get('/dashboard/reservations/payment/cancel', [ReservationController::class, 'paymentCancel'])
    ->name('reservations.payment.cancel');
