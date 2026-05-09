@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Edit Employee</h3>
    <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Profile</a>
</div>

<div class="card premium-table mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">Personal Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" value="{{ $employee->first_name }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" value="{{ $employee->last_name }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ optional($employee->date_of_birth)->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address (Login ID)</label>
                    <input type="email" value="{{ $employee->email }}" class="form-control bg-light" readonly disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ $employee->phone }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ $employee->emergency_contact }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Current / Permanent Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ $employee->address }}</textarea>
                </div>
            </div>

            <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">Job Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $employee->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" value="{{ $employee->designation }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" {{ $employee->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $employee->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ $employee->status == 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>
            </div>

            <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">Compensation (INR/Month)</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Annual CTC</label>
                    <input type="number" step="0.01" name="ctc" class="form-control" value="{{ $employee->ctc }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Basic Salary</label>
                    <input type="number" step="0.01" name="basic_salary" class="form-control" value="{{ $employee->basic_salary }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">HRA</label>
                    <input type="number" step="0.01" name="hra" class="form-control" value="{{ $employee->hra }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Allowances</label>
                    <input type="number" step="0.01" name="allowances" class="form-control" value="{{ $employee->allowances }}" required>
                </div>
            </div>

            <h5 class="mb-3 mt-2 text-primary fw-bold border-bottom pb-2">Bank & KYC Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Aadhaar Number</label>
                    <input type="text" name="aadhar_number" value="{{ $employee->aadhar_number }}" class="form-control" placeholder="12-digit Aadhaar">
                </div>
                <div class="col-md-6">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" value="{{ $employee->pan_number }}" class="form-control text-uppercase" placeholder="10-digit PAN">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ $employee->bank_name }}" class="form-control" placeholder="e.g., HDFC Bank">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account" value="{{ $employee->bank_account }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" value="{{ $employee->ifsc_code }}" class="form-control text-uppercase">
                </div>
            </div>

            <h5 class="mb-3 mt-2 text-primary fw-bold border-bottom pb-2">Document Uploads (Leave blank to keep existing)</h5>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            @if($employee->photo)
                                <img src="{{ asset('storage/'.$employee->photo) }}" class="rounded-circle mb-2" width="50" height="50" style="object-fit: cover;">
                            @else
                                <i class="fas fa-user-circle text-primary mb-2 fa-2x"></i>
                            @endif
                            <h6 class="fw-bold">Profile Photo</h6>
                            <input class="form-control form-control-sm" type="file" name="photo" accept="image/png, image/jpeg">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-id-card {{ $employee->pan_doc ? 'text-success' : 'text-secondary' }} mb-2 fa-2x"></i>
                            <h6 class="fw-bold">PAN Card</h6>
                            @if($employee->pan_doc) <span class="badge bg-success mb-2">Uploaded</span> @endif
                            <input class="form-control form-control-sm" type="file" name="pan_doc">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-fingerprint {{ $employee->aadhar_doc ? 'text-info' : 'text-secondary' }} mb-2 fa-2x"></i>
                            <h6 class="fw-bold">Aadhaar Card</h6>
                            @if($employee->aadhar_doc) <span class="badge bg-success mb-2">Uploaded</span> @endif
                            <input class="form-control form-control-sm" type="file" name="aadhar_doc">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-university {{ $employee->bank_doc ? 'text-warning' : 'text-secondary' }} mb-2 fa-2x"></i>
                            <h6 class="fw-bold">Bank Passbook</h6>
                            @if($employee->bank_doc) <span class="badge bg-success mb-2">Uploaded</span> @endif
                            <input class="form-control form-control-sm" type="file" name="bank_doc">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm"><i class="fas fa-save me-2"></i> Update Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection
