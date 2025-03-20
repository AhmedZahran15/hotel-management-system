<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\PermissionMiddleware as MiddlewarePermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware as MiddlewareRoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware as MiddlewareRoleOrPermissionMiddleware;


class MiddlewareServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Route::aliasMiddleware('role', MiddlewareRoleMiddleware::class);
        Route::aliasMiddleware('permission', MiddlewarePermissionMiddleware::class);
        Route::aliasMiddleware('role_or_permission', MiddlewareRoleOrPermissionMiddleware::class);
    }
}
