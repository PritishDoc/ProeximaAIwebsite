<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Services\RazorpayService;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    public function __construct(private RazorpayService $razorpay) {}

    public function showPlans(Company $company)
    {
        if ($company->status !== 'pending' && $company->activeSubscription()) {
            return redirect()->route('login')->with('info', 'Your company is already active.');
        }

        $plans = Plan::active()->get();
        return view('payment.plans', compact('company', 'plans'));
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'company_id'    => 'required|exists:companies,id',
            'plan_id'       => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $amount = $request->billing_cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        try {
            $order = $this->razorpay->createOrder($amount, 'INR', [
                'company_id'    => $request->company_id,
                'plan_id'       => $plan->id,
                'billing_cycle' => $request->billing_cycle,
            ]);

            return response()->json([
                'success'  => true,
                'order_id' => $order['id'],
                'amount'   => $amount * 100,
                'key'      => config('services.razorpay.key_id')
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay Order Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to initiate payment.'], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
            'company_id'          => 'required|exists:companies,id',
            'plan_id'             => 'required|exists:plans,id',
            'billing_cycle'       => 'required|in:monthly,yearly',
        ]);

        $isValid = $this->razorpay->verifyPayment(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$isValid) {
            return response()->json(['success' => false, 'message' => 'Invalid payment signature.'], 400);
        }

        $company = Company::findOrFail($request->company_id);
        $plan    = Plan::findOrFail($request->plan_id);

        $subscription = $this->razorpay->activateSubscription($company, $plan, $request->billing_cycle, $request->all());

        AuditLog::record('subscription_activated', 'Subscription', $subscription->id, [], $subscription->toArray(), "Company {$company->name} activated subscription.");

        return response()->json([
            'success'      => true,
            'redirect_url' => route('login'),
            'message'      => 'Payment successful! You can now log in.'
        ]);
    }
}
