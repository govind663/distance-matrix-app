<?php

use App\Http\Controllers\GeolocationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/geolocation', [GeolocationController::class, 'index'])->name('geolocation');
    Route::post('/location', [GeolocationController::class, 'getLocation'])->name('location');
    Route::post('/location/delete', [GeolocationController::class, 'deleteLocation'])->name('location.delete');
});

require __DIR__.'/auth.php';
