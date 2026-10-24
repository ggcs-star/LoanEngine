<?php

use Illuminate\Support\Facades\Route;
use LoanEngine\Auth\Http\Controllers\AuthController;

Route::middleware('web')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('auth.login.submit');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('auth.register.submit');
        });
        
Route::get('/forgot-password', [
    AuthController::class,
    'showForgotPassword',
])->name('password.request');

Route::post('/forgot-password', [
    AuthController::class,
    'sendResetOtp',
])->middleware('throttle:3,1')->name('password.email');

Route::get('/verify-reset-otp', [
    AuthController::class,
    'showVerifyResetOtp',
])->middleware('throttle:10,1')->name('password.otp.form');

Route::post('/verify-reset-otp', [
    AuthController::class,
    'verifyResetOtp',
])->middleware('throttle:5,1')->name('password.otp.verify');

Route::get('/reset-password', [
    AuthController::class,
    'showResetPassword',
])->middleware('throttle:10,1')->name('password.reset.form');

Route::post('/reset-password', [
    AuthController::class,
    'resetPassword',
])->middleware('throttle:5,1')->name('password.reset.update');


    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->middleware(['auth', 'role:admin'])->name('admin.dashboard');

    Route::get('/user/dashboard', fn () => view('user.dashboard'))
    ->middleware(['auth', 'role:user'])
    ->name('user.dashboard');
});
