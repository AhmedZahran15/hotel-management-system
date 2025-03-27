<?php

use App\Http\Controllers\ClientExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:manager'])->prefix('dashboard/manager')->group(function () {
    Route::prefix('receptionists')->name('manager.receptionists.')->group(function () {
        Route::get('clients/export', [ClientExportController::class, 'export'])->name('clients.export');
    });
});
