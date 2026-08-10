<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The gap between what a supplier actually refunded Travel Orbit and what
 * was passed on to the customer, kept as extra margin — created automatically
 * when accounts approves a Refund to Customer payout that claims some (see
 * PaymentChargeRequest::advanceLinkedRefund). Mirrors MarginDeduction's shape
 * but is system-generated rather than manually applied, and is tied to the
 * booking the refund came from.
 */
class MarginClaim extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'applied_by_user_id',
        'amount',
        'reason',
        'claim_date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'claim_date' => 'date',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }
}
