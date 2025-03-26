<?php

use App\Http\Controllers\ClientExportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerReceptionistController;

Route::middleware(['auth', 'verified', 'role:manager'])->prefix('dashboard/manager')->group(function () {
    Route::prefix('receptionists')->name('manager.receptionists.')->group(function () {
        Route::post('{receptionist}/ban', [ManagerReceptionistController::class, 'ban'])->name('ban');
        Route::post('{receptionist}/unban', [ManagerReceptionistController::class, 'unban'])->name('unban');
        Route::get('clients/export', [ClientExportController::class, 'export'])->name('clients.export');
    });
});
