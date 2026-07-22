<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Portal\AccountAccessController;
use App\Http\Controllers\Portal\DashboardRedirectController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PropertyCatalogueController;
use App\Http\Controllers\Staff\StaffDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->name('home');

Route::get('/health', HealthCheckController::class)
    ->name('health');

Route::get(
    '/properties',
    [PropertyCatalogueController::class, 'index'],
)->name('properties.index');

Route::get(
    '/properties/{house}',
    [PropertyCatalogueController::class, 'show'],
)->name('properties.show');

Route::middleware([
    'auth',
    'verified',
])->group(function (): void {
    Route::get(
        '/dashboard',
        DashboardRedirectController::class,
    )->name('dashboard');

    Route::get(
        '/access-status',
        AccountAccessController::class,
    )->name('access.status');
});

Route::middleware([
    'auth',
    'verified',
    'active',
    'customer',
])->prefix('customer')
    ->name('customer.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            CustomerDashboardController::class,
        )->name('dashboard');
    });

Route::middleware([
    'auth',
    'verified',
    'active',
    'staff',
])->prefix('staff')
    ->name('staff.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            StaffDashboardController::class,
        )->name('dashboard');
    });

Route::middleware([
    'auth',
    'verified',
    'active',
    'staff',
    'role:'.RoleName::SystemAdministrator->value,
])->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            AdminDashboardController::class,
        )->name('dashboard');

        Route::get(
            '/staff',
            [StaffManagementController::class, 'index'],
        )->middleware('permission:users.view')
            ->name('staff.index');

        Route::get(
            '/staff/create',
            [StaffManagementController::class, 'create'],
        )->middleware('permission:users.manage')
            ->name('staff.create');

        Route::post(
            '/staff',
            [StaffManagementController::class, 'store'],
        )->middleware('permission:users.manage')
            ->name('staff.store');

        Route::get(
            '/staff/{staff}/edit',
            [StaffManagementController::class, 'edit'],
        )->middleware('permission:users.manage')
            ->name('staff.edit');

        Route::put(
            '/staff/{staff}',
            [StaffManagementController::class, 'update'],
        )->middleware('permission:users.manage')
            ->name('staff.update');

        Route::patch(
            '/staff/{staff}/activate',
            [StaffManagementController::class, 'activate'],
        )->middleware('permission:users.manage')
            ->name('staff.activate');

        Route::patch(
            '/staff/{staff}/suspend',
            [StaffManagementController::class, 'suspend'],
        )->middleware('permission:users.manage')
            ->name('staff.suspend');

        Route::post(
            '/staff/{staff}/resend-setup',
            [StaffManagementController::class, 'resendSetup'],
        )->middleware('permission:users.manage')
            ->name('staff.resend-setup');
    });

require __DIR__.'/settings.php';
