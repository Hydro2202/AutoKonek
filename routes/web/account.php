<?php

use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Customer\ActivityController;
use App\Http\Controllers\Customer\ReportController;
use App\Http\Controllers\Customer\ReviewController;
use Illuminate\Support\Facades\Route;

// Any signed-in, active user
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/report', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/report', [ReportController::class, 'store'])->name('reports.store');
});

// Customers only
Route::middleware(['auth', 'active', 'role:customer'])->group(function () {
    Route::post('/establishments/{establishment}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/my-reviews', [ActivityController::class, 'reviews'])->name('customer.reviews');
    Route::get('/my-reports', [ActivityController::class, 'reports'])->name('customer.reports');
});
