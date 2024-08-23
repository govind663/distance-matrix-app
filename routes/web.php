<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeolocationController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/geolocation', [GeolocationController::class, 'index']);
Route::post('/location', [GeolocationController::class, 'getLocation'])->name('geolocation');
