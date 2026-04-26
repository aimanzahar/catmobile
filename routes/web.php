<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::post('/dashboard/bookings/{booking}/cancel', [DashboardController::class, 'cancelBooking'])->name('dashboard.bookings.cancel');

    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
    Route::patch('/pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    Route::delete('/pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/book', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/book/create/{service}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/book/{booking}/payment', [BookingController::class, 'payment'])->name('bookings.payment');
    Route::post('/book/{booking}/payment', [BookingController::class, 'processPayment'])->name('bookings.payment.process');
    Route::get('/book/{booking}/payment/cancel', [BookingController::class, 'cancelPayment'])->name('bookings.payment.cancel');
    Route::get('/book/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
});
