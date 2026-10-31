<?php

namespace App\Http\Controllers\Admin\FinancialFlow;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:150'],
                'activity' => ['nullable', 'in:all,with_events,no_events'],
                'sort' => ['nullable', 'in:newest,oldest,name,events'],
                'month' => ['nullable', 'date_format:Y-m'],
            ]);

            $month = Carbon::createFromFormat(
                '!Y-m',
                $filters['month'] ?? now()->format('Y-m')
            )->startOfMonth();

            $monthStart = $month->toDateString();
            $monthEnd = $month->copy()->endOfMonth()->toDateString();

            $usersQuery = User::query()
                ->withCount('financialEvents')
                ->withMax('financialEvents', 'event_date')
                ->withCount([
                    'financialEvents as month_events_count' => function (
                        Builder $query
                    ) use ($monthStart, $monthEnd): void {
                        $query->whereBetween('event_date', [
                            $monthStart,
                            $monthEnd,
                        ]);
                    },
                ])
                ->when(
                    filled($filters['search'] ?? null),
                    function (Builder $query) use ($filters): void {
                        $term = trim($filters['search']);

                        $query->where(function (Builder $searchQuery) use ($term): void {
                            $searchQuery
                                ->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");

                            if (ctype_digit($term)) {
                                $searchQuery->orWhereKey((int) $term);
                            }
                        });
                    }
                )
                ->when(
                    ($filters['activity'] ?? 'all') === 'with_events',
                    fn (Builder $query) => $query->has('financialEvents')
                )
                ->when(
                    ($filters['activity'] ?? 'all') === 'no_events',
                    fn (Builder $query) => $query->doesntHave('financialEvents')
                );

            match ($filters['sort'] ?? 'newest') {
                'oldest' => $usersQuery->orderBy('created_at'),
                'name' => $usersQuery->orderBy('name'),
                'events' => $usersQuery->orderByDesc('financial_events_count'),
                default => $usersQuery->orderByDesc('id'),
            };

            $users = $usersQuery
                ->paginate(15)
                ->withQueryString();

            $totalUsers = User::query()->count();

            $usersWithEvents = User::query()
                ->has('financialEvents')
                ->count();

            $monthlyEventCount = FinancialEvent::query()
                ->whereBetween('event_date', [$monthStart, $monthEnd])
                ->count();

            return view('admin.financial-flow.calendar.index', [
                'users' => $users,
                'filters' => $filters,
                'month' => $month,
                'totalUsers' => $totalUsers,
                'usersWithEvents' => $usersWithEvents,
                'monthlyEventCount' => $monthlyEventCount,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load financial calendar directory.', [
                'filters' => $request->query(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    public function show(Request $request, User $user): View
    {
        try {
            $filters = $request->validate([
                'month' => ['nullable', 'date_format:Y-m'],
                'date' => ['nullable', 'date_format:Y-m-d'],
                'search' => ['nullable', 'string', 'max:150'],
                'category' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable', 'in:projected,cleared'],
            ]);

            $month = Carbon::createFromFormat(
                '!Y-m',
                $filters['month'] ?? now()->format('Y-m')
            )->startOfMonth();

            $monthStart = $month->toDateString();
            $monthEnd = $month->copy()->endOfMonth()->toDateString();

            $eventsQuery = $user->financialEvents()
                ->whereBetween('event_date', [$monthStart, $monthEnd])
                ->when(
                    filled($filters['search'] ?? null),
                    function (Builder $query) use ($filters): void {
                        $term = trim($filters['search']);

                        $query->where(function (Builder $searchQuery) use ($term): void {
                            $searchQuery
                                ->where('description', 'like', "%{$term}%")
                                ->orWhere('notes', 'like', "%{$term}%");
                        });
                    }
                )
                ->when(
                    filled($filters['category'] ?? null),
                    fn (Builder $query) => $query->where(
                        'category',
                        $filters['category']
                    )
                )
                ->when(
                    filled($filters['status'] ?? null),
                    fn (Builder $query) => $query->where(
                        'payment_status',
                        $filters['status']
                    )
                );

            $events = (clone $eventsQuery)
                ->orderBy('event_date')
                ->orderBy('id')
                ->get();

            $selectedDate = $filters['date']
                ?? $month->toDateString();

            // Only events belonging to this user and selected date.
            $selectedDateEvents = $events
                ->filter(
                    fn (FinancialEvent $event) =>
                        $event->event_date?->format('Y-m-d') === $selectedDate
                )
                ->values();

            $allUserEventsCount = $user->financialEvents()->count();

            $monthEventsCount = $user->financialEvents()
                ->whereBetween('event_date', [$monthStart, $monthEnd])
                ->count();

            $lastEventDate = $user->financialEvents()
                ->max('event_date');

            $categories = $user->financialEvents()
                ->whereNotNull('category')
                ->where('category', '<>', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');

            $summary = [
                'total' => $events->count(),
                'cleared' => $events->where('payment_status', 'cleared')->count(),
                'projected' => $events->where('payment_status', 'projected')->count(),
                'amount' => (float) $events->sum('amount'),
            ];

            return view('admin.financial-flow.calendar.show', [
                'user' => $user,
                'month' => $month,
                'filters' => $filters,
                'events' => $events,
                'selectedDate' => $selectedDate,
                'selectedDateEvents' => $selectedDateEvents,
                'allUserEventsCount' => $allUserEventsCount,
                'monthEventsCount' => $monthEventsCount,
                'lastEventDate' => $lastEventDate,
                'categories' => $categories,
                'summary' => $summary,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load user financial calendar.', [
                'user_id' => $user->getKey(),
                'filters' => $request->query(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }
}
