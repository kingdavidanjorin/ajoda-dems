<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PropertyCatalogueController;
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
