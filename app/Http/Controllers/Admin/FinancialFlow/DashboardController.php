<?php

namespace App\Http\Controllers\Admin\FinancialFlow;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvent;
use App\Models\User;
use App\Services\FinancialFlow\FinancialAnalyticsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FinancialAnalyticsService $analytics
    ) {}

    public function index(Request $request)
    {
        try {
            $filters = $request->validate([
                'year' => ['nullable', 'integer', 'min:2000', 'max:2200'],
                'month' => ['nullable', 'integer', 'between:1,12'],
                'category' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable', 'string', Rule::in(['projected', 'cleared'])],
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
                'search' => ['nullable', 'string', 'max:150'],
                'date' => ['nullable', 'date_format:Y-m-d'],
            ]);

            $month = Carbon::create(
                (int) ($filters['year'] ?? now()->year),
                (int) ($filters['month'] ?? now()->month),
                1
            )->startOfMonth();

            $userId = filled($filters['user_id'] ?? null)
                ? (int) $filters['user_id']
                : null;

            // All analytics share the same selected-user scope.
            $monthEvents = $this->analytics->monthlyEvents($month, $userId);

            $events = $this->filteredQuery($month, $filters)
                ->orderByDesc('event_date')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();

            $selectedDate = $filters['date'] ?? $month->toDateString();

            if (substr($selectedDate, 0, 7) !== $month->format('Y-m')) {
                $selectedDate = $month->toDateString();
            }

            $calendarEvents = $monthEvents->map(fn ($event) => [
                'id' => $event->id,
                'date' => $event->event_date?->format('Y-m-d'),
                'description' => $event->description,
                'amount' => (float) $event->amount,
                'category' => $event->category,
                'status' => $event->payment_status,
                'user' => $event->user?->name ?? 'Unknown user',
                'is_recurring' => (bool) $event->is_recurring,
                'frequency' => $event->recurrence_frequency,
            ])->values();

            $selectedDateEvents = $monthEvents
                ->filter(fn ($event) =>
                    $event->event_date?->format('Y-m-d') === $selectedDate)
                ->values();

            return view('admin.financial-flow.dashboard', [
                'month' => $month,
                'filters' => $filters,
                'selectedUser' => $userId
                    ? User::query()->find($userId)
                    : null,
                'summary' => $this->analytics->summary($monthEvents),
                'categories' => $this->analytics->categories($monthEvents),
                'categoryBreakdown' => $this->analytics->categoryBreakdown($monthEvents),
                'weeklyBreakdown' => $this->analytics->weeklyBreakdown($monthEvents, $month),
                'monthlyTrend' => $this->analytics->monthlyTrend($month, $userId),
                'calendarEvents' => $calendarEvents,
                'selectedDate' => $selectedDate,
                'selectedDateEvents' => $selectedDateEvents,
                'events' => $events,
                'users' => User::query()
                    ->select(['id', 'name', 'email'])
                    ->orderBy('name')
                    ->get(),
                'statuses' => FinancialEvent::query()
                    ->whereNotNull('payment_status')
                    ->distinct()
                    ->orderBy('payment_status')
                    ->pluck('payment_status'),
            ]);
        } catch (Throwable $exception) {
            Log::error('Financial Flow dashboard failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return back()->withInput()->with(
                'error',
                'Financial Flow dashboard load nahi ho saka. Please try again.'
            );
        }
    }

    public function export(Request $request): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $filters = $request->validate([
                'year' => ['nullable', 'integer', 'min:2000', 'max:2200'],
                'month' => ['nullable', 'integer', 'between:1,12'],
                'category' => ['nullable', 'string', 'max:255'],
                'status' => ['nullable', 'string', Rule::in(['projected', 'cleared'])],
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
                'search' => ['nullable', 'string', 'max:150'],
            ]);

            $month = Carbon::create(
                (int) ($filters['year'] ?? now()->year),
                (int) ($filters['month'] ?? now()->month),
                1
            )->startOfMonth();

            $query = $this->filteredQuery($month, $filters)
                ->with('user:id,name,email')
                ->orderBy('event_date')
                ->orderBy('id');

            return response()->streamDownload(function () use ($query) {
                $handle = fopen('php://output', 'w');

                if ($handle === false) {
                    throw new \RuntimeException('Unable to open CSV stream.');
                }

                try {
                    fwrite($handle, "\xEF\xBB\xBF");

                    fputcsv($handle, [
                        'ID', 'User', 'Email', 'Description', 'Category',
                        'Amount', 'Payment Status', 'Event Date',
                        'Recurring', 'Recurrence Frequency',
                    ]);

                    $query->chunk(500, function ($items) use ($handle) {
                        foreach ($items as $event) {
                            fputcsv($handle, [
                                $event->id,
                                $event->user?->name,
                                $event->user?->email,
                                $event->description,
                                $event->category,
                                $event->amount,
                                $event->payment_status,
                                $event->event_date?->format('Y-m-d'),
                                $event->is_recurring ? 'Yes' : 'No',
                                $event->recurrence_frequency,
                            ]);
                        }
                    });
                } finally {
                    fclose($handle);
                }
            }, 'financial-flow-' . $month->format('Y-m') . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        } catch (Throwable $exception) {
            Log::error('Financial Flow export failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            return back()->withInput()->with(
                'error',
                'CSV export failed. Please try again.'
            );
        }
    }

    private function filteredQuery(Carbon $month, array $filters): Builder
    {
        $query = FinancialEvent::query()
            ->with('user:id,name,email')
            ->whereBetween('event_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ]);

        if (filled($filters['category'] ?? null)) {
            $query->where('category', $filters['category']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('payment_status', $filters['status']);
        }

        if (filled($filters['user_id'] ?? null)) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('description', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }
}
