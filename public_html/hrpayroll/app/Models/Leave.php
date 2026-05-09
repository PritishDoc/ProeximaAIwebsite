<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $table = 'leaves';

    protected $fillable = [
        'company_id', 'employee_id', 'approved_by', 'leave_type',
        'from_date', 'to_date', 'total_days', 'reason',
        'status', 'rejection_reason', 'actioned_at',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'actioned_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopeForCompany($query, $companyId) { return $query->where('company_id', $companyId); }
    public function scopePending($query) { return $query->where('status', 'pending'); }
}
