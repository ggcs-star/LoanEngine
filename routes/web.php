<?php

use App\Http\Controllers\Admin\Calculators\CalculatorController as AdminCalculatorController;
use App\Http\Controllers\Admin\Calculators\CalculatorInsightsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Education\CategoryController;
use App\Http\Controllers\Admin\Education\LessonController;
use App\Http\Controllers\Admin\Education\ModuleController;
use App\Http\Controllers\Admin\FinancialFlow\CalendarController;
use App\Http\Controllers\Admin\FinancialFlow\DashboardController as AdminFinancialFlowDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\Tools\JourneyStepController;
use App\Http\Controllers\Admin\Tools\ToolController;
use App\Http\Controllers\Admin\Tools\ToolJourneyController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\User\CalculatorController as UserCalculatorController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\EducationController as UserEducationController;
use App\Http\Controllers\User\FinancialFlowCalendarController;
use App\Http\Controllers\User\LessonProgressController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\User\RecommendedCalculatorController;
use App\Http\Controllers\User\ToolController as UserToolController;
use App\Http\Controllers\User\ToolJourneyController as UserToolJourneyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserManagementController;
// use App\Http\Controllers\Admin\Reports\AnalyticsController;
use App\Http\Controllers\Admin\Reports\UserActivityController;
use App\Http\Controllers\Admin\Reports\UserAnalyticsController;
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->controller(UserManagementController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{user}', 'show')
            ->whereNumber('user')
            ->name('show');
    });


/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/


Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('auth.login');
    }

    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.dashboard');
})->name('home');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::post('tools/bulk-delete', [ToolController::class, 'bulkDestroy'])
            ->name('tools.bulk-destroy');

        Route::resource('tools', ToolController::class)
            ->names('tools');

        Route::get('settings', [SiteSettingController::class, 'edit'])
            ->name('settings.edit');

        Route::put('settings', [SiteSettingController::class, 'update'])
            ->name('settings.update');

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('profile', [AdminProfileController::class, 'show'])
            ->name('profile.show');

        Route::get('profile/edit', [AdminProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        Route::get(
            'tool-journeys/{tool_journey}/tools',
            [JourneyStepController::class, 'toolsForJourney']
        )->name('journey-steps.tools');

        Route::post('journey-steps/bulk-delete', [JourneyStepController::class, 'bulkDestroy'])
            ->name('journey-steps.bulk-destroy');

        Route::resource('journey-steps', JourneyStepController::class);

        Route::post('tool-journeys/bulk-delete', [ToolJourneyController::class, 'bulkDestroy'])
            ->name('tool-journeys.bulk-destroy');

        Route::resource('tool-journeys', ToolJourneyController::class);

        Route::prefix('calculator-insights')
            ->name('calculator-insights.')
            ->group(function (): void {
                Route::get('/', [CalculatorInsightsController::class, 'index'])
                    ->name('index');
            });

        Route::prefix('education')
            ->name('education.')
            ->group(function (): void {
                Route::delete(
                    'categories/bulk-delete',
                    [CategoryController::class, 'bulkDestroy']
                )->name('categories.bulk-destroy');

                Route::resource('categories', CategoryController::class);

                Route::delete(
                    'modules/bulk-destroy',
                    [ModuleController::class, 'bulkDestroy']
                )->name('modules.bulk-destroy');

                Route::resource('modules', ModuleController::class);

                Route::delete(
                    'lessons/bulk-destroy',
                    [LessonController::class, 'bulkDestroy']
                )->name('lessons.bulk-destroy');

                Route::resource('lessons', LessonController::class);
            });

        Route::prefix('financial-flow')
            ->name('financial-flow.')
            ->controller(AdminFinancialFlowDashboardController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/export', 'export')->name('export');
            });

        Route::prefix('financial-flow/calendar')
            ->name('financial-flow.calendar.')
            ->controller(CalendarController::class)
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/users/{user}', 'show')->whereNumber('user')->name('show');
                Route::get('/theme.css', 'themeCss')->name('theme');
            });

        Route::prefix('calculators')
            ->name('calculators.')
            ->group(function (): void {
                Route::get('/', [AdminCalculatorController::class, 'index'])
                    ->name('index');

                Route::get('/create', [AdminCalculatorController::class, 'create'])
                    ->name('create');

                Route::post('/', [AdminCalculatorController::class, 'store'])
                    ->name('store');

                Route::delete('/bulk-destroy', [AdminCalculatorController::class, 'bulkDestroy'])
                    ->name('bulk-destroy');

                Route::get('/{calculator}/edit', [AdminCalculatorController::class, 'edit'])
                    ->name('edit');

                Route::put('/{calculator}', [AdminCalculatorController::class, 'update'])
                    ->name('update');

                Route::delete('/{calculator}', [AdminCalculatorController::class, 'destroy'])
                    ->name('destroy');
            });

        Route::prefix('reports')->name('reports.')->group(function () {

    Route::get('/activities', [
        UserActivityController::class,
        'index'
    ])->name('activities');

    Route::get('/export', [
        UserActivityController::class,
        'exportAll'
    ])->name('export');

    Route::get('/users/{user}/export', [
        UserActivityController::class,
        'exportUser'
    ])->whereNumber('user')->name('users.export');

    Route::get('/users/{user}', [
        UserAnalyticsController::class,
        'show'
    ])->whereNumber('user')->name('users.show');

});
             });

