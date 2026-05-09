<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'user_id', 'department_id', 'employee_id',
        'first_name', 'last_name', 'email', 'phone', 'designation',
        'employment_type', 'gender', 'date_of_birth', 'joining_date', 'leaving_date',
        'status', 'ctc', 'basic_salary', 'hra', 'allowances', 'pf_contribution',
        'aadhar_number', 'pan_number', 'bank_account', 'bank_name', 'ifsc_code',
        'address', 'emergency_contact', 'aadhar_doc', 'pan_doc', 'bank_doc', 'offer_letter', 'photo',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'ctc' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'allowances' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name) . '&background=6366f1&color=fff';
    }

    public function getProfileCompletionAttribute(): int
    {
        $fields = [
            'first_name', 'last_name', 'email', 'phone', 'date_of_birth',
            'designation', 'basic_salary', 'address', 'emergency_contact',
            'aadhar_number', 'pan_number', 'bank_name', 'bank_account', 'ifsc_code',
            'photo', 'aadhar_doc', 'pan_doc', 'bank_doc'
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->{$field})) {
                $filled++;
            }
        }

        return (int) round(($filled / count($fields)) * 100);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
