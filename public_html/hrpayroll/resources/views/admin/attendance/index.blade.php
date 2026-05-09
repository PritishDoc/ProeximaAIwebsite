@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Attendance Management</h3>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.attendance.bulk_mark') }}" method="POST" class="d-flex gap-2">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="submit" name="status" value="present" class="btn btn-success btn-sm"><i class="fas fa-check-double"></i> Mark All Present</button>
            <button type="submit" name="status" value="holiday" class="btn btn-info btn-sm text-white"><i class="fas fa-gift"></i> Mark Holiday</button>
        </form>
    </div>
</div>

<div class="card stat-card mb-4">
    <div class="card-body p-3 bg-light border-bottom">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Select Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-control w-100" onchange="this.form.submit()">
            </div>
            <div class="col-md-8 text-md-end">
                <div class="badge bg-success p-2 fs-6 mx-1">Present: {{ $stats['present'] }}</div>
                <div class="badge bg-danger p-2 fs-6 mx-1">Absent: {{ $stats['absent'] }}</div>
                <div class="badge bg-warning text-dark p-2 fs-6 mx-1">Leave: {{ $stats['leave'] }}</div>
            </div>
        </form>
    </div>

    <div class="table-responsive p-0">
        <table class="table table-hover mb-0 premium-table align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Employee</th>
                    <th>Status</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                    <th>Working Hrs</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                    @php $att = $employee->attendance->first(); @endphp
                    <tr>
                        <td class="ps-4">
                            <h6 class="mb-0 fw-bold">{{ $employee->full_name }}</h6>
                            <small class="text-muted">{{ $employee->employee_id }}</small>
                        </td>
                        <td>
                            @if($att && $att->status == 'present') <span class="badge bg-success">Present</span>
                            @elseif($att && $att->status == 'half_day') <span class="badge bg-warning">Half Day</span>
                            @elseif($att && $att->status == 'absent') <span class="badge bg-danger">Absent</span>
                            @elseif($att && $att->status == 'leave') <span class="badge bg-info">On Leave</span>
                            @elseif($att && $att->status == 'holiday') <span class="badge bg-primary">Holiday</span>
                            @else <span class="badge bg-secondary">Unmarked</span>
                            @endif
                        </td>
                        <td>
                            <input type="time" name="login_time" value="{{ $att->login_time ?? '' }}" class="form-control form-control-sm" form="att-form-{{ $employee->id }}">
                        </td>
                        <td>
                            <input type="time" name="logout_time" value="{{ $att->logout_time ?? '' }}" class="form-control form-control-sm" form="att-form-{{ $employee->id }}">
                        </td>
                        <td>{{ $att->working_hours ?? '0.00' }} hrs</td>
                        <td class="text-end pe-4">
                            <form id="att-form-{{ $employee->id }}" action="{{ route('admin.attendance.mark') }}" method="POST" class="d-inline-flex gap-2 align-items-center">
                                @csrf
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                
                                <select name="status" class="form-select form-select-sm" style="width: auto;">
                                    <option value="present" {{ ($att->status ?? '') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ ($att->status ?? '') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="half_day" {{ ($att->status ?? '') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                    <option value="leave" {{ ($att->status ?? '') == 'leave' ? 'selected' : '' }}>Leave</option>
                                    <option value="holiday" {{ ($att->status ?? '') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="card-footer bg-white border-top p-3">
        {{ $employees->links() }}
    </div>
    @endif
</div>
@endsection
