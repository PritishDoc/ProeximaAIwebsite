<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    private function employeeId(): int { return auth()->user()->employee->id; }
    private function companyId(): int { return auth()->user()->company_id; }

    public function dashboard()
    {
        $employee = auth()->user()->employee;
        $employee->load('department');

        $pendingLeaves = Leave::where('employee_id', $this->employeeId())->pending()->count();
        $recentPayslips = Payroll::where('employee_id', $this->employeeId())->latest('year')->latest('month')->take(3)->get();

        $todayAttendance = Attendance::where('employee_id', $this->employeeId())
            ->whereDate('date', today())
            ->first();

        return view('employee.dashboard', compact('employee', 'pendingLeaves', 'recentPayslips', 'todayAttendance'));
    }

    public function attendance(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $attendances = Attendance::where('employee_id', $this->employeeId())
            ->forMonth($month, $year)
            ->orderBy('date', 'desc')
            ->get();

        return view('employee.attendance', compact('attendances', 'month', 'year'));
    }

    public function checkIn(Request $request)
    {
        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $this->employeeId(), 'company_id' => $this->companyId(), 'date' => today()],
            ['login_time' => now()->format('H:i'), 'status' => 'present']
        );

        if (!$attendance->wasRecentlyCreated && !$attendance->login_time) {
             $attendance->update(['login_time' => now()->format('H:i'), 'status' => 'present']);
        }

        return back()->with('success', 'Checked in successfully!');
    }

    public function checkOut(Request $request)
    {
        $attendance = Attendance::where('employee_id', $this->employeeId())
            ->whereDate('date', today())
            ->firstOrFail();

        $login = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->login_time ?? $attendance->login_time . ':00');
        $logout = now();
        $workingHours = round($logout->diffInMinutes($login) / 60, 2);

        $attendance->update([
            'logout_time'   => $logout->format('H:i'),
            'working_hours' => $workingHours,
            'is_overtime'   => $workingHours > 8,
            'overtime_hours'=> max(0, $workingHours - 8),
        ]);

        return back()->with('success', 'Checked out successfully!');
    }

    public function leaves(Request $request)
    {
        $leaves = Leave::where('employee_id', $this->employeeId())->latest()->get();
        return view('employee.leaves.index', compact('leaves'));
    }

    public function applyLeave(Request $request)
    {
        $request->validate([
            'leave_type' => 'required|in:sick,casual,earned,unpaid,other',
            'from_date'  => 'required|date|after_or_equal:today',
            'to_date'    => 'required|date|after_or_equal:from_date',
            'reason'     => 'required|string',
        ]);

        $from = \Carbon\Carbon::parse($request->from_date);
        $to   = \Carbon\Carbon::parse($request->to_date);
        $days = $from->diffInDays($to) + 1;

        // Note: Can enhance this to exclude weekends/holidays based on company settings

        Leave::create([
            'company_id'  => $this->companyId(),
            'employee_id' => $this->employeeId(),
            'leave_type'  => $request->leave_type,
            'from_date'   => $request->from_date,
            'to_date'     => $request->to_date,
            'total_days'  => $days,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Leave applied successfully.');
    }

    public function payslips()
    {
        $payrolls = Payroll::where('employee_id', $this->employeeId())->latest('year')->latest('month')->get();
        return view('employee.payslips.index', compact('payrolls'));
    }

    public function downloadPayslip(Payroll $payroll, PdfService $pdfService)
    {
        if ($payroll->employee_id !== $this->employeeId()) abort(403);
        return $pdfService->streamPayslip($payroll);
    }
}
