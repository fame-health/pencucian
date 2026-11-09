<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/daftar-pencuci', [GoogleAuthController::class, 'showRegistration'])->name('washer.register');
Route::post('/daftar-pencuci', [GoogleAuthController::class, 'registerWasher'])->name('washer.register.store');
