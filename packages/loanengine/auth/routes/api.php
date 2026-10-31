<?php

use Illuminate\Support\Facades\Route;
use LoanEngine\Auth\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| LoanEngine Authentication API Routes
|--------------------------------------------------------------------------
|
| Base URL: /api/v1/auth
|
*/

Route::prefix('api/v1/auth')
    ->name('loanengine-auth.api.')
    ->group(function (): void {

        // Public authentication routes
        Route::middleware('throttle:4,1')->group(function (): void {
            Route::post('/register', [AuthController::class, 'register'])
                ->name('register');
        });

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:6,1')
            ->name('login');

        // Protected authentication routes
        Route::middleware('auth:sanctum')->group(function (): void {

            Route::get('/me', [AuthController::class, 'me'])
                ->name('me');

            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('logout');

            // User activity history
            Route::get('/activity', [AuthController::class, 'activity'])
                ->middleware('throttle:60,1')
                ->name('activity');

            // User activity summary
            Route::get('/activity/summary', [AuthController::class, 'activitySummary'])
                ->middleware('throttle:60,1')
                ->name('activity.summary');

            // Admin security events
            Route::get('/admin/security-events', [AuthController::class, 'securityEvents'])
                ->middleware('throttle:30,1')
                ->name('admin.security-events');
        });
    });
    