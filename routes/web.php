<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\FlightSearchController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:register')->name('register.store');

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('account/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('account/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('flights', [FlightSearchController::class, 'index'])->name('flights.index');
    Route::get('flights/search', [FlightSearchController::class, 'search'])->middleware('throttle:flight-search')->name('flights.search');
    Route::get('flights/cities', [CityController::class, 'index'])->middleware('throttle:60,1')->name('flights.cities');
});
