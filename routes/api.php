<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\EducationCategoryController;
use App\Http\Controllers\Api\V1\User\EducationModuleController;
use App\Http\Controllers\Api\V1\User\EducationLessonController;
use App\Http\Controllers\Api\V1\User\ToolController;
use App\Http\Controllers\Api\V1\User\ToolJourneyController;
use App\Http\Controllers\Api\V1\User\JourneyStepController;

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
    });
    


Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::prefix('user/tools')
            ->name('user.tools.')
            ->group(function () {

                // Tools
                Route::get('/', [
                    ToolController::class,
                    'index',
                ])->name('index');

                Route::get('/{tool}', [
                    ToolController::class,
                    'show',
                ])->whereNumber('tool')->name('show');

                // Tool journeys
                Route::get('/journeys', [
                    ToolJourneyController::class,
                    'index',
                ])->name('journeys.index');

                Route::get('/journeys/{journey}', [
                    ToolJourneyController::class,
                    'show',
                ])->whereNumber('journey')->name('journeys.show');

                // Journey step details and navigation
                Route::get('/journeys/{journey}/steps/{step}', [
                    JourneyStepController::class,
                    'show',
                ])->whereNumber('journey')
                  ->whereNumber('step')
                  ->name('journeys.steps.show');
            });
    });

