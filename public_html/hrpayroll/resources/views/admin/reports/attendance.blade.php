@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Attendance Report</h3>
</div>

<div class="card stat-card mb-4">
    <div class="card-body p-4 bg-light border-bottom">
        <form method="GET" action="{{ route('admin.reports.attendance') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-medium">Month</label>
                <select name="month" class="form-select border-0 shadow-sm">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month', now()->month) == $i ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$i,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium">Year</label>
                <select name="year" class="form-select border-0 shadow-sm">
                    @for($i = now()->year; $i >= now()->year - 2; $i--)
                        <option value="{{ $i }}" {{ request('year', now()->year) == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark shadow-sm w-100">Filter Report</button>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 premium-table align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Total Present</th>
                        <th>Total Absent</th>
                        <th>Leave Days</th>
                        <th>Total Overtime (Hrs)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td class="ps-4 py-3">
                            <h6 class="mb-0 fw-bold">{{ $employee->full_name }}</h6>
                        </td>
                        <td class="text-success fw-bold">{{ $employee->attendance->where('status', 'present')->count() }}</td>
                        <td class="text-danger fw-bold">{{ $employee->attendance->where('status', 'absent')->count() }}</td>
                        <td class="text-warning fw-bold">{{ $employee->attendance->where('status', 'leave')->count() }}</td>
                        <td>{{ number_format($employee->attendance->sum('overtime_hours'), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <p class="mb-0">No records found for this period.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
