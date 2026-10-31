<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\Calendar\StoreCalendarEventRequest;
use App\Http\Requests\Api\V1\User\Calendar\UpdateCalendarEventRequest;
use App\Http\Requests\Api\V1\User\Calendar\UpdateCalendarEventStatusRequest;
use App\Http\Resources\Api\V1\User\Calendar\FinancialEventResource;
use App\Models\FinancialEvent;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = (int) ($filters['month'] ?? now()->month);

        $events = FinancialEvent::query()
            ->forUser((int) $request->user()->id)
            ->forMonth($year, $month)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        $eventsByDate = $events
            ->groupBy(fn (FinancialEvent $event) => $event->event_date->toDateString())
            ->map(fn ($dayEvents) => FinancialEventResource::collection($dayEvents)->resolve())
            ->all();

        return $this->successResponse(
            message: 'Calendar fetched successfully.',
            data: [
                'year' => $year,
                'month' => $month,
                'events' => FinancialEventResource::collection($events)->resolve(),
                'events_by_date' => $eventsByDate,
                'summary' => $this->summary($events),
            ]
        );
    }

    public function events(Request $request)
    {
        $filters = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'category' => [
                'nullable',
                Rule::in([
                    'income',
                    'investment',
                    'loan_emi',
                    'fixed_bill',
                    'living_expense',
                ]),
            ],
        ]);

        $events = FinancialEvent::query()
            ->forUser((int) $request->user()->id)
            ->forMonth((int) $filters['year'], (int) $filters['month'])
            ->when(
                $filters['category'] ?? null,
                fn ($query, $category) => $query->where('category', $category)
            )
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        return $this->successResponse(
            message: 'Calendar events fetched successfully.',
            data: FinancialEventResource::collection($events)->resolve(),
            meta: [
                'year' => (int) $filters['year'],
                'month' => (int) $filters['month'],
                'summary' => $this->summary($events),
            ]
        );
    }

    public function show(Request $request, int $event)
    {
        $record = $this->findOwnedEvent($request, $event);

        if (!$record) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(
            message: 'Calendar event fetched successfully.',
            data: (new FinancialEventResource($record))->resolve()
        );
    }

    public function store(StoreCalendarEventRequest $request)
    {
        $data = $request->validated();

        $event = $request->user()
            ->financialEvents()
            ->create($this->attributes($data) + [
                'reminder_sent_at' => null,
            ]);

        return $this->successResponse(
            message: 'Financial event created successfully.',
            data: (new FinancialEventResource($event))->resolve(),
            statusCode: 201
        );
    }

    public function update(
        UpdateCalendarEventRequest $request,
        int $event
    ) {
        $record = $this->findOwnedEvent($request, $event);

        if (!$record) {
            return $this->notFoundResponse();
        }

        if ($this->isEventLocked($record)) {
            return $this->errorResponse(statusCode: 409);
        }

        $data = $request->validated();
        $attributes = $this->attributes($data);

        $oldDate = $record->event_date->toDateString();
        $oldTime = $record->reminder_time
            ? Carbon::parse($record->reminder_time)->format('H:i')
            : null;

        $newTime = $data['reminder_time'] ?? null;

        if (
            $oldDate !== $data['event_date'] ||
            $oldTime !== $newTime
        ) {
            $attributes['reminder_sent_at'] = null;
        }

        $updated = DB::transaction(function () use (
            $request,
            $event,
            $attributes
        ) {
            $lockedEvent = FinancialEvent::query()
                ->forUser((int) $request->user()->id)
                ->whereKey($event)
                ->lockForUpdate()
                ->first();

            if (!$lockedEvent || $this->isEventLocked($lockedEvent)) {
                return false;
            }

            $lockedEvent->update($attributes);

            return true;
        });

        if (!$updated) {
            $currentRecord = $this->findOwnedEvent($request, $event);

            if (!$currentRecord) {
                return $this->notFoundResponse();
            }

            return $this->errorResponse(statusCode: 409);
        }

        $updatedEvent = $this->findOwnedEvent($request, $event);

        return $this->successResponse(
            message: 'Financial event updated successfully.',
            data: (new FinancialEventResource($updatedEvent))->resolve()
        );
    }

    public function updateStatus(
        UpdateCalendarEventStatusRequest $request,
        int $event
    ) {
        $record = $this->findOwnedEvent($request, $event);

        if (!$record) {
            return $this->notFoundResponse();
        }

        if ($this->isEventLocked($record)) {
            return $this->errorResponse(statusCode: 409);
        }

        $data = $request->validated();

        $updated = DB::transaction(function () use (
            $request,
            $event,
            $data
        ) {
            $lockedEvent = FinancialEvent::query()
                ->forUser((int) $request->user()->id)
                ->whereKey($event)
                ->lockForUpdate()
                ->first();

            if (!$lockedEvent || $this->isEventLocked($lockedEvent)) {
                return false;
            }

            $lockedEvent->update($data);

            return true;
        });

        if (!$updated) {
            $currentRecord = $this->findOwnedEvent($request, $event);

            if (!$currentRecord) {
                return $this->notFoundResponse();
            }

            return $this->errorResponse(statusCode: 409);
        }

        $updatedEvent = $this->findOwnedEvent($request, $event);

        return $this->successResponse(
            message: 'Payment status updated successfully.',
            data: (new FinancialEventResource($updatedEvent))->resolve()
        );
    }

    public function destroy(Request $request, int $event)
    {
        $record = $this->findOwnedEvent($request, $event);

        if (!$record) {
            return $this->notFoundResponse();
        }

        if ($this->isEventLocked($record)) {
            return $this->errorResponse(statusCode: 409);
        }

        $deleted = DB::transaction(function () use ($request, $event) {
            $lockedEvent = FinancialEvent::query()
                ->forUser((int) $request->user()->id)
                ->whereKey($event)
                ->lockForUpdate()
                ->first();

            if (!$lockedEvent || $this->isEventLocked($lockedEvent)) {
                return false;
            }

            $lockedEvent->delete();

            return true;
        });

        if (!$deleted) {
            $currentRecord = $this->findOwnedEvent($request, $event);

            if (!$currentRecord) {
                return $this->notFoundResponse();
            }

            return $this->errorResponse(statusCode: 409);
        }

        return $this->successResponse(
            message: 'Financial event deleted successfully.'
        );
    }

    private function findOwnedEvent(
        Request $request,
        int $event
    ): ?FinancialEvent {
        return FinancialEvent::query()
            ->forUser((int) $request->user()->id)
            ->whereKey($event)
            ->first();
    }

    private function isEventLocked(FinancialEvent $event): bool
    {
        return $event->reminder_sent_at !== null
            || $event->source_event_id !== null;
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
