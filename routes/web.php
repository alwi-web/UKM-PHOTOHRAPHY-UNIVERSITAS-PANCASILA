<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/daftar', [RegistrationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('register');

Route::post('/karya/{artwork}/view', [HomeController::class, 'recordView'])
    ->middleware('throttle:60,1')
    ->name('artworks.view');
