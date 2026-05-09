@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Employee Profile</h3>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card premium-table text-center p-4">
            <img src="{{ $employee->photo_url }}" class="rounded-circle mx-auto mb-3" width="120" height="120" style="object-fit: cover;">
            <h4 class="fw-bold mb-1">{{ $employee->full_name }}</h4>
            <p class="text-muted mb-3">{{ $employee->designation ?? 'N/A' }} | {{ $employee->department->name ?? 'N/A' }}</p>
            
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-outline-primary mb-2 w-100"><i class="fas fa-edit"></i> Edit Details</a>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card premium-table p-4">
            <h5 class="fw-bold border-bottom pb-3 mb-3">Employment Details</h5>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Employee ID</div>
                <div class="col-sm-8 fw-medium">{{ $employee->employee_id }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Type</div>
                <div class="col-sm-8 fw-medium">{{ ucfirst(str_replace('_', ' ', $employee->employment_type)) }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Joined</div>
                <div class="col-sm-8 fw-medium">{{ $employee->joining_date->format('M d, Y') }}</div>
            </div>
            
            <h5 class="fw-bold border-bottom pb-3 mb-3 mt-4">Compensation</h5>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Basic Salary</div>
                <div class="col-sm-8 fw-medium">₹{{ number_format($employee->basic_salary, 2) }} / mo</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">HRA</div>
                <div class="col-sm-8 fw-medium">₹{{ number_format($employee->hra, 2) }} / mo</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Allowances</div>
                <div class="col-sm-8 fw-medium">₹{{ number_format($employee->allowances, 2) }} / mo</div>
            </div>
            
            <h5 class="fw-bold border-bottom pb-3 mb-3 mt-4">Contact Info</h5>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Email</div>
                <div class="col-sm-8 fw-medium"><a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a></div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Phone</div>
                <div class="col-sm-8 fw-medium">{{ $employee->phone ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
