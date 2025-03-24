<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ManagerReceptionistController;

Route::prefix('dashboard')->middleware(['auth', 'role:manager'])->group(function () {
    
    Route::prefix('receptionists')->name('manager.receptionists.')->group(function () {
        Route::post('{receptionist}/ban', [ManagerReceptionistController::class, 'ban'])->name('ban');
        Route::post('{receptionist}/unban', [ManagerReceptionistController::class, 'unban'])->name('unban');
    });
});
