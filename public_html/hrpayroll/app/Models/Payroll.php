<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payroll extends Model
{
    protected $table = 'payroll';

    protected $fillable = [
        'company_id', 'employee_id', 'month', 'year',
        'basic_pay', 'hra', 'allowances', 'bonus', 'overtime_pay', 'gross_salary',
        'pf_deduction', 'esi_deduction', 'tax_deduction', 'other_deductions', 'total_deductions',
        'net_salary', 'working_days', 'present_days', 'absent_days', 'leave_days',
        'overtime_hours', 'status', 'paid_at', 'remarks', 'email_sent_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function payslip(): HasOne { return $this->hasOne(Payslip::class); }

    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    public function scopeForCompany($query, $companyId) { return $query->where('company_id', $companyId); }
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }
}
