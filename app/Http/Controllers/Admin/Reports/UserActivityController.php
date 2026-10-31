<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\CalculatorUsage;
use App\Models\FinancialEvent;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserLessonProgress;
use App\Services\Admin\Reports\UserActivityReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserActivityController extends Controller
{
    public function __construct(
        private UserActivityReportService $reports
    ) {}

    /**
     * All users directory.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'module' => [
                'nullable',
                'in:education,tools,calculators,calendar'
            ],
            'activity_type' => ['nullable', 'string', 'max:60'],
            'user_status' => ['nullable', 'in:all,active,inactive'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => [
                'nullable',
                'in:latest,oldest,name,most_active'
            ],
        ]);

        return view('admin.reports.users.index', [
            'users' => $this->reports->activityDirectory($filters),
            'stats' => $this->reports->dashboardStats(),
            'filters' => $filters,
            'activityTypes' => $this->reports->activityTypes(),
        ]);
    }

    /**
     * Export all registered users.
     */
    public function exportAll(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'module' => [
                'nullable',
                'in:education,tools,calculators,calendar'
            ],
            'activity_type' => ['nullable', 'string', 'max:60'],
            'user_status' => ['nullable', 'in:all,active,inactive'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => [
                'nullable',
                'in:latest,oldest,name,most_active'
            ],
        ]);

        return $this->downloadWorkbook($filters);
    }

    /**
     * Export only the selected user's complete report.
     */
    public function exportUser(User $user): StreamedResponse
    {
        return $this->downloadWorkbook([], $user);
    }

    /**
     * Build the Excel workbook.
     */
    private function downloadWorkbook(
        array $filters = [],
        ?User $selectedUser = null
    ): StreamedResponse {

        $spreadsheet = new Spreadsheet();

        /*
        |--------------------------------------------------------------------------
        | Sheet creation helper
        |--------------------------------------------------------------------------
        */

        $createSheet = function (
            string $title,
            array $headers
        ) use ($spreadsheet) {

            $sheet = $spreadsheet->createSheet();

            $sheet->setTitle($title);

            $sheet->fromArray($headers, null, 'A1');

            $sheet->getStyle('1:1')->applyFromArray([
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

        $userQuery = User::query()
            ->select('id', 'name', 'email', 'mobile', 'created_at')
            ->when(
                $selectedUser,
                fn ($q) => $q->whereKey($selectedUser->id)
            )
            ->when(
                filled($filters['search'] ?? null),
                function ($q) use ($filters) {
                    $term = trim($filters['search']);

                    $q->where(function ($w) use ($term) {
                        $w->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%")
                            ->orWhere('mobile', 'like', "%{$term}%");

                        if (ctype_digit($term)) {
                            $w->orWhere('id', (int) $term);
                        }
                    });
                }
            )
            ->orderBy('id');

        $users = $userQuery->get();

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

            $educationCount = UserActivityLog::where('user_id', $user->id)
                ->where('module', 'education')
                ->count();

            $completedCount = UserLessonProgress::where('user_id', $user->id)
                ->whereNotNull('completed_at')
                ->count();

            $toolsCount = UserActivityLog::where('user_id', $user->id)
                ->where('module', 'tools')
                ->count();

            $calculatorCount = CalculatorUsage::where('user_id', $user->id)
                ->count();

            $calendarCount = FinancialEvent::where('user_id', $user->id)
                ->count();

            $lastActivity = collect([
                UserActivityLog::where('user_id', $user->id)->max('created_at'),
                CalculatorUsage::where('user_id', $user->id)->max('created_at'),
                FinancialEvent::where('user_id', $user->id)->max('created_at'),
                UserLessonProgress::where('user_id', $user->id)->max('completed_at'),
            ])->filter()->sortDesc()->first();

            $summarySheet->fromArray([
                $user->id,
                $user->name,
                $user->email,
                $user->mobile,
                $user->created_at?->format('Y-m-d H:i:s'),
                $educationCount,
                $completedCount,
                $toolsCount,
                $calculatorCount,
                $calendarCount,
                $educationCount + $toolsCount + $calculatorCount + $calendarCount,
                $lastActivity,
            ], null, 'A' . ($summarySheet->getHighestRow() + 1));
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

        UserActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->where('module', 'education')
            ->orderBy('created_at')
            ->chunk(500, function ($logs) use (
                $educationSheet,
                $userMap
            ) {
                foreach ($logs as $log) {
                    $user = $userMap->get($log->user_id);

                    $educationSheet->fromArray([
                        $log->user_id,
                        $user?->name,
                        $user?->email,
                        $log->activity_type,
                        $log->subject_type,
                        $log->subject_id,
                        json_encode($log->metadata),
                        $log->created_at,
                    ], null, 'A' . ($educationSheet->getHighestRow() + 1));
                }
            });

        UserLessonProgress::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->chunk(500, function ($progressRecords) use (
                $educationSheet,
                $userMap
            ) {
                foreach ($progressRecords as $progress) {
                    $user = $userMap->get($progress->user_id);

                    $educationSheet->fromArray([
                        $progress->user_id,
                        $user?->name,
                        $user?->email,
                        'lesson_completed',
                        'lesson',
                        $progress->lesson_id,
                        'Lesson completion record',
                        $progress->completed_at?->format('Y-m-d H:i:s'),
                    ], null, 'A' . ($educationSheet->getHighestRow() + 1));
                }
            });

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

        UserActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->where('module', 'tools')
            ->orderBy('created_at')
            ->chunk(500, function ($logs) use (
                $toolsSheet,
                $userMap
            ) {
                foreach ($logs as $log) {
                    $user = $userMap->get($log->user_id);

                    $toolsSheet->fromArray([
                        $log->user_id,
                        $user?->name,
                        $user?->email,
                        $log->activity_type,
                        $log->subject_type,
                        $log->subject_id,
                        json_encode($log->metadata),
                        $log->created_at,
                    ], null, 'A' . ($toolsSheet->getHighestRow() + 1));
                }
            });

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

        CalculatorUsage::query()
            ->whereIn('user_id', $userIds)
            ->orderBy('created_at')
            ->chunk(500, function ($usages) use (
                $calculatorSheet,
                $userMap
            ) {
                foreach ($usages as $usage) {
                    $user = $userMap->get($usage->user_id);

                    $calculatorSheet->fromArray([
                        $usage->user_id,
                        $user?->name,
                        $user?->email,
                        $usage->calculator_id,
                        $usage->event_type,
                        $usage->status,
                        json_encode($usage->inputs),
                        json_encode($usage->result),
                        $usage->session_id,
                        $usage->created_at?->format('Y-m-d H:i:s'),
                    ], null, 'A' . ($calculatorSheet->getHighestRow() + 1));
                }
            });

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

        FinancialEvent::query()
            ->whereIn('user_id', $userIds)
            ->orderBy('event_date')
            ->chunk(500, function ($events) use (
                $calendarSheet,
                $userMap
            ) {
                foreach ($events as $event) {
                    $user = $userMap->get($event->user_id);

                    $calendarSheet->fromArray([
                        $event->user_id,
                        $user?->name,
                        $user?->email,
                        $event->id,
                        $event->description,
                        (float) $event->amount,
                        $event->category,
                        $event->event_date?->format('Y-m-d'),
                        $event->reminder_time,
                        $event->reminder_sent_at?->format('Y-m-d H:i:s'),
                        $event->payment_status,
                        $event->source_event_id,
                        $event->notes,
                        $event->is_recurring ? 'Yes' : 'No',
                        $event->recurrence_frequency,
                        $event->recurrence_end_date?->format('Y-m-d'),
                        $event->last_processed_at?->format('Y-m-d H:i:s'),
                        $event->created_at?->format('Y-m-d H:i:s'),
                    ], null, 'A' . ($calendarSheet->getHighestRow() + 1));
                }
            });

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

        UserActivityLog::query()
            ->whereIn('user_id', $userIds)
            ->orderBy('created_at')
            ->chunk(500, function ($logs) use (
                $timelineSheet,
                $userMap
            ) {
                foreach ($logs as $log) {
                    $user = $userMap->get($log->user_id);

                    $timelineSheet->fromArray([
                        $log->user_id,
                        $user?->name,
                        $user?->email,
                        $log->module,
                        $log->activity_type,
                        $log->subject_type,
                        $log->subject_id,
                        json_encode($log->metadata),
                        $log->created_at?->format('Y-m-d H:i:s'),
                    ], null, 'A' . ($timelineSheet->getHighestRow() + 1));
                }
            });

        /*
        |--------------------------------------------------------------------------
        | Formatting
        |--------------------------------------------------------------------------
        */

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheet->getDefaultRowDimension()->setRowHeight(20);

            $sheet->getStyle(
                'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow()
            )->getAlignment()->setVertical(
                Alignment::VERTICAL_CENTER
            );

            foreach (range('A', $sheet->getHighestColumn()) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $sheet->setAutoFilter(
                'A1:' . $sheet->getHighestColumn() . '1'
            );
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = $selectedUser
            ? 'LoanEngine-User-' . $selectedUser->id . '-Report-' . now()->format('Ymd-His') . '.xlsx'
            : 'LoanEngine-All-Users-Report-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}
