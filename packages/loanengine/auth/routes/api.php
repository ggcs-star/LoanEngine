<?php

use Illuminate\Support\Facades\Route;
use LoanEngine\Auth\Http\Controllers\Api\AuthController;

Route::prefix('api/v1/auth')->name('loanengine-auth.api.')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:4,1')->name('register');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
