@extends('layouts.admin')

@section('content')
<div class="card stat-card mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <h4 class="fw-bold mb-0">Payroll Management</h4>
    </div>
    
    <div class="card-body p-4 bg-light border-bottom">
        <form action="{{ route('admin.payroll.index') }}" method="GET" class="row g-3 align-items-end">
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
                <button type="submit" class="btn btn-dark shadow-sm w-100">Filter Payroll</button>
            </div>
        </form>
        
        <div class="row mt-3 text-end">
            <div class="col-12">
                <form action="{{ route('admin.payroll.process') }}" method="POST" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="month" value="{{ request('month', now()->month) }}">
                    <input type="hidden" name="year" value="{{ request('year', now()->year) }}">
                    <button type="submit" class="btn btn-primary shadow-sm px-4" onclick="return confirm('Process payroll for all active employees for this month?');">
                        <i class="fas fa-cogs me-1"></i> Process Payroll
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary strip -->
    <div class="row mx-0 border-bottom text-center">
        <div class="col-6 p-3 border-end">
            <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Net Salary</small>
            <h3 class="fw-bold text-success mb-0">₹{{ number_format($totalNet, 2) }}</h3>
        </div>
        <div class="col-6 p-3">
            <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Gross Salary</small>
            <h3 class="fw-bold text-dark mb-0">₹{{ number_format($totalGross, 2) }}</h3>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 premium-table">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Email Status</th>
                        <th class="text-end pe-4">Payslip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                    <tr>
                        <td class="ps-4 py-3">
                            <h6 class="mb-0 fw-bold">{{ $payroll->employee->full_name }}</h6>
                            <small class="text-muted">{{ $payroll->employee->employee_id }}</small>
                        </td>
                        <td>₹{{ number_format($payroll->gross_salary, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($payroll->total_deductions, 2) }}</td>
                        <td class="fw-bold text-success">₹{{ number_format($payroll->net_salary, 2) }}</td>
                        <td>
                            @if($payroll->status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Paid</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($payroll->email_sent_at)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fas fa-check-circle me-1"></i> Delivered</span>
                                <div class="text-muted" style="font-size: 0.70rem;">{{ $payroll->email_sent_at->format('M d, H:i') }}</div>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary"><i class="fas fa-clock me-1"></i> Pending</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.payroll.download_pdf', $payroll) }}" class="btn btn-sm btn-outline-danger me-2" target="_blank"><i class="fas fa-file-pdf"></i></a>
                            
                            @if($payroll->status === 'processed')
                            <form action="{{ route('admin.payroll.mark_paid', $payroll) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success shadow-sm" title="Mark as Paid"><i class="fas fa-check"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-money-check-alt fa-3x mb-3 text-light"></i>
                            <p class="mb-0">No payroll records found for this month.</p>
                            <small>Click "Process Payroll" to generate automatic salary calculations.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
