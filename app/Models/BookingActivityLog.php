<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingActivityLog extends Model
{
    protected $table = 'booking_activity_log';

    protected $fillable = [
        'booking_id',
        'user_id',
        'action',
        'comment',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // God-mode admins (CEO) operate untracked — their actions leave no activity row.
        static::creating(fn () => User::actingAsGodMode() ? false : null);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
