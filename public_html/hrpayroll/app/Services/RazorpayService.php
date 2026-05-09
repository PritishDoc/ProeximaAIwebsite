<?php

namespace App\Services;

use Razorpay\Api\Api;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key_id'),
            config('services.razorpay.key_secret')
        );
    }

    public function createOrder(float $amount, string $currency = 'INR', array $notes = []): array
    {
        $order = $this->api->order->create([
            'amount'   => (int)($amount * 100), // paise
            'currency' => $currency,
            'notes'    => $notes,
        ]);

        return $order->toArray();
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, config('services.razorpay.key_secret'));
        return hash_equals($expectedSignature, $signature);
    }

    public function activateSubscription(Company $company, Plan $plan, string $billingCycle, array $paymentData): Subscription
    {
        $amount = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        $months = $billingCycle === 'yearly' ? 12 : 1;

        $subscription = Subscription::create([
            'company_id'    => $company->id,
            'plan_id'       => $plan->id,
            'billing_cycle' => $billingCycle,
            'amount'        => $amount,
            'status'        => 'active',
            'starts_at'     => now(),
            'expires_at'    => now()->addMonths($months),
        ]);

        Payment::create([
            'company_id'          => $company->id,
            'subscription_id'     => $subscription->id,
            'razorpay_order_id'   => $paymentData['razorpay_order_id'] ?? null,
            'razorpay_payment_id' => $paymentData['razorpay_payment_id'] ?? null,
            'razorpay_signature'  => $paymentData['razorpay_signature'] ?? null,
            'amount'              => $amount,
            'currency'            => 'INR',
            'status'              => 'success',
            'method'              => $paymentData['method'] ?? null,
        ]);

        $company->update(['status' => 'active', 'plan_id' => $plan->id]);

        return $subscription;
    }
}
