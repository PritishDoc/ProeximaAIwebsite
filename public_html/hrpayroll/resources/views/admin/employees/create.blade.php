@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Add New Employee</h3>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
</div>

<div class="card premium-table mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.employees.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">Personal Information</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address (Login ID)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Default Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Current / Permanent Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">Job Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Employment Type</label>
                    <select name="employment_type" class="form-select" required>
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="intern">Intern</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" required>
                </div>
            </div>

            <h5 class="mb-3 text-primary fw-bold border-bottom pb-2">Compensation (INR/Month)</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Annual CTC</label>
                    <input type="number" step="0.01" name="ctc" class="form-control" value="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Basic Salary</label>
                    <input type="number" step="0.01" name="basic_salary" class="form-control" value="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">HRA</label>
                    <input type="number" step="0.01" name="hra" class="form-control" value="0" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Allowances</label>
                    <input type="number" step="0.01" name="allowances" class="form-control" value="0" required>
                </div>
            </div>

            <h5 class="mb-3 mt-2 text-primary fw-bold border-bottom pb-2">Bank & KYC Details</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Aadhaar Number</label>
                    <input type="text" name="aadhar_number" class="form-control" placeholder="12-digit Aadhaar">
                </div>
                <div class="col-md-6">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control text-uppercase" placeholder="10-digit PAN">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="e.g., HDFC Bank">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control text-uppercase">
                </div>
            </div>

            <h5 class="mb-3 mt-2 text-primary fw-bold border-bottom pb-2">Document Uploads</h5>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-user-circle text-primary mb-2 fa-2x"></i>
                            <h6 class="fw-bold">Profile Photo</h6>
                            <small class="d-block text-muted mb-2">Passport Size (JPG/PNG)</small>
                            <input class="form-control form-control-sm" type="file" name="photo" accept="image/png, image/jpeg">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-id-card text-success mb-2 fa-2x"></i>
                            <h6 class="fw-bold">PAN Card</h6>
                            <small class="d-block text-muted mb-2">Scanned Copy (Image/PDF)</small>
                            <input class="form-control form-control-sm" type="file" name="pan_doc">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-fingerprint text-info mb-2 fa-2x"></i>
                            <h6 class="fw-bold">Aadhaar Card</h6>
                            <small class="d-block text-muted mb-2">Scanned Copy (Image/PDF)</small>
                            <input class="form-control form-control-sm" type="file" name="aadhar_doc">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-university text-warning mb-2 fa-2x"></i>
                            <h6 class="fw-bold">Bank Passbook</h6>
                            <small class="d-block text-muted mb-2">Cancelled Cheque or Passbook</small>
                            <input class="form-control form-control-sm" type="file" name="bank_doc">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm"><i class="fas fa-save me-2"></i> Save Employee</button>
            </div>
        </form>
    </div>
</div>
@endsection
