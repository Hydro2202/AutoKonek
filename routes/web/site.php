<?php

use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\EstablishmentController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LocationController;
use Illuminate\Support\Facades\Route;

/*
| What a visitor who is not logged in can open:
|  - the landing page,
|  - the legal pages (they must be readable before someone agrees to them),
|  - login and register (see auth.php).
| Everything else below needs an account: guests are sent to the login page,
| and after logging in they land on the page they asked for.
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');

Route::middleware(['auth', 'active'])->group(function () {
    Route::view('/about', 'site.about')->name('about');
    Route::get('/contact', [ContactController::class, 'create'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

    Route::get('/establishments', [EstablishmentController::class, 'index'])->name('establishments.index');
    Route::get('/establishments/{establishment}', [EstablishmentController::class, 'show'])->name('establishments.show');
    Route::get('/categories/{category:slug}', [EstablishmentController::class, 'category'])->name('categories.show');

    // Places: every Philippine region, province, city and municipality
    Route::get('/places', [LocationController::class, 'index'])->name('places.index');
    Route::get('/places/provinces', [LocationController::class, 'provinces'])->middleware('throttle:60,1')->name('places.provinces');
    Route::get('/places/cities', [LocationController::class, 'cities'])->middleware('throttle:60,1')->name('places.cities');
});
