<?php

namespace App\Services\Admin\Reports;

use App\Models\Calculator;
use App\Models\CalculatorUsage;
use App\Models\EducationCategory;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Models\FinancialEvent;
use App\Models\JourneyStep;
use App\Models\Tool;
use App\Models\ToolJourney;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserLessonProgress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserActivityReportService
{
    private const PER_PAGE = 15;

    private function makeActivity(
        mixed $userId,
        string $module,
        ?string $activityType,
        ?string $subjectType,
        mixed $subjectId,
        mixed $date,
        ?string $detail = null,
        array $extra = []
    ): object {
        return (object) array_merge([
            'user_id' => $userId,
            'module' => $module,
            'activity_type' => $activityType,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'happened_at' => $date ? Carbon::parse($date) : null,
            'detail' => $detail,
            'ip_address' => null,
            'browser' => null,
            'operating_system' => null,
            'device_type' => null,
            'status_code' => null,
            'event' => null,
        ], $extra);
    }

    private function emailFingerprint(User $user): ?string
    {
        $email = strtolower(trim((string) $user->email));

        if ($email === '') {
            return null;
        }

        return hash_hmac(
            'sha256',
            $email,
            (string) config('app.key')
        );
    }

    /**
     * Collect activity from all configured sources.
     */
    private function activityStream(?User $user = null): Collection
    {
        $records = collect();

        // User activity logs
        $logs = UserActivityLog::query()
            ->when(
                $user,
                fn (Builder $query) => $query->where('user_id', $user->id)
            )
            ->get();

        foreach ($logs as $log) {
            $metadata = $log->metadata;

            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true) ?: [];
            }

            $records->push($this->makeActivity(
                $log->user_id,
                (string) $log->module,
                $log->activity_type,
                $log->subject_type,
                $log->subject_id,
                $log->created_at,
                data_get($metadata, 'title'),
                [
                    'source' => 'activity_log',
                    'source_id' => $log->id,
                ]
            ));
        }

        // Calculator usages
        $calculatorUsages = CalculatorUsage::query()
            ->when(
                $user,
                fn (Builder $query) => $query->where('user_id', $user->id)
            )
            ->get();

        foreach ($calculatorUsages as $usage) {
            $records->push($this->makeActivity(
                $usage->user_id,
                'calculators',
                $usage->event_type ?: 'calculated',
                'calculator',
                $usage->calculator_id,
                $usage->created_at,
                'Calculator #' . $usage->calculator_id,
                [
                    'source' => 'calculator_usage',
                    'source_id' => $usage->id,
                ]
            ));
        }

        // Financial events
        $financialEvents = FinancialEvent::query()
            ->when(
                $user,
                fn (Builder $query) => $query->where('user_id', $user->id)
            )
            ->get();

        foreach ($financialEvents as $event) {
            $records->push($this->makeActivity(
                $event->user_id,
                'calendar',
                'event_created',
                'financial_event',
                $event->id,
                $event->created_at,
                $event->description,
                [
                    'source' => 'financial_event',
                    'source_id' => $event->id,
                ]
            ));
        }

        // Completed lessons
        $completedLessons = UserLessonProgress::query()
            ->whereNotNull('completed_at')
            ->when(
                $user,
                fn (Builder $query) => $query->where('user_id', $user->id)
            )
            ->get();

        foreach ($completedLessons as $progress) {
            $records->push($this->makeActivity(
                $progress->user_id,
                'education',
                'lesson_completed',
                'lesson',
                $progress->lesson_id,
                $progress->completed_at,
                'Lesson #' . $progress->lesson_id . ' completed',
                [
                    'source' => 'lesson_progress',
                    'source_id' => $progress->id,
                ]
            ));
        }

        // Authentication records
        if (DB::getSchemaBuilder()->hasTable('loanengine_auth_activities')) {
            $authQuery = DB::table('loanengine_auth_activities');

            if ($user) {
                $fingerprint = $this->emailFingerprint($user);

                $authQuery->where(function ($query) use ($user, $fingerprint) {
                    $query->where('user_id', $user->id);

                    if ($fingerprint) {
                        $query->orWhere(
                            'email_fingerprint',
                            $fingerprint
                        );
                    }
                });
            }

            foreach ($authQuery->get() as $auth) {
                $metadata = $auth->metadata ?? [];

                if (is_string($metadata)) {
                    $metadata = json_decode($metadata, true) ?: [];
                }

                $event = (string) $auth->event;

                $detailParts = array_filter([
                    data_get($metadata, 'message'),
                    $auth->ip_address
                        ? 'IP: ' . $auth->ip_address
                        : null,
                    $auth->browser
                        ? 'Browser: ' . $auth->browser
                        : null,
                    $auth->operating_system
                        ? 'OS: ' . $auth->operating_system
                        : null,
                    $auth->device_type
                        ? 'Device: ' . $auth->device_type
                        : null,
                    $auth->status_code
                        ? 'Status: ' . $auth->status_code
                        : null,
                ]);

                $records->push($this->makeActivity(
                    $auth->user_id,
                    'authentication',
                    $event,
                    'authentication',
                    $auth->id,
                    $auth->created_at,
                    implode(' · ', $detailParts),
                    [
                        'source' => 'authentication',
                        'source_id' => $auth->id,
                        'event' => $event,
                        'ip_address' => $auth->ip_address,
                        'browser' => $auth->browser,
                        'operating_system' => $auth->operating_system,
                        'device_type' => $auth->device_type,
                        'status_code' => $auth->status_code,
                        'risk_level' => $auth->risk_level ?? null,
                        'country' => $auth->country ?? null,
                        'city' => $auth->city ?? null,
                    ]
                ));
            }
        }

        return $records
            ->filter(fn ($record) => !empty($record->happened_at))
            ->sortByDesc(fn ($record) => $record->happened_at)
            ->values();
    }

    /**
     * Apply activity filters.
     */
    private function filteredStream(
        array $filters,
        ?User $user = null
    ): Collection {
        return $this->activityStream($user)
            ->filter(function ($record) use ($filters) {
                $search = trim((string) ($filters['search'] ?? ''));

                if ($search !== '') {
                    $haystack = implode(' ', array_filter([
                        $record->detail,
                        $record->activity_type,
                        $record->subject_type,
                        $record->subject_id,
                        $record->ip_address,
                        $record->browser,
                        $record->operating_system,
                        $record->device_type,
                    ]));

                    if (stripos($haystack, $search) === false) {
                        return false;
                    }
                }

                if (
                    filled($filters['module'] ?? null)
                    && $record->module !== $filters['module']
                ) {
                    return false;
                }

                if (
                    filled($filters['activity_type'] ?? null)
                    && $record->activity_type !== $filters['activity_type']
                ) {
                    return false;
                }

                if (
                    filled($filters['from'] ?? null)
                    && $record->happened_at->toDateString() < $filters['from']
                ) {
                    return false;
                }

                if (
                    filled($filters['to'] ?? null)
                    && $record->happened_at->toDateString() > $filters['to']
                ) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    /**
     * Public filtered activity collection for Excel export.
     * No pagination is applied.
     */
    public function exportActivities(array $filters): Collection
    {
        return $this->filteredStream($filters);
    }

    private function paginateCollection(
        Collection $records,
        int $perPage = self::PER_PAGE
    ): LengthAwarePaginator {
        $page = Paginator::resolveCurrentPage();

        return new Paginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    private function normalizeSubjectType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        return strtolower(class_basename($type));
    }

    private function resolveSubjectNames(Collection $records): Collection
    {
        $groups = $records->groupBy(function ($record) {
            return $this->normalizeSubjectType($record->subject_type);
        });

        $map = [
            'tool' => [Tool::class, 'name'],
            'tooljourney' => [ToolJourney::class, 'name'],
            'journeystep' => [JourneyStep::class, 'title'],
            'educationlesson' => [EducationLesson::class, 'title'],
            'lesson' => [EducationLesson::class, 'title'],
            'educationmodule' => [EducationModule::class, 'title'],
            'educationcategory' => [EducationCategory::class, 'name'],
            'financialevent' => [FinancialEvent::class, 'description'],
            'calculator' => [Calculator::class, 'name'],
        ];

        foreach ($groups as $type => $items) {
            if (!isset($map[$type])) {
                continue;
            }

            [$model, $column] = $map[$type];

            $ids = $items
                ->pluck('subject_id')
                ->filter()
                ->unique()
                ->values();

            if ($ids->isEmpty()) {
                continue;
            }

            $names = $model::query()
                ->whereIn('id', $ids)
                ->pluck($column, 'id');

            foreach ($items as $item) {
                $item->subject_name = $names->get($item->subject_id)
                    ?: ($item->detail ?: '—');
            }
        }

        foreach ($records as $record) {
            $record->subject_name ??= $record->detail ?: '—';
        }

        return $records;
    }

    public function resolveTimelineSubjectNames(
        LengthAwarePaginator $records
    ): LengthAwarePaginator {
        $this->resolveSubjectNames(collect($records->items()));

        return $records;
    }

    /**
     * Paginated users directory.
     */
    public function activityDirectory(array $filters): LengthAwarePaginator
    {
        $users = User::query()
            ->where('role', '!=', 'admin')
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $term = trim($filters['search']);

                $query->where(function ($where) use ($term) {
                    $where->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%");

                    if (ctype_digit($term)) {
                        $where->orWhere('id', (int) $term);
                    }
                });
            })
            ->get();

        $activity = $this->filteredStream($filters)->groupBy('user_id');

        $rows = $users->map(function ($user) use ($activity) {
            $items = $activity->get($user->id, collect());

            $user->total_activities = $items->count();
            $user->education_count = $items->where('module', 'education')->count();
            $user->tools_count = $items->where('module', 'tools')->count();
            $user->calculators_count = $items->where('module', 'calculators')->count();
            $user->calendar_count = $items->where('module', 'calendar')->count();
            $user->authentication_count = $items->where('module', 'authentication')->count();
            $user->last_active = $items->first()?->happened_at;

            return $user;
        });

        if (($filters['user_status'] ?? 'all') === 'active') {
            $rows = $rows->filter(fn ($user) => $user->total_activities > 0);
        } elseif (($filters['user_status'] ?? 'all') === 'inactive') {
            $rows = $rows->filter(fn ($user) => $user->total_activities === 0);
        }

        $rows = match ($filters['sort'] ?? 'latest') {
            'oldest' => $rows->sortBy('created_at'),
            'name' => $rows->sortBy('name'),
            'most_active' => $rows->sortByDesc('total_activities'),
            default => $rows->sortByDesc('last_active'),
        };

        return $this->paginateCollection($rows->values(), 20);
    }

    /**
     * Return ALL users matching the filters.
     * This method intentionally does not paginate.
     */
    public function exportDirectory(array $filters): Collection
    {
        $users = User::query()
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $term = trim($filters['search']);

                $query->where(function ($where) use ($term) {
                    $where->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%");

                    if (ctype_digit($term)) {
                        $where->orWhere('id', (int) $term);
                    }
                });
            })
            ->get();

        $activity = $this->filteredStream($filters)->groupBy('user_id');

        $rows = $users->map(function ($user) use ($activity) {
            $items = $activity->get($user->id, collect());

            $user->total_activities = $items->count();
            $user->education_count = $items->where('module', 'education')->count();
            $user->tools_count = $items->where('module', 'tools')->count();
            $user->calculators_count = $items->where('module', 'calculators')->count();
            $user->calendar_count = $items->where('module', 'calendar')->count();
            $user->authentication_count = $items->where('module', 'authentication')->count();
            $user->last_active = $items->first()?->happened_at;

            return $user;
        });

        if (($filters['user_status'] ?? 'all') === 'active') {
            $rows = $rows->filter(fn ($user) => $user->total_activities > 0);
        } elseif (($filters['user_status'] ?? 'all') === 'inactive') {
            $rows = $rows->filter(fn ($user) => $user->total_activities === 0);
        }

        $rows = match ($filters['sort'] ?? 'latest') {
            'oldest' => $rows->sortBy('created_at'),
            'name' => $rows->sortBy('name'),
            'most_active' => $rows->sortByDesc('total_activities'),
            default => $rows->sortByDesc('last_active'),
        };

        return $rows->values();
    }

    public function dashboardStats(): array
    {
        $totalUsers = User::count();
        $records = $this->activityStream();

        return [
            'total_users' => $totalUsers,
            'active_users' => $records->pluck('user_id')->filter()->unique()->count(),
            'total_activities' => $records->count(),
            'average' => $totalUsers
                ? round($records->count() / $totalUsers, 2)
                : 0,
        ];
    }

    public function activityTypes(): array
    {
        return $this->activityStream()
            ->pluck('activity_type')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function userSummary(User $user): array
    {
        $records = $this->activityStream($user);
        $calendar = FinancialEvent::query()->where('user_id', $user->id);

        $authRecords = $records->where('module', 'authentication');

        return [
            'total' => $records->count(),
            'education' => $records->where('module', 'education')->count(),
            'tools' => $records->where('module', 'tools')->count(),
            'calculators' => $records->where('module', 'calculators')->count(),
            'calendar' => $records->where('module', 'calendar')->count(),
            'authentication' => $authRecords->count(),
            'total_logins' => $authRecords->where('event', 'login_success')->count(),
            'failed_logins' => $authRecords->where('event', 'login_failed')->count(),
            'last_login' => $authRecords
                ->where('event', 'login_success')
                ->first()?->happened_at,
            'completed_lessons' => UserLessonProgress::query()
                ->where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->count(),
            'calendar_total' => (clone $calendar)->count(),
            'calendar_cleared' => (clone $calendar)
                ->where('payment_status', 'cleared')
                ->count(),
            'calendar_projected' => (clone $calendar)
                ->where('payment_status', 'projected')
                ->count(),
            'last_active' => $records->first()?->happened_at,
        ];
    }

    public function recentActivities(User $user, int $limit = 6): Collection
    {
        return $this->resolveSubjectNames(
            $this->activityStream($user)->take($limit)->values()
        );
    }

    public function chartData(User $user, int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $records = $this->activityStream($user)
            ->filter(fn ($record) => $record->happened_at >= $start);

        $labels = collect(range(0, $days - 1))
            ->map(fn ($index) => $start->copy()->addDays($index)->format('d M'))
            ->all();

        $modules = [
            'education',
            'tools',
            'calculators',
            'calendar',
            'authentication',
        ];

        $series = [];

        foreach ($modules as $module) {
            $series[$module] = collect(range(0, $days - 1))
                ->map(function ($index) use ($start, $records, $module) {
                    $date = $start->copy()->addDays($index)->toDateString();

                    return $records
                        ->filter(fn ($record) =>
                            $record->module === $module
                            && Carbon::parse($record->happened_at)->toDateString() === $date
                        )
                        ->count();
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
            ['label' => 'Authentication', 'value' => $summary['authentication']],
        ];
    }

    public function userRecords(
        User $user,
        string $tab,
        array $filters
    ): LengthAwarePaginator {
        if ($tab === 'calculators') {
            return CalculatorUsage::query()
                ->where('user_id', $user->id)
                ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                    $term = trim($filters['search']);

                    $query->where(function ($where) use ($term) {
                        $where->where('event_type', 'like', "%{$term}%")
                            ->orWhere('calculator_id', 'like', "%{$term}%")
                            ->orWhere('status', 'like', "%{$term}%");
                    });
                })
                ->when(
                    filled($filters['activity_type'] ?? null),
                    fn ($query) => $query->where('event_type', $filters['activity_type'])
                )
                ->when(
                    filled($filters['from'] ?? null),
                    fn ($query) => $query->whereDate('created_at', '>=', $filters['from'])
                )
                ->when(
                    filled($filters['to'] ?? null),
                    fn ($query) => $query->whereDate('created_at', '<=', $filters['to'])
                )
                ->latest()
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        if ($tab === 'calendar') {
            return FinancialEvent::query()
                ->where('user_id', $user->id)
                ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                    $term = trim($filters['search']);

                    $query->where(function ($where) use ($term) {
                        $where->where('description', 'like', "%{$term}%")
                            ->orWhere('notes', 'like', "%{$term}%");
                    });
                })
                ->when(
                    filled($filters['category'] ?? null),
                    fn ($query) => $query->where('category', $filters['category'])
                )
                ->when(
                    filled($filters['status'] ?? null),
                    fn ($query) => $query->where('payment_status', $filters['status'])
                )
                ->when(
                    filled($filters['from'] ?? null),
                    fn ($query) => $query->whereDate('event_date', '>=', $filters['from'])
                )
                ->when(
                    filled($filters['to'] ?? null),
                    fn ($query) => $query->whereDate('event_date', '<=', $filters['to'])
                )
                ->orderByDesc('event_date')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        if ($tab === 'timeline') {
            $records = $this->filteredStream($filters, $user);

            return $this->paginateCollection(
                $this->resolveSubjectNames($records),
                self::PER_PAGE
            );
        }

        $records = UserActivityLog::query()
            ->where('user_id', $user->id)
            ->where('module', $tab)
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $term = trim($filters['search']);

                $query->where(function ($where) use ($term) {
                    $where->where('activity_type', 'like', "%{$term}%")
                        ->orWhere('subject_type', 'like', "%{$term}%")
                        ->orWhere('subject_id', 'like', "%{$term}%");
                });
            })
            ->when(
                filled($filters['activity_type'] ?? null),
                fn ($query) => $query->where('activity_type', $filters['activity_type'])
            )
            ->when(
                filled($filters['from'] ?? null),
                fn ($query) => $query->whereDate('created_at', '>=', $filters['from'])
            )
            ->when(
                filled($filters['to'] ?? null),
                fn ($query) => $query->whereDate('created_at', '<=', $filters['to'])
            )
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $this->resolveSubjectNames(collect($records->items()));

        return $records;
    }
    

