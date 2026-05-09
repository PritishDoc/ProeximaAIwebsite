@extends('layouts.auth')

@section('content')
<div class="card auth-card w-100" style="max-width: 450px;">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: #4f46e5;"><i class="fas fa-layer-group"></i> HR SaaS</h2>
            <p class="text-muted">Welcome back! Please login to your account.</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Email address</label>
                <input type="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium">Password</label>
                <input type="password" name="password" class="form-control form-control-lg" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="#" class="text-decoration-none" style="color: #4f46e5;">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 fw-bold shadow-sm">Sign In</button>
            
            <div class="text-center mt-3">
                <p class="mb-0">New company? <a href="{{ route('register') }}" class="text-decoration-none fw-bold" style="color: #4f46e5;">Create an account</a></p>
            </div>
        </form>
    </div>
</div>
@endsection
