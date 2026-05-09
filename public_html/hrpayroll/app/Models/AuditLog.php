<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'company_id', 'user_id', 'action', 'model', 'model_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function record(string $action, ?string $model = null, ?int $modelId = null, array $old = [], array $new = [], ?string $description = null): void
    {
        $user = auth()->user();
        static::create([
            'company_id'  => $user?->company_id,
            'user_id'     => $user?->id,
            'action'      => $action,
            'model'       => $model,
            'model_id'    => $modelId,
            'old_values'  => $old ?: null,
            'new_values'  => $new ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'description' => $description,
        ]);
    }
}
