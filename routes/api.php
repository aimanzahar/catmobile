<?php

use App\Http\Controllers\Api\Auth\RegisteredUserController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TimeSlotController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [SessionController::class, 'store']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/time-slots', [TimeSlotController::class, 'available']);

Route::middleware('auth:pocketbase')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy']);
    Route::get('/me', MeController::class);
    Route::get('/dashboard', [DashboardController::class, 'show']);
    Route::get('/pets', [PetController::class, 'index']);
    Route::post('/pets', [PetController::class, 'store']);
    Route::patch('/pets/{pet}', [PetController::class, 'update']);
    Route::delete('/pets/{pet}', [PetController::class, 'destroy']);
    Route::patch('/profile', [ProfileController::class, 'update']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/payment', [BookingController::class, 'pay']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});
