@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Payroll Report</h3>
</div>

<div class="card stat-card mb-4">
    <div class="card-body p-4 bg-light border-bottom">
        <form method="GET" action="{{ route('admin.reports.payroll') }}" class="row g-3 align-items-end">
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
            <div class="col-md-3 text-end">
                <button type="submit" name="export" value="pdf" class="btn btn-danger shadow-sm w-100"><i class="fas fa-file-pdf me-2"></i> Export to PDF</button>
            </div>
        </form>
    </div>

    <!-- Summary strip -->
    <div class="row mx-0 border-bottom text-center">
        <div class="col-4 p-3 border-end">
            <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Net</small>
            <h4 class="fw-bold text-success mb-0">₹{{ number_format($summary['total_net'], 2) }}</h4>
        </div>
        <div class="col-4 p-3 border-end">
            <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Deductions</small>
            <h4 class="fw-bold text-danger mb-0">₹{{ number_format($summary['total_deductions'], 2) }}</h4>
        </div>
        <div class="col-4 p-3">
            <small class="text-muted text-uppercase fw-bold d-block mb-1">Total Gross</small>
            <h4 class="fw-bold text-dark mb-0">₹{{ number_format($summary['total_gross'], 2) }}</h4>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 premium-table align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Gross Pay</th>
                        <th>PF</th>
                        <th>ESI</th>
                        <th>Tax</th>
                        <th>Net Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                    <tr>
                        <td class="ps-4 py-3">
                            <h6 class="mb-0 fw-bold">{{ $payroll->employee->full_name }}</h6>
                        </td>
                        <td>₹{{ number_format($payroll->gross_salary, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($payroll->pf_deduction, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($payroll->esi_deduction, 2) }}</td>
                        <td class="text-danger">-₹{{ number_format($payroll->tax_deduction, 2) }}</td>
                        <td class="fw-bold text-success">₹{{ number_format($payroll->net_salary, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-light"></i>
                            <p class="mb-0">No processed payroll records found for this period.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
