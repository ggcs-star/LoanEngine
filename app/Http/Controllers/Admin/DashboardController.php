<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Throwable;

class DashboardController extends Controller
{
    public function index(): View
    {
        try {
            $statistics = [
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

            $recentTools = Tool::latest()->take(5)->get();
            $recentModules = EducationModule::latest()->take(5)->get();
            $recentLessons = EducationLesson::latest()->take(5)->get();
            $recentJourneys = ToolJourney::latest()->take(5)->get();
            $recentJourneySteps = JourneyStep::latest()->take(5)->get();

            $recentActivity = collect()
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
                    'categories' => EducationCategory::whereBetween(
                        'created_at', [$start, $end]
                    )->count(),
                    'modules' => EducationModule::whereBetween(
                        'created_at', [$start, $end]
                    )->count(),
                    'lessons' => EducationLesson::whereBetween(
                        'created_at', [$start, $end]
                    )->count(),
                    'tools' => Tool::whereBetween(
                        'created_at', [$start, $end]
                    )->count(),
                ];
            })->values();
            $recommendedModule = EducationModule::where('status', 'published')
                ->latest()
                ->first();

            return view('admin.dashboard', compact(
                'statistics',
                'recentTools',
                'recentModules',
                'recentLessons',
                'recentJourneys',
                'recentJourneySteps',
                'recentActivity',
                'monthlyContent',
                'recommendedModule'
            ));
        } catch (Throwable $exception) {
            Log::error('Admin dashboard loading failed.', [
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
