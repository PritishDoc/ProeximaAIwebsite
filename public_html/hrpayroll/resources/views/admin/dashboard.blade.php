@extends('layouts.admin')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Dashboard Overview</h3>
        <p class="text-muted mb-0">Welcome back! Here's what's happening today.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        @if($subscription)
            <span class="badge bg-success bg-opacity-10 text-success border border-success p-2 rounded-pill shadow-sm">
                <i class="fas fa-crown me-1"></i> {{ $company->plan->name }} Plan (Active)
            </span>
        @else
            <span class="badge bg-danger p-2 rounded-pill"><i class="fas fa-exclamation-triangle"></i> No Active Plan</span>
        @endif
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary rounded-pill shadow-sm ms-2">
            <i class="fas fa-plus me-1"></i> Add Employee
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Total Employees</h6>
                    <h2 class="fw-bold mb-0">{{ $totalEmployees }}</h2>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-auto">
                <div class="progress" style="height: 6px;">
                    @php $limit = $company->plan ? $company->plan->employee_limit : 1; $pct = min(100, ($totalEmployees / $limit) * 100); @endphp
                    <div class="progress-bar custom-bg-primary" role="progressbar" style="width: {{ $pct }}%; background-color:#4f46e5;"></div>
                </div>
                <small class="text-muted d-block mt-2">{{ $totalEmployees }} / {{ $company->plan ? $company->plan->employee_limit : '0' }} employees limit</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Today's Attendance</h6>
                    <h2 class="fw-bold mb-0">{{ $todayAttendance }}</h2>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-fingerprint"></i>
                </div>
            </div>
            <div class="mt-auto">
                <small class="text-success fw-medium"><i class="fas fa-arrow-up"></i> Present today</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Pending Leaves</h6>
                    <h2 class="fw-bold mb-0">{{ $pendingLeaves }}</h2>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-calendar-minus"></i>
                </div>
            </div>
            <div class="mt-auto">
                <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" class="text-decoration-none small fw-medium">View pending requests <i class="fas fa-chevron-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Salary Expense ({{ date('M') }})</h6>
                    <h2 class="fw-bold mb-0">₹{{ number_format($monthlySalaryExpense) }}</h2>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="mt-auto">
                <a href="{{ route('admin.payroll.index') }}" class="text-decoration-none small text-danger fw-medium">View payroll <i class="fas fa-chevron-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card stat-card p-4 h-100">
            <h5 class="fw-bold mb-4">Payroll Overview (Last 6 Months)</h5>
            <canvas id="payrollChart" height="100"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card stat-card p-0 h-100 premium-table">
            <div class="p-4 border-bottom">
                <h5 class="fw-bold mb-0">Recent Employees</h5>
            </div>
            <div class="p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentEmployees as $emp)
                    <li class="list-group-item d-flex align-items-center p-3 border-0 border-bottom">
                        <img src="{{ $emp->photo_url }}" class="rounded-circle me-3" width="40" height="40">
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $emp->full_name }}</h6>
                            <small class="text-muted">{{ $emp->department ? $emp->department->name : 'N/A' }}</small>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center p-4 text-muted">No employees found.</li>
                    @endforelse
                </ul>
            </div>
            <div class="p-3 text-center bg-light">
                <a href="{{ route('admin.employees.index') }}" class="text-decoration-none fw-medium">View All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const ctx = document.getElementById('payrollChart').getContext('2d');
    const chartData = @json($payrollChart);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.month).reverse(),
            datasets: [{
                label: 'Salary Expense (₹)',
                data: chartData.map(d => d.total).reverse(),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
@endpush
