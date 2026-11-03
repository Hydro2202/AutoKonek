<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Owner;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
| Public pages
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/establishments', [EstablishmentController::class, 'index'])->name('establishments.index');
Route::get('/establishments/{establishment}', [EstablishmentController::class, 'show'])->name('establishments.show');
Route::get('/categories/{category:slug}', [EstablishmentController::class, 'category'])->name('categories.show');

/*
| Guests only
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

/*
| Any signed-in, active user
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/report', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/report', [ReportController::class, 'store'])->name('reports.store');
});

/*
| Customers
*/
Route::middleware(['auth', 'active', 'role:customer'])->group(function () {
    Route::post('/establishments/{establishment}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/my-reviews', [CustomerController::class, 'reviews'])->name('customer.reviews');
    Route::get('/my-reports', [CustomerController::class, 'reports'])->name('customer.reports');
});

/*
| Business owners
*/
Route::middleware(['auth', 'active', 'role:business_owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [Owner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reviews', [Owner\ReviewController::class, 'index'])->name('reviews');

    Route::resource('establishments', Owner\EstablishmentController::class)->except('show');
    Route::get('establishments/{establishment}/services', [Owner\ServiceController::class, 'index'])->name('services.index');
    Route::post('establishments/{establishment}/services', [Owner\ServiceController::class, 'store'])->name('services.store');
    Route::put('establishments/{establishment}/services/{service}', [Owner\ServiceController::class, 'update'])->name('services.update');
    Route::delete('establishments/{establishment}/services/{service}', [Owner\ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('establishments/{establishment}/images', [Owner\ImageController::class, 'index'])->name('images.index');
    Route::post('establishments/{establishment}/images', [Owner\ImageController::class, 'store'])->name('images.store');
    Route::put('establishments/{establishment}/images/{image}/primary', [Owner\ImageController::class, 'makePrimary'])->name('images.primary');
    Route::delete('establishments/{establishment}/images/{image}', [Owner\ImageController::class, 'destroy'])->name('images.destroy');
});

/*
| Administrators
*/
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

    Route::resource('categories', Admin\CategoryController::class)->except('create');

    Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [Admin\ReportController::class, 'update'])->name('reports.update');

    Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
});
