@extends('layouts.auth')

@section('content')
<div class="card auth-card w-100 my-5" style="max-width: 800px;">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold" style="color: #4f46e5;"><i class="fas fa-layer-group"></i> Join HR SaaS</h2>
            <p class="text-muted">Register your company and streamline HR operations.</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <h5 class="mb-3 border-bottom pb-2">Company Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                    @error('company_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Company Email</label>
                    <input type="email" name="company_email" class="form-control" value="{{ old('company_email') }}" required>
                    @error('company_email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <h5 class="mb-3 mt-4 border-bottom pb-2">Admin Account</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Your Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Login Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="mb-4 mt-3">
                <label class="form-label fw-medium">Select Plan</label>
                <select name="plan_id" class="form-select form-select-lg" required>
                    <option value="">-- Choose a Subscription Plan --</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} (Up to {{ $plan->employee_limit }} employees) - ₹{{ $plan->price_monthly }}/month</option>
                    @endforeach
                </select>
                @error('plan_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 fw-bold shadow-sm">Continue to Payment <i class="fas fa-arrow-right ms-2"></i></button>
            
            <div class="text-center mt-3">
                <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="text-decoration-none fw-bold" style="color: #4f46e5;">Log in</a></p>
            </div>
        </form>
    </div>
</div>
@endsection