Route::middleware('auth')
    ->prefix('education')
    ->name('education.')
    ->controller(EducationController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/{moduleSlug}', 'show')->name('show');
        Route::get('/{moduleSlug}/lessons/{lessonSlug}', 'lesson')->name('lesson');
    });

Route::middleware(['auth', 'role:user'])
    ->group(function (): void {
        Route::prefix('user')
            ->name('user.')
            ->group(function (): void {
                Route::get('/dashboard', [UserDashboardController::class, 'index'])
                    ->name('dashboard');

                Route::prefix('education')
                    ->name('education.')
                    ->controller(UserEducationController::class)
                    ->group(function (): void {
                        Route::get('/', 'index')->name('index');
                        Route::get('/categories/{category}', 'category')->name('category');
                        Route::get('/modules/{module}', 'module')->name('module');
                        Route::get('/lessons/{lesson}', 'lesson')->name('lesson');

                        Route::post('/lessons/{lesson}/complete', [
                            LessonProgressController::class,
                            'complete',
                        ])->name('lesson.complete');
                    });

                Route::prefix('tools')
                    ->name('tools.')
                    ->group(function (): void {
                        Route::get('/', [UserToolController::class, 'index'])
                            ->name('index');

                        Route::get('/journeys', [UserToolJourneyController::class, 'index'])
                            ->name('journeys.index');

                        Route::get('/journeys/{journey:slug}', [
                            UserToolJourneyController::class,
                            'show',
                        ])->name('journeys.show');

                        Route::get('/journeys/{journey:slug}/steps/{step}', [
                            UserToolJourneyController::class,
                            'step',
                        ])->name('journeys.steps.show');

                        Route::get('/{tool:slug}', [UserToolController::class, 'show'])
                            ->name('show');
                    });

                Route::prefix('financial-flow-calendar')
                    ->name('financial-flow.')
                    ->controller(FinancialFlowCalendarController::class)
                    ->group(function (): void {
                        Route::get('/', 'index')->name('index');
                        Route::get('/events', 'eventsJson')->name('events');
                        Route::post('/events', 'store')->name('events.store');
                        Route::patch('/events/{event}', 'update')->name('events.update');
                        Route::patch('/events/{event}/status', 'updateStatus')->name('events.status');
                        Route::delete('/events/{event}', 'destroy')->name('events.destroy');
                    });

                Route::prefix('home-loan-calculators')
                    ->name('calculators.')
                    ->controller(RecommendedCalculatorController::class)
                    ->group(function (): void {
                        Route::get('/', 'index')->name('journeys.index');
                        Route::get('/journeys/{journey:slug}', 'show')->name('journeys.show');
                    });

                Route::get('/profile', [UserProfileController::class, 'show'])
                    ->name('profile.show');

                Route::get('/profile/edit', [UserProfileController::class, 'edit'])
                    ->name('profile.edit');

                Route::put('/profile', [UserProfileController::class, 'update'])
                    ->name('profile.update');

                Route::delete('/profile/photo', [
                    UserProfileController::class,
                    'destroyPhoto',
                ])->name('profile.photo.destroy');
            });

        Route::get('/tools/{tool:slug}', [
            UserCalculatorController::class,
            'show',
        ])->name('calculators.show');

        Route::post('/tools/{tool:slug}', [
            UserCalculatorController::class,
            'calculate',
        ])->name('calculators.calculate');
    });

Route::prefix('reports')
    ->name('reports.')
    ->controller(UserAnalyticsController::class)
    ->group(function () {
        Route::get('/analytics', 'index')->name('analytics');
        Route::get('/activities', 'activities')->name('activities');
        Route::get('/users/{user}', 'user')
            ->whereNumber('user')
            ->name('users.show');
    });


