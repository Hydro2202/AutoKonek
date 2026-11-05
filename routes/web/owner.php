<?php

use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:business_owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [Owner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reviews', [Owner\ReviewController::class, 'index'])->name('reviews');

    Route::resource('establishments', Owner\EstablishmentController::class)->except('show');

    Route::prefix('establishments/{establishment}')->group(function () {
        Route::get('services', [Owner\ServiceController::class, 'index'])->name('services.index');
        Route::post('services', [Owner\ServiceController::class, 'store'])->name('services.store');
        Route::put('services/{service}', [Owner\ServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [Owner\ServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('images', [Owner\ImageController::class, 'index'])->name('images.index');
        Route::post('images', [Owner\ImageController::class, 'store'])->name('images.store');
        Route::put('images/{image}/primary', [Owner\ImageController::class, 'makePrimary'])->name('images.primary');
        Route::delete('images/{image}', [Owner\ImageController::class, 'destroy'])->name('images.destroy');
    });
});
