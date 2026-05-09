<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        $companyId = $company->id;

        $totalEmployees     = Employee::forCompany($companyId)->active()->count();
        $totalDepartments   = $company->departments()->count();
        $pendingLeaves      = Leave::forCompany($companyId)->pending()->count();

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $monthlySalaryExpense = Payroll::forCompany($companyId)
            ->forMonth($currentMonth, $currentYear)
            ->sum('net_salary');

        $todayAttendance = Attendance::forCompany($companyId)
            ->whereDate('date', today())
            ->where('status', 'present')
            ->count();

        // Last 6 months payroll chart data
        $payrollChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $payrollChart[] = [
                'month' => $date->format('M Y'),
                'total' => Payroll::forCompany($companyId)
                    ->forMonth($date->month, $date->year)
                    ->sum('net_salary'),
            ];
        }

        // Department-wise employee count
        $deptStats = $company->departments()
            ->withCount(['employees' => fn($q) => $q->where('status', 'active')])
            ->get();

        // Recent employees
        $recentEmployees = Employee::forCompany($companyId)
            ->with('department')
            ->latest()
            ->take(5)
            ->get();

        $subscription = $company->activeSubscription();

        return view('admin.dashboard', compact(
            'company', 'totalEmployees', 'totalDepartments', 'pendingLeaves',
            'monthlySalaryExpense', 'todayAttendance', 'payrollChart',
            'deptStats', 'recentEmployees', 'subscription'
        ));
    }
}
