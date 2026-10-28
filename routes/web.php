<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profil', [HomeController::class, 'profile'])->name('profile');
Route::get('/galeri', [HomeController::class, 'gallery'])->name('gallery');

Route::get('/daftar', [RegistrationController::class, 'show'])->name('register.show');
Route::post('/daftar', [RegistrationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('register');

Route::post('/karya/{artwork}/view', [HomeController::class, 'recordView'])
    ->middleware('throttle:60,1')
    ->name('artworks.view');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');
