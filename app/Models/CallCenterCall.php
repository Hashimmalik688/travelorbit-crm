<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallCenterCall extends Model
{
    protected $table = 'callcenter_calls';

    protected $fillable = [
        'inquiry_id', 'user_id', 'direction', 'disposition', 'caller_comment', 'agent_comment', 'called_at', 'is_followup',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'is_followup' => 'boolean',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(CallCenterInquiry::class, 'inquiry_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The 11 dispositions. Edit this list (and the matching index in the
     * calls migration) if you want to add/rename options.
     */
    public static function dispositions(): array
    {
        return [
            'in_process' => 'In process',
            'will_call_back' => 'Will call back',
            'wrong_number' => 'Wrong number',
            'plan_postponed' => 'Plan postponed',
            'will_decide' => 'Will decide',
            'need_to_ask_family' => 'Need to ask family',
            'no_answer' => 'No answer',
            'booked_elsewhere' => 'Booked elsewhere',
            'booked' => 'Booked',
            'booked_by_us' => 'Booked by us',
            'already_booked' => 'Already booked',
        ];
    }

    /** Dispositions that count as a won inquiry and require an MIS booking number. */
    public static function bookedDispositions(): array
    {
        return ['booked'];
    }

    public static function isBookedDisposition(?string $disposition): bool
    {
        return in_array($disposition, static::bookedDispositions(), true);
    }

    public static function isCallbackDisposition(?string $disposition): bool
    {
        return $disposition === 'will_call_back';
    }

    /** Maps a disposition to what the parent Inquiry's status should become. */
    public static function statusFor(string $disposition): string
    {
        return match ($disposition) {
            'booked' => 'converted',
            'wrong_number', 'booked_elsewhere', 'no_answer' => 'lost',
            default => 'in_progress',
        };
    }
}
