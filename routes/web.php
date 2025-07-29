<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect('/liceo');
})->name('home');

// Redireccionar rutas de autenticación por defecto a Filament
Route::get('/login', function () {
    return redirect('/liceo/login');
});

Route::get('/register', function () {
    return redirect('/liceo/login');
});

Route::get('/forgot-password', function () {
    return redirect('/liceo/login');
});

Route::get('/reset-password/{token}', function () {
    return redirect('/liceo/login');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
