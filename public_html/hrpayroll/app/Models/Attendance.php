<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'company_id', 'employee_id', 'date', 'login_time', 'logout_time',
        'working_hours', 'status', 'remarks', 'is_overtime', 'overtime_hours',
    ];

    protected $casts = [
        'date' => 'date',
        'is_overtime' => 'boolean',
        'working_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }
}
