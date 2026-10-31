<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\Reports\UserActivityReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class UserAnalyticsController extends Controller
{
    public function __construct(private UserActivityReportService $reports) {}
    public function activities(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'user_status' => ['nullable', 'in:all,active,inactive'],
            'module' => ['nullable', 'in:education,tools,calculators,calendar'],
            'activity_type' => ['nullable', 'string', 'max:60'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => ['nullable', 'in:latest,oldest,name,most_active'],
        ]);

        $data = [
            'filters' => $filters,
            'users' => $this->reports->activityDirectory($filters),
            'stats' => $this->reports->dashboardStats(),
            'activityTypes' => $this->reports->activityTypes(),
            'dashboardChart' => $this->reports->dashboardChartData(30),
            'moduleBreakdown' => $this->reports->dashboardModuleBreakdown(),
        ];

        return view('admin.reports.index', $data);
    }

    public function show(Request $request, User $user): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', 'in:overview,education,tools,calculators,calendar,timeline'],
            'search' => ['nullable', 'string', 'max:150'],
            'activity_type' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'in:income,investment,loan_emi,fixed_bill,living_expense'],
            'status' => ['nullable', 'in:projected,cleared'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $tab = $filters['tab'] ?? 'overview';
        $data = [
            'user' => $user,
            'tab' => $tab,
            'filters' => $filters,
            'summary' => $this->reports->userSummary($user),
            'chart' => $this->reports->chartData($user),
            'breakdown' => $this->reports->moduleBreakdown($user),
        ];

        if ($tab !== 'overview') {
            $data['records'] = $this->reports->userRecords($user, $tab, $filters);
        }

        return view('admin.reports.users.show', $data);
    }

    public function export(Request $request, User $user): StreamedResponse
    {
        $filters = $request->validate([
            'tab' => ['required', 'in:education,tools,calculators,calendar,timeline'],
            'search' => ['nullable', 'string', 'max:150'],
            'activity_type' => ['nullable', 'string', 'max:60'],
            'category' => ['nullable', 'in:income,investment,loan_emi,fixed_bill,living_expense'],
            'status' => ['nullable', 'in:projected,cleared'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $tab = $filters['tab'];

        return response()->streamDownload(function () use ($user, $filters, $tab) {
            $out = fopen('php://output', 'w');
            if ($tab === 'calendar') {
                fputcsv($out, ['Description', 'Category', 'Event Date', 'Amount', 'Payment Status', 'Recurring', 'Reminder Time']);
                foreach ($this->reports->exportRows($filters, $user, $tab) as $r) {
                    fputcsv($out, [$r->description, $r->category, $r->event_date, $r->amount, $r->payment_status, $r->is_recurring ? 'Yes' : 'No', $r->reminder_time]);
                }
            } elseif ($tab === 'calculators') {
                fputcsv($out, ['Calculator ID', 'Event Type', 'Status', 'Inputs', 'Result', 'Date & Time']);
                foreach ($this->reports->exportRows($filters, $user, $tab) as $r) {
                    fputcsv($out, [$r->calculator_id, $r->event_type, $r->status, json_encode($r->inputs), json_encode($r->result), $r->created_at]);
                }
            } else {
                fputcsv($out, ['Module', 'Activity', 'Subject Type', 'Subject ID', 'Metadata', 'Date & Time']);
                foreach ($this->reports->exportRows($filters, $user, $tab) as $r) {
                    fputcsv($out, [$r->module, $r->activity_type, $r->subject_type, $r->subject_id, json_encode($r->metadata), $r->created_at]);
                }
            }
            fclose($out);
        }, 'loanengine-'.$user->id.'-'.$tab.'-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
