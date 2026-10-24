<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Models\FinancialEvent;
use App\Models\UserLessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;
        $today = now()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

        $educationCount = EducationCategory::query()
            ->active()
            ->count();

        $publishedModulesCount = EducationModule::query()
            ->published()
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->count();

        $availableLessonsQuery = EducationLesson::query()
            ->published()
            ->whereHas('module', function ($query) {
                $query->where('status', 'published')
                    ->whereHas('category', function ($categoryQuery) {
                        $categoryQuery->where('is_active', true);
                    });
            });

        $totalLessons = (clone $availableLessonsQuery)->count();

        $dashboardStats = [
            'education' => $educationCount,
            'calculators' => $this->countFirstExistingTable([
                'calculators',
                'loan_calculators',
            ]),
            'tools' => $this->countFirstExistingTable([
                'tools',
                'loan_tools',
            ]),
            'resources' => $totalLessons,
        ];

        /*
        |--------------------------------------------------------------------------
        | Financial Calendar
        |--------------------------------------------------------------------------
        */

        $calendarEvents = FinancialEvent::query()
            ->forUser($userId)
            ->forMonth((int) now()->year, (int) now()->month)
            ->orderBy('event_date')
            ->orderBy('reminder_time')
            ->orderBy('id')
            ->get();

        $calendarEventsByDate = $calendarEvents->groupBy(
            fn ($event) => Carbon::parse($event->event_date)->toDateString()
        );

        $upcomingEvents = FinancialEvent::query()
            ->forUser($userId)
            ->whereDate('event_date', '>=', $today)
            ->orderBy('event_date')
            ->orderByRaw('reminder_time IS NULL')
            ->orderBy('reminder_time')
            ->orderBy('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | User-Specific Learning Progress
        |--------------------------------------------------------------------------
        */

        // Get lesson IDs completed by the signed-in user.
        $completedLessonIds = UserLessonProgress::query()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->pluck('lesson_id');

        // Count only lessons that are still published and available.
        $completedLessons = (clone $availableLessonsQuery)
            ->whereIn('id', $completedLessonIds)
            ->count();

        // Avoid division by zero when no lessons are available.
        $learningProgress = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : 0;

        // Keep the dashboard compact: show only three recent lessons.
        $recentLessons = (clone $availableLessonsQuery)
            ->with('module:id,title')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get([
                'id',
                'education_module_id',
                'title',
                'content_type',
            ]);

        // Add a completion flag for each lesson for this user.
        $completedIds = $completedLessonIds
            ->map(fn ($id) => (int) $id)
            ->flip();

        $recentLessons->each(function ($lesson) use ($completedIds) {
            $lesson->is_completed = $completedIds->has(
                (int) $lesson->id
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Recommendations and Updates
        |--------------------------------------------------------------------------
        |
        | Keep the existing empty collections until real data sources are
        | connected to these sections.
        |
        */

        $recommendedItems = collect();
        $recentUpdates = collect();

        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('user.dashboard', compact(
            'dashboardStats',
            'publishedModulesCount',
            'learningProgress',
            'completedLessons',
            'totalLessons',
            'recentLessons',
            'recommendedItems',
            'recentUpdates',
            'upcomingEvents',
            'calendarEvents',
            'calendarEventsByDate'
        ));
    }

    /**
     * Count records from the first existing table.
     */
    private function countFirstExistingTable(array $tables): int
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return DB::table($table)->count();
            }
        }

        return 0;
    }
}
