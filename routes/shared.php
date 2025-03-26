<?php

use App\Http\Controllers\BanRecptionistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReservationController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('reservations/available', [ReservationController::class, 'availableRooms'])->name('reservations.available');
    Route::resource('reservations', ReservationController::class)->except(['store']);
    Route::resource('receptionists', ReceptionistController::class);
});
Route::middleware(['auth', 'verified', 'role:admin|manager'])->prefix('dashboard/receptionists')->group(function () {
    Route::post('{receptionist}/ban', [BanRecptionistController::class, 'ban'])->name('ban');
    Route::post('{receptionist}/unban', [BanRecptionistController::class, 'unban'])->name('unban');
});
