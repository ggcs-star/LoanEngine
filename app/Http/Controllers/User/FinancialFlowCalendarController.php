<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FinancialEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinancialFlowCalendarController extends Controller
{
    private const CATEGORIES = [
        'income' => 'Income',
        'investment' => 'SIP / Investment',
        'loan_emi' => 'Loan EMI',
        'fixed_bill' => 'Fixed Bill',
        'living_expense' => 'Living Expense',
    ];

    public function index(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        abort_if(
            $year < 2000 || $year > 2100 || $month < 1 || $month > 12,
            422
        );

        $events = FinancialEvent::query()
            ->forUser((int) $request->user()->id)
            ->forMonth($year, $month)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        $inflow = (float) $events
            ->where('category', 'income')
            ->sum('amount');

        $outflow = (float) $events->whereIn('category', [
            'investment',
            'loan_emi',
            'fixed_bill',
            'living_expense',
        ])->sum('amount');

        $emi = (float) $events
            ->where('category', 'loan_emi')
            ->sum('amount');

        $dailyEvents = $events->groupBy(
            fn ($event) => $event->event_date->toDateString()
        );

        return view('user.financial-flow.index', [
            'events' => $events,
            'eventsByDate' => $dailyEvents,
            'year' => $year,
            'month' => $month,
            'categories' => self::CATEGORIES,
            'summary' => [
                'inflow' => $inflow,
                'outflow' => $outflow,
                'emi' => $emi,
                'net_flow' => $inflow - $outflow,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $attributes = $this->attributes($data);
        $attributes['reminder_sent_at'] = null;

        $request->user()
            ->financialEvents()
            ->create($attributes);

        return $this->backToMonth(
            $data['event_date'],
            'Financial event scheduled successfully.'
        );
    }

    public function update(
        Request $request,
        FinancialEvent $event
    ): RedirectResponse {
        $this->ensureOwner($request, $event);

        // Sent reminders and generated occurrences cannot be edited.
        if ($this->isEventLocked($event)) {
            return $this->lockedResponse(
                'This event is locked because its reminder has been sent or it is a generated occurrence.'
            );
        }

        $data = $this->validatedData($request);

        $oldDate = $event->event_date->toDateString();
        $oldTime = $event->reminder_time
            ? Carbon::parse($event->reminder_time)->format('H:i')
            : null;

        $newTime = $data['reminder_time'] ?? null;

        $attributes = $this->attributes($data);

        // Reset sent state only when the date or reminder time changes.
        if (
            $oldDate !== $data['event_date'] ||
            $oldTime !== $newTime
        ) {
            $attributes['reminder_sent_at'] = null;
        }

        DB::transaction(function () use ($event, $attributes) {
            $lockedEvent = FinancialEvent::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isEventLocked($lockedEvent)) {
                abort(
                    409,
                    'This event has already been locked.'
                );
            }

            $lockedEvent->update($attributes);
        });

        return $this->backToMonth(
            $data['event_date'],
            'Financial event updated successfully.'
        );
    }

   public function updateStatus(
        Request $request,
        FinancialEvent $event
    ): RedirectResponse {
        $this->ensureOwner($request, $event);

        if ($this->isEventLocked($event)) {
            return $this->lockedResponse(
                'This event is locked and its status cannot be changed.'
            );
        }

        $data = $request->validate([
            'payment_status' => [
                'required',
                Rule::in(['projected', 'cleared']),
            ],
        ]);

        DB::transaction(function () use ($event, $data) {
            $lockedEvent = FinancialEvent::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isEventLocked($lockedEvent)) {
                abort(
                    409,
                    'This event has already been locked.'
                );
            }

            $lockedEvent->update($data);
        });

        return back()->with(
            'success',
            'Payment status updated.'
        );
    }
    public function destroy(
        Request $request,
        FinancialEvent $event
    ): RedirectResponse {
        $this->ensureOwner($request, $event);

        if ($this->isEventLocked($event)) {
            return $this->lockedResponse(
                'This event cannot be deleted because its reminder has been sent or it is a generated occurrence.'
            );
        }

        $date = $event->event_date->toDateString();

        DB::transaction(function () use ($event) {
            $lockedEvent = FinancialEvent::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isEventLocked($lockedEvent)) {
                abort(
                    409,
                    'This event has already been locked.'
                );
            }

            $lockedEvent->delete();
        });

        return $this->backToMonth(
            $date,
            'Financial event deleted successfully.'
        );
    }

    public function eventsJson(Request $request): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'category' => [
                'nullable',
                Rule::in(array_keys(self::CATEGORIES)),
            ],
        ]);

        $events = FinancialEvent::query()
            ->forUser((int) $request->user()->id)
            ->forMonth(
                (int) $data['year'],
                (int) $data['month']
            )
            ->when(
                $data['category'] ?? null,
                fn ($query, $category) =>
                    $query->where('category', $category)
            )
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $events,
            'summary' => $this->summary($events),
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'description' => [
                'required',
                'string',
                'max:180',
            ],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999999999',
            ],
            'category' => [
                'required',
                Rule::in(array_keys(self::CATEGORIES)),
            ],
            'event_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:2000-01-01',
                'before_or_equal:2100-12-31',
            ],
            'reminder_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'payment_status' => [
                'required',
                Rule::in(['projected', 'cleared']),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'is_recurring' => [
                'sometimes',
                'boolean',
            ],
            'recurrence_frequency' => [
                'nullable',
                Rule::in(['weekly', 'monthly', 'yearly']),
                'required_if:is_recurring,1',
            ],
            'recurrence_end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:event_date',
                'required_if:is_recurring,1',
                'before_or_equal:2100-12-31',
            ],
        ]);
    }

    private function attributes(array $data): array
    {
        $recurring = (bool) ($data['is_recurring'] ?? false);

        return [
            'description' => $data['description'],
            'amount' => $data['amount'],
            'category' => $data['category'],
            'event_date' => $data['event_date'],
            'reminder_time' => $data['reminder_time'] ?? null,
            'payment_status' => $data['payment_status'],
            'notes' => $data['notes'] ?? null,
            'is_recurring' => $recurring,
            'recurrence_frequency' => $recurring
                ? $data['recurrence_frequency']
                : null,
            'recurrence_end_date' => $recurring
                ? ($data['recurrence_end_date'] ?? null)
                : null,
        ];
    }
    private function isEventLocked(FinancialEvent $event): bool
    {
        return $event->reminder_sent_at !== null
            || $event->source_event_id !== null;
    }

    private function lockedResponse(string $message): RedirectResponse
    {
        return back()->with('error', $message);
    }

    private function ensureOwner(
        Request $request,
        FinancialEvent $event
    ): void {
        abort_unless(
            (int) $event->user_id === (int) $request->user()->id,
            404
        );
    }

    private function backToMonth(
        string $date,
        string $message
    ): RedirectResponse {
        $parsed = Carbon::parse($date);

        return redirect()->route('user.financial-flow.index', [
            'year' => $parsed->year,
            'month' => $parsed->month,
        ])->with('success', $message);
    }

    private function summary($events): array
    {
        $inflow = (float) $events
            ->where('category', 'income')
            ->sum('amount');

        $outflow = (float) $events->whereIn('category', [
            'investment',
            'loan_emi',
            'fixed_bill',
            'living_expense',
        ])->sum('amount');

        return [
            'inflow' => $inflow,
            'outflow' => $outflow,
            'emi' => (float) $events
                ->where('category', 'loan_emi')
                ->sum('amount'),
            'net_flow' => $inflow - $outflow,
        ];
    }
}