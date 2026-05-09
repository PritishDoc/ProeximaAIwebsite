<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Leave;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    private function companyId(): int { return auth()->user()->company_id; }

    public function payrollReport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $payrolls = Payroll::forCompany($this->companyId())
            ->forMonth($month, $year)
            ->with('employee.department')
            ->get();

        $summary = [
            'total_gross'      => $payrolls->sum('gross_salary'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net'        => $payrolls->sum('net_salary'),
            'total_pf'         => $payrolls->sum('pf_deduction'),
            'total_esi'        => $payrolls->sum('esi_deduction'),
            'total_tax'        => $payrolls->sum('tax_deduction'),
        ];

        if ($request->get('export') === 'pdf') {
            return Pdf::loadView('pdf.payroll-report', compact('payrolls', 'summary', 'month', 'year'))
                ->setPaper('a4', 'landscape')
                ->download("payroll-report-{$year}-{$month}.pdf");
        }

        return view('admin.reports.payroll', compact('payrolls', 'summary', 'month', 'year'));
    }

    public function attendanceReport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $employees = Employee::forCompany($this->companyId())->active()
            ->with(['attendance' => fn($q) => $q->forMonth($month, $year)])
            ->get();

        return view('admin.reports.attendance', compact('employees', 'month', 'year'));
    }

    public function employeeReport(Request $request)
    {
        $employees = Employee::forCompany($this->companyId())
            ->with('department')
            ->get();

        if ($request->get('export') === 'pdf') {
            return Pdf::loadView('pdf.employee-report', compact('employees'))
                ->setPaper('a4', 'landscape')
                ->download('employee-report.pdf');
        }

        return view('admin.reports.employees', compact('employees'));
    }
}
