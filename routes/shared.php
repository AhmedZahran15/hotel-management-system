<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReservationController;

Route::prefix('dashboard')->middleware(['role:admin|manager|client'])->group(function () {
    // Available rooms route (specific before resource)
    Route::get('reservations/available', [ReservationController::class, 'availableRooms'])
        ->name('reservations.available');
    Route::get('reservations/rooms/{roomId}', [ReservationController::class, 'create'])
        ->name('reservations.create');
    
    // General resources
    Route::resource('receptionists', ReceptionistController::class);
    Route::resource('reservations', ReservationController::class)
        ->except(['store']); // Exclude store as it's handled in client routes
});
