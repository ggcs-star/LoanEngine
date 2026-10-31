
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\Api\V1\User\DashboardController;

use App\Http\Controllers\Api\V1\User\Education\CategoryController;
use App\Http\Controllers\Api\V1\User\Education\ModuleController;
use App\Http\Controllers\Api\V1\User\Education\LessonController;
use App\Http\Controllers\Api\V1\User\Education\LessonProgressController;

use App\Http\Controllers\Api\V1\User\ToolController;
use App\Http\Controllers\Api\V1\User\ToolJourneyController;
use App\Http\Controllers\Api\V1\User\JourneyStepController;

use App\Http\Controllers\Api\V1\User\CalculatorController;
use App\Http\Controllers\Api\V1\User\RecommendedCalculatorController;

use App\Http\Controllers\Api\V1\User\FinancialFlowCalendarController;
use App\Http\Controllers\Api\V1\User\FinancialCalendarAnalyticsController;

/*
|--------------------------------------------------------------------------
| LoanEngine User API Routes
|--------------------------------------------------------------------------
|
| Base URL: /api/v1
| Authentication: Laravel Sanctum
| Auth routes are managed separately.
|
*/

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('auth:sanctum')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | User Profile
        |--------------------------------------------------------------------------
        */

        Route::prefix('user/profile')
            ->name('user.profile.')
            ->controller(ProfileController::class)
            ->group(function (): void {
                Route::get('/', 'show')->name('show');
                Route::patch('/', 'update')->name('update');
                Route::delete('/photo', 'destroyPhoto')->name('photo.destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            DashboardController::class,
            'index',
        ])->name('dashboard.index');

        /*
        |--------------------------------------------------------------------------
        | Education Categories
        |--------------------------------------------------------------------------
        */

        Route::get('/education/categories', [
            CategoryController::class,
            'index',
        ])->name('education.categories.index');

        Route::get('/education/categories/{category}', [
            CategoryController::class,
            'show',
        ])->name('education.categories.show');

        /*
        |--------------------------------------------------------------------------
        | Education Modules
        |--------------------------------------------------------------------------
        */

        Route::get('/education/modules', [
            ModuleController::class,
            'index',
        ])->name('education.modules.index');

        Route::get('/education/modules/{module}', [
            ModuleController::class,
            'show',
        ])->name('education.modules.show');

        /*
        |--------------------------------------------------------------------------
        | Education Lessons
        |--------------------------------------------------------------------------
        */

        Route::get('/education/lessons', [
            LessonController::class,
            'index',
        ])->name('education.lessons.index');

        Route::get('/education/lessons/{lesson}', [
            LessonController::class,
            'show',
        ])->name('education.lessons.show');

        /*
        |--------------------------------------------------------------------------
        | Lesson Progress
        |--------------------------------------------------------------------------
        */

        Route::get('/education/progress', [
            LessonProgressController::class,
            'index',
        ])->name('education.progress.index');

        Route::post('/education/lessons/{lesson}/progress', [
            LessonProgressController::class,
            'store',
        ])->name('education.lessons.progress.store');

        Route::patch('/education/lessons/{lesson}/progress', [
            LessonProgressController::class,
            'update',
        ])->name('education.lessons.progress.update');

        Route::post('/education/lessons/{lesson}/complete', [
            LessonProgressController::class,
            'complete',
        ])->name('education.lessons.complete');

        /*
        |--------------------------------------------------------------------------
        | Tools
        |--------------------------------------------------------------------------
        */

        Route::get('/tools', [
            ToolController::class,
            'index',
        ])->name('tools.index');

        Route::get('/tools/{tool}', [
            ToolController::class,
            'show',
        ])->name('tools.show');

        /*
        |--------------------------------------------------------------------------
        | Tool Journeys
        |--------------------------------------------------------------------------
        */

        Route::get('/tool-journeys', [
            ToolJourneyController::class,
            'index',
        ])->name('tool-journeys.index');

        Route::get('/tool-journeys/{journey}', [
            ToolJourneyController::class,
            'show',
        ])->name('tool-journeys.show');

        /*
        |--------------------------------------------------------------------------
        | Journey Steps
        |--------------------------------------------------------------------------
        */

        Route::get('/tool-journeys/{journey}/steps', [
            JourneyStepController::class,
            'index',
        ])->name('tool-journeys.steps.index');

        Route::get('/tool-journeys/{journey}/steps/{step}', [
            JourneyStepController::class,
            'show',
        ])->name('tool-journeys.steps.show');

        /*
        |--------------------------------------------------------------------------
        | Calculators
        |--------------------------------------------------------------------------
        */

        Route::get('/calculators', [
            CalculatorController::class,
            'index',
        ])->name('calculators.index');

        Route::get('/calculators/{calculator}', [
            CalculatorController::class,
            'show',
        ])->name('calculators.show');

        Route::post('/calculators/{calculator}/calculate', [
            CalculatorController::class,
            'calculate',
        ])->name('calculators.calculate');

        /*
        |--------------------------------------------------------------------------
        | Recommended Calculators
        |--------------------------------------------------------------------------
        */

        Route::get('/recommended-calculators', [
            RecommendedCalculatorController::class,
            'index',
        ])->name('recommended-calculators.index');

        Route::get('/recommended-calculators/{calculator}', [
            RecommendedCalculatorController::class,
            'show',
        ])->name('recommended-calculators.show');

        /*
        |--------------------------------------------------------------------------
        | Financial Flow Calendar
        |--------------------------------------------------------------------------
        */

        Route::get('/financial-calendar', [
            FinancialFlowCalendarController::class,
            'index',
        ])->name('financial-calendar.index');

        Route::post('/financial-calendar', [
            FinancialFlowCalendarController::class,
            'store',
        ])->name('financial-calendar.store');

        Route::get('/financial-calendar/{event}', [
            FinancialFlowCalendarController::class,
            'show',
        ])->name('financial-calendar.show');

        Route::put('/financial-calendar/{event}', [
            FinancialFlowCalendarController::class,
            'update',
        ])->name('financial-calendar.update');

        Route::patch('/financial-calendar/{event}', [
            FinancialFlowCalendarController::class,
            'update',
        ])->name('financial-calendar.patch');

        Route::delete('/financial-calendar/{event}', [
            FinancialFlowCalendarController::class,
            'destroy',
        ])->name('financial-calendar.destroy');

        /*
        |--------------------------------------------------------------------------
        | Financial Calendar Analytics
        |--------------------------------------------------------------------------
        */

        Route::get('/financial-calendar-analytics', [
            FinancialCalendarAnalyticsController::class,
            'index',
        ])->name('financial-calendar-analytics.index');

    });
