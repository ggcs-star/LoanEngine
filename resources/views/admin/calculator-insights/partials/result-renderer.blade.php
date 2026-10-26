@php
    $depth = $depth ?? 0;

    if (is_string($value)) {
        $decoded = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $value = $decoded;
        }
    }

    if (is_object($value)) {
        $value = (array) $value;
    }

    $isArray = is_array($value);
    $isList = $isArray && array_is_list($value);

    $formatValue = static function ($item): string {
        if ($item === null || $item === '') {
            return '—';
        }

        if (is_bool($item)) {
            return $item ? 'Yes' : 'No';
        }

        if (is_array($item) || is_object($item)) {
            return json_encode(
                $item,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) ?: '—';
        }

        return (string) $item;
    };

    $formatLabel = static fn ($key): string =>
        \Illuminate\Support\Str::headline((string) $key);

    $isSchedule = $isList
        && count($value) > 0
        && collect($value)->contains(
            fn ($row) => is_array($row)
                && (
                    array_key_exists('emi_number', $row)
                    || array_key_exists('principal', $row)
                    || array_key_exists('interest', $row)
                    || array_key_exists('balance', $row)
                )
        );

    $scheduleColumns = $isSchedule
        ? collect($value)
            ->filter(fn ($row) => is_array($row))
            ->flatMap(fn ($row) => array_keys($row))
            ->unique()
            ->values()
            ->all()
        : [];

    $isTable = $isList
        && count($value) > 0
        && collect($value)->every(
            fn ($row) => is_array($row) || is_object($row)
        );

    $tableColumns = $isTable && ! $isSchedule
        ? collect($value)
            ->flatMap(fn ($row) => array_keys((array) $row))
            ->unique()
            ->values()
            ->all()
        : [];
@endphp

@if ($depth > 10)
    <span>Nested data limit reached</span>

@elseif (! $isArray)
    <span>{{ $formatValue($value) }}</span>

@elseif (empty($value))
    <span>—</span>

@elseif ($isSchedule || $isTable)
    <div class="{{ $isSchedule ? 'ci-repayment-scroll' : 'ci-nested-table-scroll' }}">
        <table class="ci-result-table">
            <thead>
                <tr>
                    @foreach ($isSchedule ? $scheduleColumns : $tableColumns as $column)
                        <th scope="col">
                            {{ $formatLabel($column) }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @foreach ($value as $row)
                    @php
                        $row = (array) $row;
                    @endphp

                    <tr>
                        @foreach ($isSchedule ? $scheduleColumns : $tableColumns as $column)
                            <td>
                                @if (array_key_exists($column, $row))
                                    @if (is_array($row[$column]) || is_object($row[$column]))
                                        @include('admin.calculator-insights.partials.result-renderer', [
                                            'value' => $row[$column],
                                            'depth' => $depth + 1,
                                        ])
                                    @else
                                        {{ $formatValue($row[$column]) }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@elseif ($isList)
    <div class="ci-nested-list">
        @foreach ($value as $item)
            <div class="ci-nested-list-item">
                @if (is_array($item) || is_object($item))
                    @include('admin.calculator-insights.partials.result-renderer', [
                        'value' => $item,
                        'depth' => $depth + 1,
                    ])
                @else
                    <span>{{ $formatValue($item) }}</span>
                @endif
            </div>
        @endforeach
    </div>

@else
    <div class="ci-nested-object">
        @foreach ($value as $key => $item)
            <div class="ci-nested-object-row">
                <span class="ci-nested-object-label">
                    {{ $formatLabel($key) }}
                </span>

                <div class="ci-nested-object-value">
                    @if (is_array($item) || is_object($item))
                        @include('admin.calculator-insights.partials.result-renderer', [
                            'value' => $item,
                            'depth' => $depth + 1,
                        ])
                    @else
                        {{ $formatValue($item) }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif