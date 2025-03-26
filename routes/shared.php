<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReservationController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('reservations/available', [ReservationController::class, 'availableRooms'])->name('reservations.available');
    Route::resource('reservations', ReservationController::class)->except(['store']);
    Route::resource('receptionists', ReceptionistController::class);

    Route::get('reservations/payment/success', [ReservationController::class, 'handlePaymentSuccess'])->name('reservations.payment.success');
    Route::get('reservations/payment/cancel', [ReservationController::class, 'handlePaymentCancel'])->name('reservations.payment.cancel');
});
