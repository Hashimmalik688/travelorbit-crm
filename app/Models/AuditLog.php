<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id', 'user_email', 'action', 'model', 'model_id',
        'ip_address', 'user_agent', 'changes', 'description',
        // legacy booking-specific fields
        'booking_id', 'old_values', 'new_values',
    ];

    protected $casts = [
        'changes'    => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // God-mode admins (CEO) operate untracked — never write an audit entry for them.
        static::creating(fn () => User::actingAsGodMode() ? false : null);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Adapted from Taurus - static helper used everywhere
    public static function logAction(
        string  $action,
        ?User   $user        = null,
        ?string $model       = null,
        ?int    $model_id    = null,
        ?string $description = null,
        ?array  $changes     = null
    ): void {
        try {
            static::create([
                'user_id'     => $user?->id,
                'user_email'  => $user?->email ?? 'system',
                'action'      => $action,
                'model'       => $model,
                'model_id'    => $model_id,
                'ip_address'  => request()->ip(),
                'user_agent'  => substr(request()->userAgent() ?? '', 0, 500),
                'changes'     => $changes,
                'description' => $description,
            ]);
        } catch (\Throwable) {
            // Never let audit logging crash the app
        }
    }
}
