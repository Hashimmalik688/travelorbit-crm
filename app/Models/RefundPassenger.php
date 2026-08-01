<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundPassenger extends Model
{
    protected $fillable = [
        'refund_id',
        'passenger_id',
        'e_ticket_number',
        'gds_locator',
        'airline_locator',
        'airline_waiver_code',
    ];

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(BookingPassenger::class, 'passenger_id');
    }
}
