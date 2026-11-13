<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');

    Route::get('/approvals', [Admin\EstablishmentController::class, 'approvals'])->name('approvals');
    Route::get('/establishments', [Admin\EstablishmentController::class, 'index'])->name('establishments.index');
    Route::get('/establishments/{establishment}', [Admin\EstablishmentController::class, 'show'])->name('establishments.show');
    Route::patch('/establishments/{establishment}/approve', [Admin\EstablishmentController::class, 'approve'])->name('establishments.approve');
    Route::patch('/establishments/{establishment}/reject', [Admin\EstablishmentController::class, 'reject'])->name('establishments.reject');
    Route::delete('/establishments/{establishment}', [Admin\EstablishmentController::class, 'destroy'])->name('establishments.destroy');

    Route::patch('/categories/{category}/toggle', [Admin\CategoryController::class, 'toggle'])->name('categories.toggle');
    Route::resource('categories', Admin\CategoryController::class)->except('create');

    Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [Admin\ReportController::class, 'update'])->name('reports.update');

    Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');

    Route::get('/fuel-prices', [Admin\FuelPriceController::class, 'index'])->name('fuel-prices.index');
    Route::post('/fuel-prices', [Admin\FuelPriceController::class, 'store'])->name('fuel-prices.store');
    Route::delete('/fuel-prices/latest', [Admin\FuelPriceController::class, 'destroyLatest'])->name('fuel-prices.destroy-latest');
});
