<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Payment\RazorpayController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Employee\PortalController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Payment Routing (Requires Company via URL segment)
Route::get('/company/{company}/plans', [RazorpayController::class, 'showPlans'])->name('payment.plans');
Route::post('/payment/create-order', [RazorpayController::class, 'createOrder'])->name('payment.create_order');
Route::post('/payment/verify', [RazorpayController::class, 'verifyPayment'])->name('payment.verify');

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/companies', [SuperAdminDashboardController::class, 'companies'])->name('companies');
    Route::post('/companies/{company}/toggle', [SuperAdminDashboardController::class, 'toggleCompanyStatus'])->name('companies.toggle');
    Route::resource('plans', PlanController::class);
});

// Admin Routes (Company Owner/Admin)
Route::middleware(['auth', 'role:admin', 'tenant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('employees', EmployeeController::class);
    
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('/attendance/bulk-mark', [AttendanceController::class, 'bulkMark'])->name('attendance.bulk_mark');
    Route::get('/attendance/monthly', [AttendanceController::class, 'monthlyReport'])->name('attendance.monthly');
    
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');
    
    Route::resource('holidays', HolidayController::class)->only(['index', 'store', 'destroy']);
    
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/process', [PayrollController::class, 'process'])->name('payroll.process');
    Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::post('/payroll/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('payroll.mark_paid');
    Route::get('/payroll/{payroll}/pdf', [PayrollController::class, 'downloadPayslip'])->name('payroll.download_pdf');
    
    Route::get('/reports/payroll', [ReportController::class, 'payrollReport'])->name('reports.payroll');
    Route::get('/reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('/reports/employees', [ReportController::class, 'employeeReport'])->name('reports.employees');
});

// Employee Portal Routes
Route::middleware(['auth', 'role:employee', 'tenant'])->prefix('portal')->name('employee.')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [PortalController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/check-in', [PortalController::class, 'checkIn'])->name('attendance.check_in');
    Route::post('/attendance/check-out', [PortalController::class, 'checkOut'])->name('attendance.check_out');
    
    Route::get('/leaves', [PortalController::class, 'leaves'])->name('leaves');
    Route::post('/leaves/apply', [PortalController::class, 'applyLeave'])->name('leaves.apply');
    
    Route::get('/payslips', [PortalController::class, 'payslips'])->name('payslips');
    Route::get('/payslips/{payroll}/pdf', [PortalController::class, 'downloadPayslip'])->name('payslips.download');
});
