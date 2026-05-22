<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingFlightCost extends Model
{
    protected $table = 'booking_flight_costs';

    protected $fillable = [
        'booking_id',
        'cost_type',
        'cost',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
