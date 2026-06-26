<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPayment extends Model
{
    protected $table = 'booking_payments';

    protected $fillable = [
        'booking_id',
        'booking_plan',
        'amount_paid',
        'total_amount',
        'balance_remaining',
        'due_date',
        'installment_period',
        'installment_first_amount',
        'debit_card_change',
        'deposit_amount',
        'is_deposit_nonrefundable',
        'payment_mode',
        'payment_mode_2',
        'cc_charges',
        'cc_charge_rate',
        'margin_without_cc',
        'total_margin_amount',
        'invoice_generated',
        'invoice_generated_at',
        'payment_instalments',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_deposit_nonrefundable' => 'boolean',
            'debit_card_change' => 'boolean',
            'invoice_generated' => 'boolean',
            'invoice_generated_at' => 'datetime',
            'amount_paid' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'balance_remaining' => 'decimal:2',
            'installment_first_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'cc_charges' => 'decimal:2',
            'margin_without_cc' => 'decimal:2',
            'total_margin_amount' => 'decimal:2',
            'payment_instalments' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
