<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaymentHistory extends Model
{
    protected $table = 'booking_payment_history';

    protected $fillable = [
        'booking_id',
        'user_id',
        'payment_date',
        'payment_method',
        'amount',
        'receipt_number',
        'payment_details',
        'status',
        'approved_by',
        'voided_at',
        'voided_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'payment_details' => 'array',
            'voided_at' => 'datetime',
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

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
