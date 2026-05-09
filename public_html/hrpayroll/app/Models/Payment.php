<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'company_id', 'subscription_id', 'razorpay_order_id',
        'razorpay_payment_id', 'razorpay_signature', 'amount', 'currency',
        'status', 'method', 'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'amount' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }

    public function scopeForCompany($query, $companyId) { return $query->where('company_id', $companyId); }
}
