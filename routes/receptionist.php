<?php

use App\Http\Controllers\BanRecptionistController;
use App\Http\Controllers\ReceptionistController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin|manager'])->prefix('dashboard')->group(function () {
    Route::resource('receptionists', ReceptionistController::class);
    Route::post('/receptionists/{receptionist}/ban', [BanRecptionistController::class, 'ban'])->name('ban');
    Route::post('/receptionists/{receptionist}/unban', [BanRecptionistController::class, 'unban'])->name('unban');
});