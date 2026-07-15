<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingComment extends Model
{
    protected $table = 'booking_comments';

    /**
     * Comment header presets. Picking one prepends a coloured heading to the
     * comment so it stands out in the timeline. Available to everyone who can
     * comment.
     */
    const PRESETS = [
        'change_cancellation' => [
            'label' => 'Change and Cancellation Policy',
            'short' => 'Change & Cancellation',
            'color' => '#D97706',
        ],
        'issuance_confirmation' => [
            'label' => 'Issuance Confirmation',
            'short' => 'Issuance Confirmation',
            'color' => '#16A34A',
        ],
        'general' => [
            'label' => 'General Comment',
            'short' => 'General',
            'color' => '#332E9E',
        ],
        'manager_info' => [
            'label' => 'Red Info Comment from Manager',
            'short' => 'Manager Info',
            'color' => '#DC2626',
        ],
    ];

    protected $fillable = [
        'booking_id',
        'user_id',
        'agent_name',
        'avatar_url',
        'action',
        'comment',
        'preset',
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
