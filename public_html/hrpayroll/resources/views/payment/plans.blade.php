@extends('layouts.auth')

@section('content')
<div class="card auth-card w-100" style="max-width: 600px;">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-credit-card text-primary"></i> Complete Your Subscription</h3>
            <p class="text-muted">You selected: <strong>{{ $company->plan->name }}</strong></p>
        </div>

        <div class="alert alert-info bg-light border-0 mb-4 p-4 row mx-0 rounded-4">
            <div class="col-6 border-end">
                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Monthly</h6>
                <h3 class="fw-bold mb-0 text-dark">₹{{ $company->plan->price_monthly }}</h3>
                <button class="btn btn-primary mt-3 w-100 pay-btn" data-cycle="monthly" data-amount="{{ $company->plan->price_monthly }}">Pay Monthly</button>
            </div>
            <div class="col-6 ps-4">
                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Yearly (Save 20%)</h6>
                <h3 class="fw-bold mb-0 text-success">₹{{ $company->plan->price_yearly }}</h3>
                <button class="btn btn-success mt-3 w-100 pay-btn" data-cycle="yearly" data-amount="{{ $company->plan->price_yearly }}">Pay Yearly</button>
            </div>
        </div>

        <ul class="list-unstyled text-muted mb-0 mx-4">
            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Employee limit: {{ $company->plan->employee_limit }}</li>
            @foreach($company->plan->features ?? [] as $feature)
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ $feature }}</li>
            @endforeach
        </ul>
        
        <form id="verify-form" action="{{ route('payment.verify') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
            <input type="hidden" name="company_id" value="{{ $company->id }}">
            <input type="hidden" name="plan_id" value="{{ $company->plan->id }}">
            <input type="hidden" name="billing_cycle" id="billing_cycle">
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.pay-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const btnEl = this;
            const originalText = btnEl.innerText;
            btnEl.innerText = 'Processing...';
            btnEl.disabled = true;

            const cycle = this.dataset.cycle;
            
            try {
                // 1. Create order on server
                const response = await fetch("{{ route('payment.create_order') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        company_id: '{{ $company->id }}',
                        plan_id: '{{ $company->plan->id }}',
                        billing_cycle: cycle
                    })
                });
                
                const data = await response.json();
                
                if(!data.success) {
                    alert(data.message);
                    btnEl.innerText = originalText;
                    btnEl.disabled = false;
                    return;
                }

                // 2. Init Razorpay
                const options = {
                    key: data.key,
                    amount: data.amount,
                    currency: "INR",
                    name: "HR Payroll SaaS",
                    description: "{{ $company->plan->name }} Subscription",
                    order_id: data.order_id,
                    handler: function (response) {
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                        document.getElementById('razorpay_signature').value = response.razorpay_signature;
                        document.getElementById('billing_cycle').value = cycle;
                        
                        // Submit the verification form via ajax
                        fetch("{{ route('payment.verify') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: new FormData(document.getElementById('verify-form'))
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                window.location.href = data.redirect_url;
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(err => {
                            alert("Payment verification failed.");
                        });
                    },
                    prefill: {
                        name: "{{ $company->name }}",
                        email: "{{ $company->email }}",
                        contact: "{{ $company->phone }}"
                    },
                    theme: { color: "#4f46e5" }
                };
                
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (response){
                    alert("Payment Failed: " + response.error.description);
                });
                rzp.open();
                
                btnEl.innerText = originalText;
                btnEl.disabled = false;
                
            } catch (error) {
                console.error(error);
                alert('Something went wrong. Please try again.');
                btnEl.innerText = originalText;
                btnEl.disabled = false;
            }
        });
    });
</script>
@endpush
