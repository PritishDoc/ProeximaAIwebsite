<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $fillable = [
        'company_id', 'payroll_id', 'employee_id',
        'pdf_path', 'pdf_filename', 'generated_at', 'emailed_at', 'is_emailed',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'emailed_at' => 'datetime',
        'is_emailed' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function payroll(): BelongsTo { return $this->belongsTo(Payroll::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    public function getPdfUrlAttribute(): string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : '#';
    }
}
