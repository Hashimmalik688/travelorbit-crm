<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingComment extends Model
{
    protected $table = 'booking_comments';

    protected $fillable = [
        'booking_id',
        'user_id',
        'agent_name',
        'avatar_url',
        'action',
        'comment',
        'is_mandatory',
    ];

    protected static function booted(): void
    {
        // God-mode admins (CEO) operate untracked — their actions leave no comment.
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