public function dashboardChartData(int $days = 30): array
{
    $start = now()->subDays($days - 1)->startOfDay();

    $records = $this->activityStream()
        ->filter(fn ($record) => $record->happened_at >= $start);

    $labels = [];
    $dates = [];

    for ($i = 0; $i < $days; $i++) {
        $date = $start->copy()->addDays($i);

        $dates[] = $date->toDateString();
        $labels[] = $date->format('d M');
    }

    $modules = [
        'education',
        'tools',
        'calculators',
        'calendar',
        'authentication',
    ];

    $series = [];

    foreach ($modules as $module) {
        $series[$module] = collect($dates)
            ->map(function ($date) use ($records, $module) {
                return $records
                    ->filter(fn ($record) =>
                        $record->module === $module
                        && $record->happened_at->toDateString() === $date
                    )
                    ->count();
            })
            ->all();
    }

    return [
        'labels' => $labels,
        'series' => $series,
    ];
}

public function dashboardModuleBreakdown(): array
{
    $records = $this->activityStream();

    $counts = $records->countBy('module');

    return [
        [
            'key' => 'education',
            'label' => 'Education',
            'value' => (int) $counts->get('education', 0),
        ],
        [
            'key' => 'tools',
            'label' => 'Tools',
            'value' => (int) $counts->get('tools', 0),
        ],
        [
            'key' => 'calculators',
            'label' => 'Calculators',
            'value' => (int) $counts->get('calculators', 0),
        ],
        [
            'key' => 'calendar',
            'label' => 'Calendar',
            'value' => (int) $counts->get('calendar', 0),
        ],
        [
            'key' => 'authentication',
            'label' => 'Authentication',
            'value' => (int) $counts->get('authentication', 0),
        ],
    ];
}




}