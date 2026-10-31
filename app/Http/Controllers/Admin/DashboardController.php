<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardController extends Controller
{
    public function index(): View
    {
        try {
            $statistics = [
                'users' => User::count(),
                'education_categories' => EducationCategory::count(),
                'education_modules' => EducationModule::count(),
                'education_lessons' => EducationLesson::count(),
                'published_modules' => EducationModule::where('status', 'published')->count(),
                'published_lessons' => EducationLesson::where('status', 'published')->count(),
                'tools' => Tool::count(),
                'active_tools' => Tool::where('status', Tool::STATUS_ACTIVE)->count(),
                'journeys' => ToolJourney::count(),
                'active_journeys' => ToolJourney::where('status', 'active')->count(),
                'journey_steps' => JourneyStep::count(),
                'active_journey_steps' => JourneyStep::where('status', 'active')->count(),
            ];

            // Optional models are detected safely so missing modules do not break the dashboard.
            $statistics['calculators'] = $this->optionalModelCount([
                \App\Models\Calculator::class,
            ]);
            $statistics['calculator_insights'] = $this->optionalModelCount([
                \App\Models\CalculatorInsight::class,
                \App\Models\CalculatorInsightRecord::class,
            ]);
            $statistics['financial_flow_records'] = $this->optionalModelCount([
                \App\Models\FinancialFlow::class,
                \App\Models\FinancialFlowRecord::class,
            ]);

            $recentTools = Tool::latest()->take(5)->get();
            $recentModules = EducationModule::latest()->take(5)->get();
            $recentLessons = EducationLesson::latest()->take(5)->get();
            $recentJourneys = ToolJourney::latest()->take(5)->get();
            $recentJourneySteps = JourneyStep::latest()->take(5)->get();

            $recentActivity = collect()
                ->concat(User::latest()->take(3)->get()->map(fn ($item) => [
                    'title' => $item->name ?? 'New user',
                    'type' => 'User',
                    'status' => 'Registered',
                    'created_at' => $item->created_at,
                ]))
                ->concat($recentTools->map(fn ($item) => [
                    'title' => $item->name,
                    'type' => 'Tool',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ]))
                ->concat($recentModules->map(fn ($item) => [
                    'title' => $item->title,
                    'type' => 'Module',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ]))
                ->concat($recentLessons->map(fn ($item) => [
                    'title' => $item->title,
                    'type' => 'Lesson',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ]))
                ->concat($recentJourneys->map(fn ($item) => [
                    'title' => $item->title ?? $item->name ?? 'Journey',
                    'type' => 'Journey',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ]))
                ->concat($recentJourneySteps->map(fn ($item) => [
                    'title' => $item->title ?? $item->name ?? 'Journey Step',
                    'type' => 'Journey Step',
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ]))
                ->filter(fn ($item) => !empty($item['created_at']))
                ->sortByDesc('created_at')
                ->take(8)
                ->values();

            $monthlyContent = collect(range(5, 0))->map(function ($monthsAgo) {
                $month = now()->startOfMonth()->subMonths($monthsAgo);
                $start = $month->copy();
                $end = $month->copy()->endOfMonth();

                return [
                    'label' => $month->format('M'),
                    'categories' => EducationCategory::whereBetween('created_at', [$start, $end])->count(),
                    'modules' => EducationModule::whereBetween('created_at', [$start, $end])->count(),
                    'lessons' => EducationLesson::whereBetween('created_at', [$start, $end])->count(),
                    'tools' => Tool::whereBetween('created_at', [$start, $end])->count(),
                ];
            })->values();

            $recommendedModule = EducationModule::where('status', 'published')->latest()->first();
            $upcomingEvents = $this->upcomingEvents();

            return view('admin.dashboard', compact(
                'statistics',
                'recentTools',
                'recentModules',
                'recentLessons',
                'recentJourneys',
                'recentJourneySteps',
                'recentActivity',
                'monthlyContent',
                'recommendedModule',
                'upcomingEvents'
            ));
        } catch (Throwable $exception) {
            Log::error('Admin dashboard loading failed.', ['exception' => $exception]);
            throw $exception;
        }
    }

    private function optionalModelCount(array $modelClasses): ?int
    {
        foreach ($modelClasses as $modelClass) {
            if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            try {
                $model = new $modelClass();
                if (Schema::hasTable($model->getTable())) {
                    return $modelClass::query()->count();
                }
            } catch (Throwable $exception) {
                Log::notice('Optional dashboard statistic unavailable.', [
                    'model' => $modelClass,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return null;
    }

    private function upcomingEvents(): Collection
    {
        $modelClasses = [
            \App\Models\CalendarEvent::class,
            \App\Models\FinancialFlowCalendarEvent::class,
            \App\Models\FinancialCalendarEvent::class,
            \App\Models\FinancialFlowEvent::class,
        ];

        foreach ($modelClasses as $modelClass) {
            if (!class_exists($modelClass) || !is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            try {
                $model = new $modelClass();
                $table = $model->getTable();

                if (!Schema::hasTable($table)) {
                    continue;
                }

                $dateColumn = collect(['starts_at', 'event_date', 'start_date', 'scheduled_at', 'date'])
                    ->first(fn ($column) => Schema::hasColumn($table, $column));

                $titleColumn = collect(['title', 'event_name', 'name'])
                    ->first(fn ($column) => Schema::hasColumn($table, $column));

                if (!$dateColumn || !$titleColumn) {
                    continue;
                }

                return $modelClass::query()
                    ->whereDate($dateColumn, '>=', now()->toDateString())
                    ->orderBy($dateColumn)
                    ->take(3)
                    ->get()
                    ->map(fn ($event) => [
                        'title' => $event->{$titleColumn} ?? 'Calendar event',
                        'date' => $event->{$dateColumn},
                        'time' => Schema::hasColumn($table, 'start_time') ? ($event->start_time ?? null) : null,
                    ]);
            } catch (Throwable $exception) {
                Log::notice('Upcoming calendar events unavailable.', [
                    'model' => $modelClass,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return collect();
    }
}
