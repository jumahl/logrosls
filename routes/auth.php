<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Rutas de autenticación deshabilitadas - usando Filament
// Route::middleware('guest')->group(function () {
//     Volt::route('login', 'auth.login')
//         ->name('login');

//     Volt::route('register', 'auth.register')
//         ->name('register');

//     Volt::route('forgot-password', 'auth.forgot-password')
//         ->name('password.request');

//     Volt::route('reset-password/{token}', 'auth.reset-password')
//         ->name('password.reset');
// });

// Redireccionar rutas nombradas a Filament
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return redirect('/liceo/login');
    })->name('login');

    Route::get('register', function () {
        return redirect('/liceo/login');
    })->name('register');

    Route::get('forgot-password', function () {
        return redirect('/liceo/login');
    })->name('password.request');

    Route::get('reset-password/{token}', function () {
        return redirect('/liceo/login');
    })->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
