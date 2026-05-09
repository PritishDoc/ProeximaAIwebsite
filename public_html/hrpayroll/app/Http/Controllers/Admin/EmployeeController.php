<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->company_id;
    }

    public function index(Request $request)
    {
        $query = Employee::forCompany($this->companyId())->with('department');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('employee_id', 'like', "%{$request->search}%");
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $employees   = $query->latest()->paginate(15);
        $departments = Department::forCompany($this->companyId())->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::forCompany($this->companyId())->where('is_active', true)->get();
        return view('admin.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'      => 'required|string|max:50',
            'last_name'       => 'required|string|max:50',
            'email'           => 'required|email|unique:employees,email|unique:users,email',
            'phone'           => 'nullable|string|max:15',
            'department_id'   => 'nullable|exists:departments,id',
            'designation'     => 'nullable|string|max:100',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'joining_date'    => 'required|date',
            'ctc'             => 'required|numeric|min:0',
            'basic_salary'    => 'required|numeric|min:0',
            'hra'             => 'required|numeric|min:0',
            'allowances'      => 'required|numeric|min:0',
            'password'        => 'required|min:8',
            'photo'           => 'nullable|image|max:2048',
            'aadhar_doc'      => 'nullable|file|max:5120',
            'pan_doc'         => 'nullable|file|max:5120',
            'bank_doc'        => 'nullable|file|max:5120',
            'offer_letter'    => 'nullable|file|max:5120',
        ]);

        // Check plan employee limit
        $company = auth()->user()->company;
        $plan    = $company->plan;
        if ($plan && Employee::forCompany($this->companyId())->active()->count() >= $plan->employee_limit) {
            return back()->withErrors(['limit' => "Your plan allows a maximum of {$plan->employee_limit} employees. Please upgrade your plan."]);
        }

        // Create user account for employee
        $user = User::create([
            'company_id' => $this->companyId(),
            'name'       => $request->first_name . ' ' . $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'employee',
        ]);

        $data = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'department_id', 'designation',
            'employment_type', 'gender', 'date_of_birth', 'joining_date',
            'ctc', 'basic_salary', 'hra', 'allowances', 'pf_contribution',
            'aadhar_number', 'pan_number', 'bank_account', 'bank_name', 'ifsc_code',
            'address', 'emergency_contact',
        ]);

        $data['company_id'] = $this->companyId();
        $data['user_id']    = $user->id;
        $data['status']     = 'active';

        // Generate employee ID
        $count = Employee::forCompany($this->companyId())->withTrashed()->count();
        $data['employee_id'] = 'EMP' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        // File uploads
        foreach (['photo', 'aadhar_doc', 'pan_doc', 'bank_doc', 'offer_letter'] as $file) {
            if ($request->hasFile($file)) {
                $data[$file] = $request->file($file)->store("employees/{$this->companyId()}/{$file}", 'public');
            }
        }

        $employee = Employee::create($data);

        AuditLog::record('created', 'Employee', $employee->id, [], $employee->toArray(), "Employee {$employee->full_name} added");

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee {$employee->full_name} added successfully!");
    }

    public function show(Employee $employee)
    {
        $this->authorizeTenant($employee);
        $employee->load(['department', 'user', 'leaves', 'payrolls']);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $this->authorizeTenant($employee);
        $departments = Department::forCompany($this->companyId())->where('is_active', true)->get();
        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeTenant($employee);

        $request->validate([
            'first_name'   => 'required|string|max:50',
            'last_name'    => 'required|string|max:50',
            'designation'  => 'nullable|string|max:100',
            'ctc'          => 'required|numeric|min:0',
            'basic_salary' => 'required|numeric|min:0',
            'hra'          => 'required|numeric|min:0',
            'allowances'   => 'required|numeric|min:0',
        ]);

        $old = $employee->toArray();
        $data = $request->only([
            'first_name', 'last_name', 'phone', 'department_id', 'designation',
            'employment_type', 'gender', 'date_of_birth', 'status',
            'ctc', 'basic_salary', 'hra', 'allowances', 'pf_contribution',
            'aadhar_number', 'pan_number', 'bank_account', 'bank_name', 'ifsc_code',
            'address', 'emergency_contact',
        ]);

        foreach (['photo', 'aadhar_doc', 'pan_doc', 'bank_doc', 'offer_letter'] as $file) {
            if ($request->hasFile($file)) {
                if ($employee->$file) Storage::disk('public')->delete($employee->$file);
                $data[$file] = $request->file($file)->store("employees/{$this->companyId()}/{$file}", 'public');
            }
        }

        $employee->update($data);

        // Sync user name
        $employee->user?->update(['name' => $data['first_name'] . ' ' . $data['last_name']]);

        AuditLog::record('updated', 'Employee', $employee->id, $old, $employee->fresh()->toArray(), "Employee {$employee->full_name} updated");

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeTenant($employee);
        $name = $employee->full_name;
        $employee->update(['status' => 'terminated', 'leaving_date' => today()]);
        $employee->delete();
        $employee->user?->delete();

        AuditLog::record('deleted', 'Employee', $employee->id, [], [], "Employee {$name} terminated");

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee {$name} terminated.");
    }

    private function authorizeTenant(Employee $employee): void
    {
        if ($employee->company_id !== $this->companyId()) {
            abort(403);
        }
    }
}
