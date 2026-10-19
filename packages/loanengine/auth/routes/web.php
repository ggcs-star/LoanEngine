<?php

use Illuminate\Support\Facades\Route;
use LoanEngine\Auth\Http\Controllers\AuthController;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login.submit');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register.submit');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->middleware(['auth', 'role:admin'])->name('admin.dashboard');

    Route::get('/user/dashboard', fn () => view('user.dashboard'))
    ->middleware(['auth', 'role:user'])
    ->name('user.dashboard');
});
