
<?php

use App\Http\Controllers\Admin\Education\CategoryController;
use App\Http\Controllers\Admin\Education\LessonController;
use App\Http\Controllers\Admin\Education\ModuleController;
use App\Http\Controllers\EducationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Tools\ToolController;
use App\Http\Controllers\Admin\Tools\ToolJourneyController;
use App\Http\Controllers\Admin\Tools\JourneyStepController;
use App\Http\Controllers\User\EducationController as UserEducationController;
use App\Http\Controllers\User\ToolController as UserToolController;
use App\Http\Controllers\User\ToolJourneyController as UserToolJourneyController;
use App\Http\Controllers\User\CalculatorController as UserCalculatorController;
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

/*
|--------------------------------------------------------------------------
| Education: Admin Management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::post('tools/bulk-delete', [ToolController::class, 'bulkDestroy'])
    ->name('tools.bulk-destroy');
        Route::resource('tools', ToolController::class)
    ->names('tools');
        
Route::get(
    'tool-journeys/{tool_journey}/tools',
    [JourneyStepController::class, 'toolsForJourney']
)->name('journey-steps.tools');

Route::post('journey-steps/bulk-delete', [JourneyStepController::class, 'bulkDestroy'])
    ->name('journey-steps.bulk-destroy');

Route::resource('journey-steps', JourneyStepController::class);
    });
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::post('tool-journeys/bulk-delete', [ToolJourneyController::class, 'bulkDestroy'])
    ->name('tool-journeys.bulk-destroy');

Route::resource('tool-journeys', ToolJourneyController::class);
    });
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/education')
    ->name('admin.education.')
    ->group(function (): void {
         Route::delete(
            'categories/bulk-delete',
            [CategoryController::class, 'bulkDestroy']
        )->name('categories.bulk-destroy');

        // Categories: index, create, store, show, edit, update, destroy
        Route::resource('categories', CategoryController::class);

        Route::delete('modules/bulk-destroy', [ModuleController::class, 'bulkDestroy'])->name('modules.bulk-destroy');
        Route::resource('modules', ModuleController::class);

        // Lessons: index, create, store, show, edit, update, destroy

        Route::delete(
            'lessons/bulk-destroy',
            [LessonController::class, 'bulkDestroy']
        )->name('lessons.bulk-destroy');

        Route::resource('lessons', LessonController::class);
    });

/*
|--------------------------------------------------------------------------
| Education: Published Content
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('education')
    ->name('education.')
    ->group(function (): void {

        // Education home
        Route::get('/', [EducationController::class, 'index'])
            ->name('index');

        // Module details
        Route::get('/{moduleSlug}', [EducationController::class, 'show'])
            ->name('show');

        // Lesson details
        Route::get('/{moduleSlug}/lessons/{lessonSlug}', [
            EducationController::class,
            'lesson',
        ])->name('lesson');
    });

Route::middleware(['auth', 'role:user'])
    ->prefix('user/education')
    ->name('user.education.')
    ->group(function () {
        
        Route::get('/', [UserEducationController::class, 'index'])
            ->name('index');

        Route::get('/categories/{category}', [
            UserEducationController::class,
            'category',
        ])->name('category');

        Route::get('/modules/{module}', [
            UserEducationController::class,
            'module',
        ])->name('module');

        Route::get('/lessons/{lesson}', [
            UserEducationController::class,
            'lesson',
        ])->name('lesson');
    });
/*
|--------------------------------------------------------------------------
| User: Loan Tools
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:user'])
    ->prefix('user/tools')
    ->name('user.tools.')
    ->group(function () {
        Route::get('/', [UserToolController::class, 'index'])
            ->name('index');

        Route::get('/journeys', [UserToolJourneyController::class, 'index'])
            ->name('journeys.index');

        Route::get('/journeys/{journey:slug}', [
            UserToolJourneyController::class, 'show',
        ])->name('journeys.show');

        Route::get('/journeys/{journey:slug}/steps/{step}', [
            UserToolJourneyController::class, 'step',
        ])->name('journeys.steps.show');

        Route::get('/{tool:slug}', [UserToolController::class, 'show'])
            ->name('show');
    });

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/tools/{tool:slug}', [
        UserCalculatorController::class, 'show',
    ])->name('calculators.show');

    Route::post('/tools/{tool:slug}', [
        UserCalculatorController::class, 'calculate',
    ])->name('calculators.calculate');
});