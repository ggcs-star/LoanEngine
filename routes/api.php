
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\EducationCategoryController;
use App\Http\Controllers\Api\V1\User\EducationModuleController;
use App\Http\Controllers\Api\V1\User\EducationLessonController;
use App\Http\Controllers\Api\V1\User\ToolController;
use App\Http\Controllers\Api\V1\User\ToolJourneyController;
use App\Http\Controllers\Api\V1\User\JourneyStepController;
use App\Http\Controllers\Api\V1\User\CalculatorController;
use App\Http\Controllers\Api\V1\User\CalendarController;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('auth:sanctum')
    ->group(function () {

        Route::prefix('user/education')
            ->name('user.education.')
            ->group(function () {

                Route::get('/categories', [
                    EducationCategoryController::class,
                    'index',
                ])->name('categories.index');

                Route::get('/categories/{category}', [
                    EducationCategoryController::class,
                    'show',
                ])->whereNumber('category')
                  ->name('categories.show');

                Route::get('/categories/{category}/modules', [
                    EducationCategoryController::class,
                    'modules',
                ])->whereNumber('category')
                  ->name('categories.modules');

                Route::get('/modules', [
                    EducationModuleController::class,
                    'index',
                ])->name('modules.index');

                Route::get('/modules/{module}', [
                    EducationModuleController::class,
                    'show',
                ])->whereNumber('module')
                  ->name('modules.show');

                Route::get('/lessons/{lesson}', [
                    EducationLessonController::class,
                    'show',
                ])->whereNumber('lesson')
                  ->name('lessons.show');
            });

        Route::prefix('user/tools')
            ->name('user.tools.')
            ->group(function () {

                Route::get('/', [
                    ToolController::class,
                    'index',
                ])->name('index');

                Route::get('/{tool}', [
                    ToolController::class,
                    'show',
                ])->whereNumber('tool')->name('show');

                Route::get('/journeys', [
                    ToolJourneyController::class,
                    'index',
                ])->name('journeys.index');

                Route::get('/journeys/{journey}', [
                    ToolJourneyController::class,
                    'show',
                ])->whereNumber('journey')->name('journeys.show');

                Route::get('/journeys/{journey}/steps/{step}', [
                    JourneyStepController::class,
                    'show',
                ])->whereNumber('journey')
                  ->whereNumber('step')
                  ->name('journeys.steps.show');
            });

        Route::prefix('user/calculators')
            ->name('user.calculators.')
            ->group(function () {

                Route::get('/', [
                    CalculatorController::class,
                    'index',
                ])->name('index');

                Route::get('/history', [
                    CalculatorController::class,
                    'history',
                ])->name('history.index');

                Route::get('/history/{usage}', [
                    CalculatorController::class,
                    'historyShow',
                ])->whereNumber('usage')
                  ->name('history.show');

                Route::get('/{calculator}', [
                    CalculatorController::class,
                    'show',
                ])->whereNumber('calculator')
                  ->name('show');

                Route::post('/{calculator}/calculate', [
                    CalculatorController::class,
                    'calculate',
                ])->whereNumber('calculator')
                  ->name('calculate');
            });
            


Route::prefix('user/calendar')
    ->name('user.calendar.')
    ->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');

        Route::get('/events', [CalendarController::class, 'events'])->name('events.index');
        Route::post('/events', [CalendarController::class, 'store'])->name('events.store');

        Route::get('/events/{event}', [CalendarController::class, 'show'])
            ->whereNumber('event')
            ->name('events.show');

        Route::put('/events/{event}', [CalendarController::class, 'update'])
            ->whereNumber('event')
            ->name('events.update');

        Route::patch('/events/{event}/status', [CalendarController::class, 'updateStatus'])
            ->whereNumber('event')
            ->name('events.status');

        Route::delete('/events/{event}', [CalendarController::class, 'destroy'])
            ->whereNumber('event')
            ->name('events.destroy');
    });

    });
