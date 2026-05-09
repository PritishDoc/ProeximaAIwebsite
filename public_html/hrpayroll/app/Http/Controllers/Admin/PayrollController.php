<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Services\PayrollCalculationService;
use App\Services\PdfService;
use App\Models\AuditLog;
use App\Models\Attendance;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollCalculationService $calculator,
        private PdfService $pdfService
    ) {}

    private function companyId(): int { return auth()->user()->company_id; }

    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $payrolls = Payroll::forCompany($this->companyId())
            ->forMonth($month, $year)
            ->with('employee.department')
            ->paginate(20);

        $totalNet = Payroll::forCompany($this->companyId())->forMonth($month, $year)->sum('net_salary');
        $totalGross = Payroll::forCompany($this->companyId())->forMonth($month, $year)->sum('gross_salary');

        return view('admin.payroll.index', compact('payrolls', 'month', 'year', 'totalNet', 'totalGross'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'month'  => 'required|integer|min:1|max:12',
            'year'   => 'required|integer|min:2020',
            'bonus'  => 'nullable|numeric|min:0',
        ]);

        $month   = $request->month;
        $year    = $request->year;
        $companyId = $this->companyId();

        $employees = Employee::forCompany($companyId)->active()->get();
        $processed = 0;

        foreach ($employees as $employee) {
            // Skip if already processed
            if (Payroll::forCompany($companyId)->forMonth($month, $year)->where('employee_id', $employee->id)->exists()) {
                continue;
            }

            // Get attendance for the month
            $workingDays = $this->getWorkingDays($month, $year);
            $presentDays = Attendance::forCompany($companyId)
                ->where('employee_id', $employee->id)
                ->forMonth($month, $year)
                ->whereIn('status', ['present', 'half_day'])
                ->count();

            $overtimeHours = Attendance::forCompany($companyId)
                ->where('employee_id', $employee->id)
                ->forMonth($month, $year)
                ->sum('overtime_hours');

            $calc = $this->calculator->calculate(
                $employee->toArray(),
                $presentDays ?: $workingDays, // if no attendance, assume full month
                $workingDays,
                (float) $overtimeHours,
                (float) $request->get('bonus', 0)
            );

            $payroll = Payroll::create(array_merge($calc, [
                'company_id'  => $companyId,
                'employee_id' => $employee->id,
                'month'       => $month,
                'year'        => $year,
                'leave_days'  => Attendance::forCompany($companyId)->where('employee_id', $employee->id)->forMonth($month, $year)->where('status', 'leave')->count(),
                'status'      => 'processed',
            ]));

            $this->pdfService->generatePayslip($payroll);
            $processed++;
        }

        AuditLog::record('payroll_processed', 'Payroll', null, [], ['month' => $month, 'year' => $year], "Payroll processed for {$processed} employees");

        return redirect()->route('admin.payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "Payroll processed for {$processed} employees.");
    }

    public function show(Payroll $payroll)
    {
        if ($payroll->company_id !== $this->companyId()) abort(403);
        $payroll->load(['employee.department', 'payslip']);
        return view('admin.payroll.show', compact('payroll'));
    }

    public function downloadPayslip(Payroll $payroll)
    {
        if ($payroll->company_id !== $this->companyId()) abort(403);
        return $this->pdfService->streamPayslip($payroll);
    }

    public function markAsPaid(Payroll $payroll)
    {
        if ($payroll->company_id !== $this->companyId()) abort(403);
        $payroll->update(['status' => 'paid', 'paid_at' => now()]);
        
        try {
            \Illuminate\Support\Facades\Mail::to($payroll->employee->email)
                ->send(new \App\Mail\PayslipMail($payroll));
            
            $payroll->update(['email_sent_at' => now()]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send payslip email: " . $e->getMessage());
        }

        AuditLog::record('salary_paid', 'Payroll', $payroll->id, [], [], "Salary marked paid for employee #{$payroll->employee_id}");
        return back()->with('success', 'Salary marked as paid and payslip emailed to employee.');
    }

    private function getWorkingDays(int $month, int $year): int
    {
        $start = \Carbon\Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();
        $days  = 0;
        while ($start->lte($end)) {
            if (!$start->isWeekend()) $days++;
            $start->addDay();
        }
        return $days;
    }
}
