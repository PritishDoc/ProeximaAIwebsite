<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    protected $table = 'holidays';
    
    protected $fillable = ['company_id', 'name', 'date'];
    
    protected $casts = [
        'date' => 'date'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
