<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private function companyId(): int { return auth()->user()->company_id; }

    public function index(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $employees = Employee::forCompany($this->companyId())->active()->with(['attendance' => function ($q) use ($date) {
            $q->whereDate('date', $date);
        }])->paginate(20);

        $stats = [
            'present' => Attendance::forCompany($this->companyId())->whereDate('date', $date)->where('status', 'present')->count(),
            'absent'  => Attendance::forCompany($this->companyId())->whereDate('date', $date)->where('status', 'absent')->count(),
            'leave'   => Attendance::forCompany($this->companyId())->whereDate('date', $date)->where('status', 'leave')->count(),
        ];

        return view('admin.attendance.index', compact('employees', 'date', 'stats', 'month', 'year'));
    }

    public function mark(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,half_day,holiday,leave',
            'login_time'  => 'nullable|date_format:H:i',
            'logout_time' => 'nullable|date_format:H:i',
            'remarks'     => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        if ($employee->company_id !== $this->companyId()) abort(403);

        $workingHours = null;
        if ($request->login_time && $request->logout_time) {
            $login  = Carbon::createFromFormat('H:i', $request->login_time);
            $logout = Carbon::createFromFormat('H:i', $request->logout_time);
            
            // If they entered 22:00 to 06:00 (overnight shift)
            if ($logout->lessThan($login)) {
                $logout->addDay();
            }
            
            // Calculate absolute difference in minutes, then convert to hours
            $workingHours = round(abs($logout->diffInMinutes($login, false)) / 60, 2);
        }

        $formattedDate = \Carbon\Carbon::parse($request->date)->startOfDay();

        Attendance::updateOrCreate(
            ['company_id' => $this->companyId(), 'employee_id' => $request->employee_id, 'date' => $formattedDate],
            [
                'login_time'   => $request->login_time,
                'logout_time'  => $request->logout_time,
                'working_hours'=> $workingHours,
                'status'       => $request->status,
                'remarks'      => $request->remarks,
                'is_overtime'  => $workingHours > 8,
                'overtime_hours'=> max(0, ($workingHours ?? 0) - 8),
            ]
        );

        return back()->with('success', 'Attendance marked successfully.');
    }

    public function bulkMark(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'status'     => 'required|in:present,absent,holiday',
        ]);

        $employees = Employee::forCompany($this->companyId())->active()->get();

        $formattedDate = \Carbon\Carbon::parse($request->date)->startOfDay();

        foreach ($employees as $employee) {
            Attendance::updateOrCreate(
                ['company_id' => $this->companyId(), 'employee_id' => $employee->id, 'date' => $formattedDate],
                ['status' => $request->status]
            );
        }

        return back()->with('success', "Bulk attendance marked as {$request->status}.");
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $employees = Employee::forCompany($this->companyId())->active()->with(['attendance' => function ($q) use ($month, $year) {
            $q->forMonth($month, $year);
        }])->get();

        return view('admin.attendance.monthly', compact('employees', 'month', 'year'));
    }
}
