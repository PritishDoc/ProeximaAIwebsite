<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\AuditLog;
use App\Models\Attendance;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    private function companyId(): int { return auth()->user()->company_id; }

    public function index(Request $request)
    {
        $query = Leave::forCompany($this->companyId())->with('employee');

        if ($request->status) $query->where('status', $request->status);
        if ($request->employee_id) $query->where('employee_id', $request->employee_id);

        $leaves    = $query->latest()->paginate(20);
        $employees = Employee::forCompany($this->companyId())->active()->get();

        return view('admin.leaves.index', compact('leaves', 'employees'));
    }

    public function approve(Leave $leave)
    {
        if ($leave->company_id !== $this->companyId()) abort(403);

        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'actioned_at' => now(),
        ]);

        // Mark attendance as leave for each day
        $start = $leave->from_date->copy();
        while ($start->lte($leave->to_date)) {
            if (!$start->isWeekend()) {
                Attendance::updateOrCreate(
                    ['company_id' => $this->companyId(), 'employee_id' => $leave->employee_id, 'date' => $start->toDateString()],
                    ['status' => 'leave']
                );
            }
            $start->addDay();
        }

        AuditLog::record('leave_approved', 'Leave', $leave->id, [], [], "Leave approved for employee #{$leave->employee_id}");

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, Leave $leave)
    {
        if ($leave->company_id !== $this->companyId()) abort(403);

        $request->validate(['rejection_reason' => 'required|string|max:255']);

        $leave->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'actioned_at'      => now(),
        ]);

        AuditLog::record('leave_rejected', 'Leave', $leave->id, [], [], "Leave rejected for employee #{$leave->employee_id}");

        return back()->with('success', 'Leave rejected.');
    }
}
