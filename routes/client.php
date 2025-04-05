<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientExportController;
use App\Http\Middleware\CheckForAnyPermission;
use App\Http\Middleware\EnsureAdminOrOwnerUser;
use Illuminate\Support\Facades\Route;

//no authentication needed to register or create account

Route::middleware(['auth'])->prefix("dashboard")->group(function () {

    Route::resource("/clients", ClientController::class)->only("index", "store", "create",)->middleware([CheckForAnyPermission::class . ":create clients,manage clients,view clients"]);

    Route::resource("/clients", ClientController::class)->only("edit", "update", "show","destroy")->middleware(EnsureAdminOrOwnerUser::class);

    Route::get("/approved", [ClientController::class, 'approved'])->middleware([CheckForAnyPermission::class .':manage clients,view clients'])->name("clients.approved");

    Route::middleware(['auth', 'verified', 'role:admin|manager|receptionist'])->group(function () {
        Route::patch('clients/{client}/approve', [ClientController::class, 'approve'])->name('clients.approve');
    });

    Route::middleware(['verified', 'role:admin|manager'])->get('clients/export', [ClientExportController::class, 'export'])
        ->name('manager.clients.export');
});
