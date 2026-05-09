<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | HR Payroll SaaS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar bg-dark text-white glass-panel">
            <div class="sidebar-header">
                <h3>{{ $currentCompany->name ?? 'HR SaaS' }}</h3>
                <small class="text-white-50">Admin Panel</small>
            </div>
            <ul class="list-unstyled components">
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Employees</a></li>
                <li><a href="{{ route('admin.attendance.index') }}"><i class="fas fa-clock"></i> Attendance</a></li>
                <li><a href="{{ route('admin.leaves.index') }}"><i class="fas fa-calendar-alt"></i> Leaves</a></li>
                <li><a href="{{ route('admin.holidays.index') }}"><i class="fas fa-umbrella-beach"></i> Holidays</a></li>
                <li><a href="{{ route('admin.payroll.index') }}"><i class="fas fa-money-bill-wave"></i> Payroll</a></li>
                <li>
                    <a href="#reportSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <ul class="collapse list-unstyled" id="reportSubmenu">
                        <li><a href="{{ route('admin.reports.payroll') }}">Payroll Report</a></li>
                        <li><a href="{{ route('admin.reports.attendance') }}">Attendance Report</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content" class="w-100">
            <!-- Topbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white topbar mb-4 shadow text-dark premium-nav">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-primary d-none d-lg-block">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center">
                        <span class="me-3 fw-medium text-dark"><i class="fas fa-user-circle"></i> {{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
                    </div>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Whoops! Something went wrong.</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.getElementById('sidebarCollapse')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
    @stack('scripts')
</body>
</html>
