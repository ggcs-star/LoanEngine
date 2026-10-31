<?php

namespace App\Services\Admin\Reports;

use App\Models\FinancialEvent;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\CalculatorUsage;
use App\Models\Calculator;
use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use App\Models\UserLessonProgress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserActivityReportService
{
    /**
     * Combine existing tracking sources into one activity stream.
     * Login activity is intentionally not fabricated.
     */
    private function activityStream(?int $userId = null)
    {
        $logs = DB::table('user_activity_logs')
            ->selectRaw("
                user_id,
                module,
                activity_type,
                subject_type,
                subject_id,
                created_at AS happened_at,
                COALESCE(
                    JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.title')),
                    ''
                ) AS detail
            ")
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        $calculators = DB::table('calculator_usages')
            ->selectRaw("
                user_id,
                'calculators' AS module,
                COALESCE(event_type, 'calculated') AS activity_type,
                'calculator' AS subject_type,
                calculator_id AS subject_id,
                created_at AS happened_at,
                CONCAT('Calculator #', calculator_id) AS detail
            ")
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        $calendar = DB::table('financial_events')
            ->selectRaw("
                user_id,
                'calendar' AS module,
                'event_created' AS activity_type,
                'financial_event' AS subject_type,
                id AS subject_id,
                created_at AS happened_at,
                description AS detail
            ")
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        $completedLessons = DB::table('user_lesson_progress')
            ->whereNotNull('completed_at')
            ->selectRaw("
                user_id,
                'education' AS module,
                'lesson_completed' AS activity_type,
                'lesson' AS subject_type,
                lesson_id AS subject_id,
                completed_at AS happened_at,
                CONCAT('Lesson #', lesson_id, ' completed') AS detail
            ")
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        return $logs
            ->unionAll($calculators)
            ->unionAll($calendar)
            ->unionAll($completedLessons);
    }

    /**
     * Normalize subject types so both short names and model class names work.
     */
    private function normalizeSubjectType(?string $type): string
    {
        $type = strtolower(class_basename((string) $type));

        return match ($type) {
            'tool_journey' => 'tooljourney',
            'journey_step' => 'journeystep',
            'education_lesson' => 'educationlesson',
            'education_module' => 'educationmodule',
            'education_category' => 'educationcategory',
            'financialevent',
            'financial_event' => 'financialevent',
            default => $type,
        };
    }

    /**
     * Resolve subject names for paginated activity records.
     * Uses grouped Eloquent queries instead of one query per row.
     */
    private function resolveSubjectNames(LengthAwarePaginator $records): void
    {
        $models = [
            'tool' => [Tool::class, 'name'],

            'journey' => [ToolJourney::class, 'name'],
            'tooljourney' => [ToolJourney::class, 'name'],

            'step' => [JourneyStep::class, 'title'],
            'journeystep' => [JourneyStep::class, 'title'],

            'lesson' => [EducationLesson::class, 'title'],
            'educationlesson' => [EducationLesson::class, 'title'],

            'module' => [EducationModule::class, 'title'],
            'educationmodule' => [EducationModule::class, 'title'],

            'category' => [EducationCategory::class, 'name'],
            'educationcategory' => [EducationCategory::class, 'name'],

            'financialevent' => [FinancialEvent::class, 'description'],

            'calculator' => [Calculator::class, 'name'],
        ];

        $rows = $records->getCollection();

        $grouped = $rows
            ->filter(fn ($row) =>
                filled($row->subject_type ?? null) &&
                filled($row->subject_id ?? null)
            )
            ->groupBy(fn ($row) =>
                $this->normalizeSubjectType($row->subject_type)
            );

        $names = [];

        foreach ($grouped as $type => $items) {
            if (!isset($models[$type])) {
                continue;
            }

            [$model, $column] = $models[$type];

            $ids = $items
                ->pluck('subject_id')
                ->filter()
                ->unique()
                ->values();

            if ($ids->isEmpty()) {
                continue;
            }

            $names[$type] = $model::query()
                ->whereKey($ids)
                ->pluck($column, 'id')
                ->all();
        }

        $rows->transform(function ($row) use ($names) {
            $type = $this->normalizeSubjectType(
                $row->subject_type ?? ''
            );

            $id = $row->subject_id ?? null;

            $row->subject_name = $names[$type][$id] ?? '—';

            return $row;
        });
    }

    /**
     * Existing timeline resolver kept as a wrapper.
     */
    private function resolveTimelineSubjectNames(
        LengthAwarePaginator $records
    ): void {
        $this->resolveSubjectNames($records);
    }

    private function filteredStream(
        array $filters,
        ?int $userId = null
    ) {
        return DB::query()
            ->fromSub($this->activityStream($userId), 'activity_feed')
            ->when(
                filled($filters['search'] ?? null),
                function ($q) use ($filters) {
                    $term = trim($filters['search']);

                    $q->where(function ($w) use ($term) {
                        $w->where('detail', 'like', "%{$term}%")
                            ->orWhere('activity_type', 'like', "%{$term}%")
                            ->orWhere('subject_type', 'like', "%{$term}%");

                        if (ctype_digit($term)) {
                            $w->orWhere('subject_id', (int) $term);
                        }
                    });
                }
            )
            ->when(
                filled($filters['module'] ?? null),
                fn ($q) => $q->where('module', $filters['module'])
            )
            ->when(
                filled($filters['activity_type'] ?? null),
                fn ($q) => $q->where(
                    'activity_type',
                    $filters['activity_type']
                )
            )
            ->when(
                filled($filters['from'] ?? null),
                fn ($q) => $q->whereDate(
                    'happened_at',
                    '>=',
                    $filters['from']
                )
            )
            ->when(
                filled($filters['to'] ?? null),
                fn ($q) => $q->whereDate(
                    'happened_at',
                    '<=',
                    $filters['to']
                )
            );
    }

    /**
     * One row per registered user.
     */
    private function userDirectoryQuery(array $filters)
    {
        $activityFilters = $filters;

        unset(
            $activityFilters['search'],
            $activityFilters['user_status'],
            $activityFilters['sort']
        );

        $aggregated = $this->filteredStream($activityFilters)
            ->selectRaw("
                user_id,
                COUNT(*) AS total_activities,
                SUM(module = 'education') AS education_count,
                SUM(module = 'tools') AS tools_count,
                SUM(module = 'calculators') AS calculators_count,
                SUM(module = 'calendar') AS calendar_count,
                MAX(happened_at) AS last_active
            ")
            ->groupBy('user_id');

        $query = User::query()
            ->leftJoinSub(
                $aggregated,
                'report_activity',
                fn ($join) => $join->on(
                    'users.id',
                    '=',
                    'report_activity.user_id'
                )
            )
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.mobile',
                'users.created_at',
            ])
            ->selectRaw("
                COALESCE(report_activity.education_count, 0)
                    AS education_count,

                COALESCE(report_activity.tools_count, 0)
                    AS tools_count,

                COALESCE(report_activity.calculators_count, 0)
                    AS calculators_count,

                COALESCE(report_activity.calendar_count, 0)
                    AS calendar_count,

                COALESCE(report_activity.total_activities, 0)
                    AS total_activities,

                report_activity.last_active AS last_active
            ");

        if (filled($filters['search'] ?? null)) {
            $term = trim($filters['search']);

            $query->where(function ($where) use ($term) {
                $where->where('users.name', 'like', "%{$term}%")
                    ->orWhere('users.email', 'like', "%{$term}%")
                    ->orWhere('users.mobile', 'like', "%{$term}%");

                if (ctype_digit($term)) {
                    $where->orWhere('users.id', (int) $term);
                }
            });
        }

        if (($filters['user_status'] ?? 'all') === 'active') {
            $query->whereNotNull('report_activity.user_id');
        } elseif (($filters['user_status'] ?? 'all') === 'inactive') {
            $query->whereNull('report_activity.user_id');
        }

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->orderBy('users.created_at'),
            'name' => $query->orderBy('users.name'),
            'most_active' => $query->orderByDesc('total_activities'),
            default => $query
                ->orderByRaw('report_activity.last_active IS NULL ASC')
                ->orderByDesc('report_activity.last_active')
                ->orderByDesc('users.id'),
        };

        return $query;
    }

    public function activityDirectory(array $filters): LengthAwarePaginator
    {
        return $this->userDirectoryQuery($filters)
            ->paginate(20)
            ->withQueryString();
    }

    public function exportDirectory(array $filters): iterable
    {
        return $this->userDirectoryQuery($filters)->cursor();
    }

    public function dashboardStats(): array
    {
        $totalUsers = User::count();

        $activeUsers = DB::query()
            ->fromSub($this->activityStream(), 'tracked')
            ->distinct()
            ->count('user_id');

        $totalActivities = DB::query()
            ->fromSub($this->activityStream(), 'tracked')
            ->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'total_activities' => $totalActivities,
            'average' => $totalUsers
                ? round($totalActivities / $totalUsers, 2)
                : 0,
        ];
    }

    public function activityTypes(): array
    {
        return DB::query()
            ->fromSub($this->activityStream(), 'feed')
            ->whereNotNull('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type')
            ->all();
    }

    public function userSummary(User $user): array
    {
        $counts = DB::query()
            ->fromSub($this->activityStream($user->id), 'feed')
            ->selectRaw("
                COUNT(*) AS total,
                SUM(module = 'education') AS education,
                SUM(module = 'tools') AS tools,
                SUM(module = 'calculators') AS calculators,
                SUM(module = 'calendar') AS calendar
            ")
            ->first();

        $completed = UserLessonProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        $calendar = FinancialEvent::where('user_id', $user->id);

        $last = DB::query()
            ->fromSub($this->activityStream($user->id), 'feed')
            ->max('happened_at');

        return [
            'total' => (int) ($counts->total ?? 0),
            'education' => (int) ($counts->education ?? 0),
            'tools' => (int) ($counts->tools ?? 0),
            'calculators' => (int) ($counts->calculators ?? 0),
            'calendar' => (int) ($counts->calendar ?? 0),
            'completed_lessons' => $completed,
            'calendar_total' => (clone $calendar)->count(),
            'calendar_cleared' => (clone $calendar)
                ->where('payment_status', 'cleared')
                ->count(),
            'calendar_projected' => (clone $calendar)
                ->where('payment_status', 'projected')
                ->count(),
            'last_active' => $last,
        ];
    }

    public function chartData(User $user, int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = DB::query()
            ->fromSub($this->activityStream($user->id), 'feed')
            ->where('happened_at', '>=', $start)
            ->selectRaw("
                DATE(happened_at) AS activity_date,
                module,
                COUNT(*) AS total
            ")
            ->groupBy('activity_date', 'module')
            ->orderBy('activity_date')
            ->get();

        $labels = collect(range(0, $days - 1))
            ->map(fn ($i) => $start->copy()->addDays($i)->format('d M'))
            ->all();

        $modules = ['education', 'tools', 'calculators', 'calendar'];
        $series = [];

        foreach ($modules as $module) {
            $map = $rows
                ->where('module', $module)
                ->keyBy(fn ($r) => substr($r->activity_date, 0, 10));

            $series[$module] = collect(range(0, $days - 1))
                ->map(function ($i) use ($start, $map) {
                    $key = $start->copy()->addDays($i)->toDateString();

                    return (int) ($map[$key]->total ?? 0);
                })
                ->all();
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    public function moduleBreakdown(User $user): array
    {
        $summary = $this->userSummary($user);

        return [
            ['label' => 'Education', 'value' => $summary['education']],
            ['label' => 'Tools', 'value' => $summary['tools']],
            ['label' => 'Calculators', 'value' => $summary['calculators']],
            ['label' => 'Calendar', 'value' => $summary['calendar']],
        ];
    }

    public function userRecords(
        User $user,
        string $tab,
        array $filters
    ): LengthAwarePaginator {
        /*
         * CALCULATORS
         */
        if ($tab === 'calculators') {
            return CalculatorUsage::query()
                ->with(['calculator:id,name'])
                ->where('user_id', $user->id)
                ->when(
                    filled($filters['search'] ?? null),
                    function ($q) use ($filters) {
                        $s = trim($filters['search']);

                        $q->where(function ($w) use ($s) {
                            $w->where('calculator_id', 'like', "%{$s}%")
                                ->orWhere('event_type', 'like', "%{$s}%")
                                ->orWhere('status', 'like', "%{$s}%");
                        });
                    }
                )
                ->when(
                    filled($filters['activity_type'] ?? null),
                    fn ($q) => $q->where(
                        'event_type',
                        $filters['activity_type']
                    )
                )
                ->when(
                    filled($filters['from'] ?? null),
                    fn ($q) => $q->whereDate(
                        'created_at',
                        '>=',
                        $filters['from']
                    )
                )
                ->when(
                    filled($filters['to'] ?? null),
                    fn ($q) => $q->whereDate(
                        'created_at',
                        '<=',
                        $filters['to']
                    )
                )
                ->latest()
                ->paginate(15)
                ->withQueryString();
        }

        /*
         * CALENDAR
         */
        if ($tab === 'calendar') {
            return FinancialEvent::query()
                ->where('user_id', $user->id)
                ->when(
                    filled($filters['search'] ?? null),
                    function ($q) use ($filters) {
                        $s = trim($filters['search']);

                        $q->where(fn ($w) => $w
                            ->where('description', 'like', "%{$s}%")
                            ->orWhere('notes', 'like', "%{$s}%"));
                    }
                )
                ->when(
                    filled($filters['category'] ?? null),
                    fn ($q) => $q->where(
                        'category',
                        $filters['category']
                    )
                )
                ->when(
                    filled($filters['status'] ?? null),
                    fn ($q) => $q->where(
                        'payment_status',
                        $filters['status']
                    )
                )
                ->when(
                    filled($filters['from'] ?? null),
                    fn ($q) => $q->whereDate(
                        'event_date',
                        '>=',
                        $filters['from']
                    )
                )
                ->when(
                    filled($filters['to'] ?? null),
                    fn ($q) => $q->whereDate(
                        'event_date',
                        '<=',
                        $filters['to']
                    )
                )
                ->orderByDesc('event_date')
                ->paginate(15)
                ->withQueryString();
        }

        /*
         * TIMELINE
         */
        if ($tab === 'timeline') {
            $records = DB::query()
                ->fromSub($this->activityStream($user->id), 'feed')
                ->when(
                    filled($filters['search'] ?? null),
                    function ($q) use ($filters) {
                        $s = trim($filters['search']);

                        $q->where(fn ($w) => $w
                            ->where('detail', 'like', "%{$s}%")
                            ->orWhere('activity_type', 'like', "%{$s}%")
                            ->orWhere('subject_type', 'like', "%{$s}%"));
                    }
                )
                ->when(
                    filled($filters['activity_type'] ?? null),
                    fn ($q) => $q->where(
                        'activity_type',
                        $filters['activity_type']
                    )
                )
                ->when(
                    filled($filters['from'] ?? null),
                    fn ($q) => $q->whereDate(
                        'happened_at',
                        '>=',
                        $filters['from']
                    )
                )
                ->when(
                    filled($filters['to'] ?? null),
                    fn ($q) => $q->whereDate(
                        'happened_at',
                        '<=',
                        $filters['to']
                    )
                )
                ->orderByDesc('happened_at')
                ->paginate(15)
                ->withQueryString();

            $this->resolveSubjectNames($records);

            return $records;
        }

        /*
         * EDUCATION AND TOOLS
         */
        $records = UserActivityLog::query()
            ->where('user_id', $user->id)
            ->where('module', $tab)
            ->when(
                filled($filters['search'] ?? null),
                function ($q) use ($filters) {
                    $s = trim($filters['search']);

                    $q->where(fn ($w) => $w
                        ->where('activity_type', 'like', "%{$s}%")
                        ->orWhere('subject_type', 'like', "%{$s}%")
                        ->orWhere('subject_id', 'like', "%{$s}%"));
                }
            )
            ->when(
                filled($filters['activity_type'] ?? null),
                fn ($q) => $q->where(
                    'activity_type',
                    $filters['activity_type']
                )
            )
            ->when(
                filled($filters['from'] ?? null),
                fn ($q) => $q->whereDate(
                    'created_at',
                    '>=',
                    $filters['from']
                )
            )
            ->when(
                filled($filters['to'] ?? null),
                fn ($q) => $q->whereDate(
                    'created_at',
                    '<=',
                    $filters['to']
                )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Resolve the names for Education and Tools as well.
        $this->resolveSubjectNames($records);

        return $records;
    }
}
