<?php

namespace App\Services\FinancialFlow;

use App\Models\FinancialEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialAnalyticsService
{
    public function monthlyEvents(Carbon $month, ?int $userId = null): Collection
    {
        $query = FinancialEvent::query()
            ->with('user:id,name,email')
            ->whereBetween('event_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ]);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('event_date')->orderBy('id')->get();
    }

    public function categoryLabel(?string $category): string
    {
        return filled($category)
            ? ucwords(str_replace(['_', '-'], ' ', trim($category)))
            : 'Uncategorized';
    }

    private function kind(?string $category): string
    {
        $category = strtolower(trim((string) $category));

        if ($category === 'income') {
            return 'income';
        }

        if ($category === 'loan_emi') {
            return 'emi';
        }

        if (str_contains($category, 'expense')) {
            return 'expense';
        }

        return 'other';
    }

    public function categories(Collection $events): array
    {
        return $events->pluck('category')
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->unique()
            ->sort()
            ->values()
            ->mapWithKeys(fn ($value) => [
                $value => $this->categoryLabel($value),
            ])
            ->all();
    }

    public function summary(Collection $events): array
    {
        return [
            'income' => (float) $events
                ->filter(fn ($event) => $this->kind($event->category) === 'income')
                ->sum('amount'),

            'expenses' => (float) $events
                ->filter(fn ($event) => $this->kind($event->category) === 'expense')
                ->sum('amount'),

            'emi' => (float) $events
                ->filter(fn ($event) => $this->kind($event->category) === 'emi')
                ->sum('amount'),

            'total_events' => $events->count(),

            'unique_users' => $events->pluck('user_id')
                ->filter()
                ->unique()
                ->count(),
        ];
    }

    public function categoryBreakdown(Collection $events): array
    {
        return $events
            ->groupBy(fn ($event) => filled($event->category)
                ? trim($event->category)
                : 'uncategorized')
            ->map(fn (Collection $items, string $category) => [
                'key' => $category,
                'label' => $this->categoryLabel(
                    $category === 'uncategorized' ? null : $category
                ),
                'amount' => (float) $items->sum('amount'),
                'count' => $items->count(),
            ])
            ->sortByDesc('amount')
            ->values()
            ->all();
    }

    public function weeklyBreakdown(Collection $events, Carbon $month): array
    {
        $weeks = [];

        for ($start = 1; $start <= $month->daysInMonth; $start += 7) {
            $end = min($start + 6, $month->daysInMonth);

            $items = $events->filter(function ($event) use ($start, $end) {
                if (! $event->event_date) {
                    return false;
                }

                $day = (int) $event->event_date->format('j');

                return $day >= $start && $day <= $end;
            });

            $weeks[] = [
                'label' => $start . '–' . $end,
                'income' => (float) $items
                    ->filter(fn ($event) => $this->kind($event->category) === 'income')
                    ->sum('amount'),
                'expenses' => (float) $items
                    ->filter(fn ($event) => $this->kind($event->category) === 'expense')
                    ->sum('amount'),
                'emi' => (float) $items
                    ->filter(fn ($event) => $this->kind($event->category) === 'emi')
                    ->sum('amount'),
            ];
        }

        return $weeks;
    }

    public function monthlyTrend(Carbon $month, ?int $userId = null): array
    {
        $query = FinancialEvent::query()
            ->whereBetween('event_date', [
                $month->copy()->startOfYear()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ]);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $events = $query->get(['id', 'user_id', 'amount', 'event_date', 'category']);
        $trend = [];

        for ($number = 1; $number <= $month->month; $number++) {
            $items = $events->filter(fn ($event) =>
                $event->event_date &&
                (int) $event->event_date->format('n') === $number
            );

            $trend[] = [
                'label' => Carbon::create($month->year, $number, 1)->format('M'),
                'income' => (float) $items
                    ->filter(fn ($event) => $this->kind($event->category) === 'income')
                    ->sum('amount'),
                'expenses' => (float) $items
                    ->filter(fn ($event) => $this->kind($event->category) === 'expense')
                    ->sum('amount'),
                'emi' => (float) $items
                    ->filter(fn ($event) => $this->kind($event->category) === 'emi')
                    ->sum('amount'),
            ];
        }

        return $trend;
    }
}
