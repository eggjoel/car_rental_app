<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\CarModelController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\RentalController;

use Illuminate\Support\Facades\Route;

//USERS
Route::post('/users/register', [UserController::class, 'register'])->name('users');
Route::post('/users/login', [UserController::class, 'login']);

// These Other APIS require Authentification
Route::middleware('auth:sanctum')->group(function () {
    //CAR MODELS
    Route::post('/models/register', [CarModelController::class, 'register'])->name('cars.models');
    Route::get('/models', [CarModelController::class, 'show']);

    //CARS
    Route::post('/cars/register', [CarController::class, 'register'])->name('cars');
    Route::get('/cars/{filter?}', [CarController::class, 'show']);

    //RENTALS
    Route::post('/rentals/register/{regNo}', [RentalController::class, 'register'])->name('cars');
    Route::get('/rentals', [RentalController::class, 'show']);
    Route::post('/rentals/update', [RentalController::class, 'modify']);
});

