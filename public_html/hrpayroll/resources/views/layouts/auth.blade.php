<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Payroll SaaS</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-light auth-body">
    <div class="container d-flex flex-column justify-content-center align-items-center min-vh-100">
        @if(session('success'))
            <div class="alert alert-success mt-4 w-100 max-w-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-4 w-100 max-w-sm">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info mt-4 w-100 max-w-sm">{{ session('info') }}</div>
        @endif
        
        @yield('content')
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @stack('scripts')
</body>
</html>
