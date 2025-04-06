<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatisticsController;

Route::middleware(['auth', 'verified', 'role:admin|manager|receptionist'])->prefix('dashboard')->group(function () {
    Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::get('statistics/male-female', [StatisticsController::class, 'maleFemaleChart'])->name('statistics.maleFemale');
    Route::get('statistics/revenue/{year?}', [StatisticsController::class, 'revenueChart'])->name('statistics.revenue');
    Route::get('statistics/countries', [StatisticsController::class, 'countriesChart'])->name('statistics.countries');
    Route::get('statistics/top-clients', [StatisticsController::class, 'topClientsChart'])->name('statistics.topClients');
});
