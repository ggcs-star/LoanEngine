<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\CalculatorUsage;
use App\Models\FinancialEvent;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserLessonProgress;
use App\Services\Admin\Reports\UserActivityReportService;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class UserActivityController extends Controller
{
    public function __construct(
        private UserActivityReportService $reports
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);

        return view('admin.reports.users.index', [
            'users' => $this->reports->activityDirectory($filters),
            'stats' => $this->reports->dashboardStats(),
            'filters' => $filters,
            'activityTypes' => $this->reports->activityTypes(),
        ]);
    }

    public function exportAll(Request $request): BinaryFileResponse|RedirectResponse
    {
        $filters = $this->validateFilters($request);

        return $this->downloadWorkbook($filters);
    }

public function exportUser(
    Request $request,
    User $user
): BinaryFileResponse|RedirectResponse {
    $filters = $this->validateFilters($request);

    Log::info('[UserActivityExport] RECEIVED FILTERS', [
        'url' => $request->fullUrl(),
        'query' => $request->query(),
        'filters' => $filters,
    ]);

    return $this->downloadWorkbook($filters, $user);
}
private function shouldCreateSheet(
    array $filters,
    string $module
): bool {
    return blank($filters['module'] ?? null)
        || $filters['module'] === $module;
}
 private function validateFilters(Request $request): array
{
    $filters = $request->validate([
        'search' => ['nullable', 'string', 'max:150'],
        'module' => ['nullable', 'in:education,tools,calculators,calendar'],
        'activity_type' => ['nullable', 'string', 'max:60'],
        'user_status' => ['nullable', 'in:all,active,inactive'],
        'from' => ['nullable', 'date'],
        'to' => ['nullable', 'date', 'after_or_equal:from'],
        'sort' => ['nullable', 'in:latest,oldest,name,most_active'],
        'tab' => ['nullable', 'in:overview,education,tools,calculators,calendar,timeline'],
        'category' => ['nullable', 'string', 'max:100'],
        'status' => ['nullable', 'in:projected,cleared'],
    ]);

    // Convert selected analytics tab into export module.
    if (empty($filters['module'])) {
        $tab = $filters['tab'] ?? null;

        if (in_array($tab, [
            'education',
            'tools',
            'calculators',
            'calendar',
        ], true)) {
            $filters['module'] = $tab;
        }
    }

    return $filters;
}

    /**
     * Apply date filters to a query.
     */
    private function applyDateFilters(
        Builder $query,
        array $filters,
        string $column = 'created_at'
    ): Builder {
        return $query
            ->when(
                filled($filters['from'] ?? null),
                fn ($q) => $q->whereDate($column, '>=', $filters['from'])
            )
            ->when(
                filled($filters['to'] ?? null),
                fn ($q) => $q->whereDate($column, '<=', $filters['to'])
            );
    }

    /**
     * Check whether a module should be included.
     */
    private function moduleSelected(array $filters, string $module): bool
    {
        return blank($filters['module'] ?? null)
            || $filters['module'] === $module;
    }

    /**
     * Convert any value into a safe Excel cell value.
     */
    private function safeValue(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode(
                $value,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) ?: '';
        }

        $value = (string) $value;
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value) ?? '';

        if (mb_strlen($value) > 32000) {
            $value = mb_substr($value, 0, 32000) . '...';
        }

        return $value;
    }

    private function jsonValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '';
    }

    private function appendRow(Worksheet $sheet, array $values): void
    {
        $row = $sheet->getHighestDataRow() + 1;

        foreach (array_values($values) as $index => $value) {
            $value = $this->safeValue($value);
            $coordinate = Coordinate::stringFromColumnIndex($index + 1) . $row;

            if (is_int($value) || is_float($value)) {
                $sheet->setCellValueExplicit(
                    $coordinate,
                    $value,
                    DataType::TYPE_NUMERIC
                );
            } else {
                $sheet->setCellValueExplicit(
                    $coordinate,
                    $value,
                    DataType::TYPE_STRING
                );
            }
        }
    }

    /**
     * Build workbook using the selected filters.
     */
    private function buildWorkbook(
        array $filters = [],
        ?User $selectedUser = null
    ): Spreadsheet {
        $spreadsheet = new Spreadsheet();
        $isFirstSheet = true;

        $createSheet = function (
            string $title,
            array $headers
        ) use ($spreadsheet, &$isFirstSheet): Worksheet {
            if ($isFirstSheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $isFirstSheet = false;
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $sheet->setTitle($title);

            foreach ($headers as $index => $header) {
                $sheet->setCellValueExplicit(
                    Coordinate::stringFromColumnIndex($index + 1) . '1',
                    $header,
                    DataType::TYPE_STRING
                );
            }

            $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '222222'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->getRowDimension(1)->setRowHeight(25);
            $sheet->freezePane('A2');

            return $sheet;
        };

        /*
        |--------------------------------------------------------------------------
        | User scope
        |--------------------------------------------------------------------------
        */

        if ($selectedUser) {
            $users = collect([$selectedUser]);
        } else {
            // Important: this applies search, user_status and sorting
            // using the same logic as the report listing.
            $users = $this->reports->exportDirectory($filters);
        }

        $userIds = $users->pluck('id')->all();
        $userMap = $users->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | 1. USER SUMMARY
        |--------------------------------------------------------------------------
        */

        $summarySheet = $createSheet('User Summary', [
            'User ID',
            'Name',
            'Email',
            'Mobile',
            'Joined Date',
            'Education Activities',
            'Lessons Completed',
            'Tools Activities',
            'Calculator Uses',
            'Calendar Events',
            'Total Activities',
            'Last Active',
        ]);

        foreach ($users as $user) {
            $educationCount = 0;
            $completedCount = 0;
            $toolsCount = 0;
            $calculatorCount = 0;
            $calendarCount = 0;

            if ($this->moduleSelected($filters, 'education')) {
                $educationQuery = UserActivityLog::where('user_id', $user->id)
                    ->where('module', 'education');

                $this->applyDateFilters($educationQuery, $filters);

                if (filled($filters['activity_type'] ?? null)) {
                    $educationQuery->where(
                        'activity_type',
                        $filters['activity_type']
                    );
                }

                $educationCount = $educationQuery->count();

                $lessonQuery = UserLessonProgress::where('user_id', $user->id)
                    ->whereNotNull('completed_at');

                $this->applyDateFilters($lessonQuery, $filters, 'completed_at');

                if (
                    filled($filters['activity_type'] ?? null)
                    && $filters['activity_type'] !== 'lesson_completed'
                ) {
                    $lessonQuery->whereRaw('1 = 0');
                }

                $completedCount = $lessonQuery->count();
            }

            if ($this->moduleSelected($filters, 'tools')) {
                $toolsQuery = UserActivityLog::where('user_id', $user->id)
                    ->where('module', 'tools');

                $this->applyDateFilters($toolsQuery, $filters);

                if (filled($filters['activity_type'] ?? null)) {
                    $toolsQuery->where(
                        'activity_type',
                        $filters['activity_type']
                    );
                }

                $toolsCount = $toolsQuery->count();
            }

            if ($this->moduleSelected($filters, 'calculators')) {
                $calculatorQuery = CalculatorUsage::where('user_id', $user->id);

                $this->applyDateFilters($calculatorQuery, $filters);

                if (filled($filters['activity_type'] ?? null)) {
                    $calculatorQuery->where(
                        'event_type',
                        $filters['activity_type']
                    );
                }

                $calculatorCount = $calculatorQuery->count();
            }

            if ($this->moduleSelected($filters, 'calendar')) {
                $calendarQuery = FinancialEvent::where('user_id', $user->id);

                $this->applyDateFilters($calendarQuery, $filters);

                if (
                    filled($filters['activity_type'] ?? null)
                    && $filters['activity_type'] !== 'event_created'
                ) {
                    $calendarQuery->whereRaw('1 = 0');
                }

                $calendarCount = $calendarQuery->count();
            }

            $lastActivity = collect([
                $educationCount || $toolsCount
                    ? UserActivityLog::where('user_id', $user->id)
                        ->when(
                            filled($filters['from'] ?? null),
                            fn ($q) => $q->whereDate('created_at', '>=', $filters['from'])
                        )
                        ->when(
                            filled($filters['to'] ?? null),
                            fn ($q) => $q->whereDate('created_at', '<=', $filters['to'])
                        )
                        ->when(
                            filled($filters['module'] ?? null),
                            fn ($q) => $q->where('module', $filters['module'])
                        )
                        ->max('created_at')
                    : null,

                $calculatorCount
                    ? CalculatorUsage::where('user_id', $user->id)
                        ->when(
                            filled($filters['from'] ?? null),
                            fn ($q) => $q->whereDate('created_at', '>=', $filters['from'])
                        )
                        ->when(
                            filled($filters['to'] ?? null),
                            fn ($q) => $q->whereDate('created_at', '<=', $filters['to'])
                        )
                        ->max('created_at')
                    : null,

                $calendarCount
                    ? FinancialEvent::where('user_id', $user->id)
                        ->when(
                            filled($filters['from'] ?? null),
                            fn ($q) => $q->whereDate('created_at', '>=', $filters['from'])
                        )
                        ->when(
                            filled($filters['to'] ?? null),
                            fn ($q) => $q->whereDate('created_at', '<=', $filters['to'])
                        )
                        ->max('created_at')
                    : null,
            ])->filter()->sortDesc()->first();

            $totalActivities = $educationCount
                + $completedCount
                + $toolsCount
                + $calculatorCount
                + $calendarCount;

            $this->appendRow($summarySheet, [
                $user->id,
                $user->name,
                $user->email,
                $user->mobile,
                $user->created_at,
                $educationCount,
                $completedCount,
                $toolsCount,
                $calculatorCount,
                $calendarCount,
                $totalActivities,
                $lastActivity,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. EDUCATION
        |--------------------------------------------------------------------------
        */

        $educationSheet = $createSheet('Education', [
            'User ID',
            'User Name',
            'Email',
            'Activity',
            'Subject Type',
            'Subject ID',
            'Metadata',
            'Activity Date',
        ]);

        if ($this->moduleSelected($filters, 'education')) {
            $educationQuery = UserActivityLog::query()
                ->whereIn('user_id', $userIds)
                ->where('module', 'education')
                ->when(
                    filled($filters['activity_type'] ?? null),
                    fn ($q) => $q->where('activity_type', $filters['activity_type'])
                );

            $this->applyDateFilters($educationQuery, $filters);

            $educationQuery->orderBy('created_at')
                ->chunk(500, function ($logs) use ($educationSheet, $userMap) {
                    foreach ($logs as $log) {
                        $user = $userMap->get($log->user_id);

                        $this->appendRow($educationSheet, [
                            $log->user_id,
                            $user?->name,
                            $user?->email,
                            $log->activity_type,
                            $log->subject_type,
                            $log->subject_id,
                            $this->jsonValue($log->metadata),
                            $log->created_at,
                        ]);
                    }
                });

            $lessonQuery = UserLessonProgress::query()
                ->whereIn('user_id', $userIds)
                ->whereNotNull('completed_at')
                ->when(
                    filled($filters['activity_type'] ?? null)
                    && $filters['activity_type'] !== 'lesson_completed',
                    fn ($q) => $q->whereRaw('1 = 0')
                );

            $this->applyDateFilters($lessonQuery, $filters, 'completed_at');

            $lessonQuery->orderBy('completed_at')
                ->chunk(500, function ($records) use ($educationSheet, $userMap) {
                    foreach ($records as $progress) {
                        $user = $userMap->get($progress->user_id);

                        $this->appendRow($educationSheet, [
                            $progress->user_id,
                            $user?->name,
                            $user?->email,
                            'lesson_completed',
                            'lesson',
                            $progress->lesson_id,
                            'Lesson completion record',
                            $progress->completed_at,
                        ]);
                    }
                });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. TOOLS AND JOURNEYS
        |--------------------------------------------------------------------------
        */

        $toolsSheet = $createSheet('Tools', [
            'User ID',
            'User Name',
            'Email',
            'Activity',
            'Subject Type',
            'Subject ID',
            'Metadata',
            'Activity Date',
        ]);

        if ($this->moduleSelected($filters, 'tools')) {
            $toolsQuery = UserActivityLog::query()
                ->whereIn('user_id', $userIds)
                ->where('module', 'tools')
                ->when(
                    filled($filters['activity_type'] ?? null),
                    fn ($q) => $q->where('activity_type', $filters['activity_type'])
                );

            $this->applyDateFilters($toolsQuery, $filters);

            $toolsQuery->orderBy('created_at')
                ->chunk(500, function ($logs) use ($toolsSheet, $userMap) {
                    foreach ($logs as $log) {
                        $user = $userMap->get($log->user_id);

                        $this->appendRow($toolsSheet, [
                            $log->user_id,
                            $user?->name,
                            $user?->email,
                            $log->activity_type,
                            $log->subject_type,
                            $log->subject_id,
                            $this->jsonValue($log->metadata),
                            $log->created_at,
                        ]);
                    }
                });
        }

        /*
        |--------------------------------------------------------------------------
        | 4. CALCULATORS
        |--------------------------------------------------------------------------
        */

        $calculatorSheet = $createSheet('Calculators', [
            'User ID',
            'User Name',
            'Email',
            'Calculator ID',
            'Event Type',
            'Status',
            'Inputs',
            'Result',
            'Session ID',
            'Created At',
        ]);

        if ($this->moduleSelected($filters, 'calculators')) {
            $calculatorQuery = CalculatorUsage::query()
                ->whereIn('user_id', $userIds)
                ->when(
                    filled($filters['activity_type'] ?? null),
                    fn ($q) => $q->where('event_type', $filters['activity_type'])
                );

            $this->applyDateFilters($calculatorQuery, $filters);

            $calculatorQuery->orderBy('created_at')
                ->chunk(500, function ($usages) use ($calculatorSheet, $userMap) {
                    foreach ($usages as $usage) {
                        $user = $userMap->get($usage->user_id);

                        $this->appendRow($calculatorSheet, [
                            $usage->user_id,
                            $user?->name,
                            $user?->email,
                            $usage->calculator_id,
                            $usage->event_type,
                            $usage->status,
                            $this->jsonValue($usage->inputs),
                            $this->jsonValue($usage->result),
                            $usage->session_id,
                            $usage->created_at,
                        ]);
                    }
                });
        }

        /*
        |--------------------------------------------------------------------------
        | 5. CALENDAR
        |--------------------------------------------------------------------------
        */

        $calendarSheet = $createSheet('Calendar', [
            'User ID',
            'User Name',
            'Email',
            'Event ID',
            'Description',
            'Amount',
            'Category',
            'Event Date',
            'Reminder Time',
            'Reminder Sent At',
            'Payment Status',
            'Source Event ID',
            'Notes',
            'Recurring',
            'Recurrence Frequency',
            'Recurrence End Date',
            'Last Processed At',
            'Created At',
        ]);

        if ($this->moduleSelected($filters, 'calendar')) {
            $calendarQuery = FinancialEvent::query()
                ->whereIn('user_id', $userIds)
                ->when(
                    filled($filters['activity_type'] ?? null)
                    && $filters['activity_type'] !== 'event_created',
                    fn ($q) => $q->whereRaw('1 = 0')
                );

            $this->applyDateFilters($calendarQuery, $filters);

            $calendarQuery->orderBy('event_date')
                ->chunk(500, function ($events) use ($calendarSheet, $userMap) {
                    foreach ($events as $event) {
                        $user = $userMap->get($event->user_id);

                        $this->appendRow($calendarSheet, [
                            $event->user_id,
                            $user?->name,
                            $user?->email,
                            $event->id,
                            $event->description,
                            (float) $event->amount,
                            $event->category,
                            $event->event_date instanceof DateTimeInterface
                                ? $event->event_date->format('Y-m-d')
                                : $event->event_date,
                            $event->reminder_time,
                            $event->reminder_sent_at,
                            $event->payment_status,
                            $event->source_event_id,
                            $event->notes,
                            $event->is_recurring ? 'Yes' : 'No',
                            $event->recurrence_frequency,
                            $event->recurrence_end_date instanceof DateTimeInterface
                                ? $event->recurrence_end_date->format('Y-m-d')
                                : $event->recurrence_end_date,
                            $event->last_processed_at,
                            $event->created_at,
                        ]);
                    }
                });
        }

        /*
        |--------------------------------------------------------------------------
        | 6. ACTIVITY TIMELINE
        |--------------------------------------------------------------------------
        */

        $timelineSheet = $createSheet('Activity Timeline', [
            'User ID',
            'User Name',
            'Email',
            'Module',
            'Activity',
            'Subject Type',
            'Subject ID',
            'Metadata',
            'Activity Date',
        ]);

        $timelineQuery = UserActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->when(
                filled($filters['module'] ?? null),
                fn ($q) => $q->where('module', $filters['module'])
            )
            ->when(
                filled($filters['activity_type'] ?? null),
                fn ($q) => $q->where('activity_type', $filters['activity_type'])
            );

        $this->applyDateFilters($timelineQuery, $filters);

        $timelineQuery->orderBy('created_at')
            ->chunk(500, function ($logs) use ($timelineSheet, $userMap) {
                foreach ($logs as $log) {
                    $user = $userMap->get($log->user_id);

                    $this->appendRow($timelineSheet, [
                        $log->user_id,
                        $user?->name,
                        $user?->email,
                        $log->module,
                        $log->activity_type,
                        $log->subject_type,
                        $log->subject_id,
                        $this->jsonValue($log->metadata),
                        $log->created_at,
                    ]);
                }
            });
            /*
|--------------------------------------------------------------------------
| Remove sheets not required for selected module
|--------------------------------------------------------------------------
*/

$selectedModule = $filters['module'] ?? null;

$sheetMap = [
    'education' => 'Education',
    'tools' => 'Tools',
    'calculators' => 'Calculators',
    'calendar' => 'Calendar',
];

$keepSheetName = $sheetMap[$selectedModule] ?? null;

// Timeline tab support
if (($filters['tab'] ?? null) === 'timeline' && !$selectedModule) {
    $keepSheetName = 'Activity Timeline';
}

if ($keepSheetName !== null) {
    foreach ($spreadsheet->getAllSheets() as $sheet) {
        if ($sheet->getTitle() !== $keepSheetName) {
            $spreadsheet->removeSheetByIndex(
                $spreadsheet->getIndex($sheet)
            );
        }
    }
}

$spreadsheet->setActiveSheetIndex(0);

        /*
        |--------------------------------------------------------------------------
        | Formatting
        |--------------------------------------------------------------------------
        */

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $lastColumn = $sheet->getHighestDataColumn();
            $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
            $lastRow = $sheet->getHighestDataRow();

            $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);

            for ($i = 1; $i <= $lastColumnIndex; $i++) {
                $sheet->getColumnDimension(
                    Coordinate::stringFromColumnIndex($i)
                )->setAutoSize(true);
            }

            $sheet->setAutoFilter("A1:{$lastColumn}1");
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Build, save, validate and download workbook.
     */
    private function downloadWorkbook(
        array $filters = [],
        ?User $selectedUser = null
    ): BinaryFileResponse|RedirectResponse {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $tag = '[UserActivityExport]';
        $tmpDir = storage_path('app/tmp');
        $tmpPath = null;

        try {
            Log::info("{$tag} START", [
                'selected_user' => $selectedUser?->id,
                'filters' => $filters,
                'php' => PHP_VERSION,
                'zip_ext' => class_exists(ZipArchive::class),
                'ob_level' => ob_get_level(),
                'headers_sent' => headers_sent($hsFile, $hsLine),
                'headers_sent_at' => $hsFile ? "{$hsFile}:{$hsLine}" : null,
            ]);

            if (!class_exists(ZipArchive::class)) {
                throw new \RuntimeException(
                    'PHP zip extension is not enabled (enable extension=zip in php.ini).'
                );
            }

            while (ob_get_level() > 0) {
                $stray = (string) ob_get_contents();

                if ($stray !== '') {
                    Log::warning("{$tag} STRAY OUTPUT FOUND BEFORE EXPORT", [
                        'length' => strlen($stray),
                        'preview' => substr($stray, 0, 500),
                    ]);
                }

                ob_end_clean();
            }

            if (!File::isDirectory($tmpDir)) {
                File::makeDirectory($tmpDir, 0775, true);
            }

            $filename = $selectedUser
                ? 'LoanEngine-User-' . $selectedUser->id . '-Report-' . now()->format('Ymd-His') . '.xlsx'
                : 'LoanEngine-All-Users-Report-' . now()->format('Ymd-His') . '.xlsx';

            $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . uniqid('export_', true) . '.xlsx';

            $startedAt = microtime(true);

            $spreadsheet = $this->buildWorkbook($filters, $selectedUser);

            Log::info("{$tag} WORKBOOK BUILT", [
                'sheets' => $spreadsheet->getSheetCount(),
                'seconds' => round(microtime(true) - $startedAt, 2),
                'memory_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
            ]);

            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save($tmpPath);

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer);

            $size = is_file($tmpPath) ? filesize($tmpPath) : 0;
            $head = $size > 0
                ? (string) file_get_contents($tmpPath, false, null, 0, 4)
                : '';

            $zip = new ZipArchive();
            $zipOpen = $zip->open($tmpPath);
            $zipEntries = $zipOpen === true ? $zip->numFiles : null;

            if ($zipOpen === true) {
                $zip->close();
            }

            Log::info("{$tag} FILE SAVED", [
                'path' => $tmpPath,
                'size_bytes' => $size,
                'starts_with_PK' => str_starts_with($head, 'PK'),
                'zip_open_result' => $zipOpen === true ? 'OK' : $zipOpen,
                'zip_entries' => $zipEntries,
            ]);

            if ($size === 0 || !str_starts_with($head, 'PK') || $zipOpen !== true) {
                Log::error("{$tag} INVALID XLSX GENERATED", [
                    'size_bytes' => $size,
                    'first_300_bytes' => $size > 0
                        ? substr((string) file_get_contents($tmpPath, false, null, 0, 300), 0, 300)
                        : '',
                ]);

                throw new \RuntimeException(
                    'Generated XLSX file is invalid. Check storage/logs/laravel.log'
                );
            }

            Log::info("{$tag} SENDING DOWNLOAD", [
                'filename' => $filename,
                'size_bytes' => $size,
            ]);

            return response()->download(
                $tmpPath,
                $filename,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
                    'Pragma' => 'public',
                ]
            )->deleteFileAfterSend(true);

        } catch (Throwable $e) {
            Log::error("{$tag} FAILED", [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => collect(explode("\n", $e->getTraceAsString()))
                    ->take(15)
                    ->all(),
            ]);

            if ($tmpPath && is_file($tmpPath)) {
                @unlink($tmpPath);
            }

            if (config('app.debug')) {
                dd([
                    'EXPORT FAILED' => $e->getMessage(),
                    'class' => get_class($e),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'trace' => collect(explode("\n", $e->getTraceAsString()))
                        ->take(15)
                        ->all(),
                ]);
            }

            return back()->with(
                'error',
                'Excel export failed. Please check the logs.'
            );
        }
    }
}
