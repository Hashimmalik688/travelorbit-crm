<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingTransfer extends Model
{
    protected $table = 'booking_transfers';

    protected $fillable = [
        'booking_id',
        'type',
        'location',
        'date_time',
        'flight_number',
        'route',
        'vehicle_type',
        'supplier',
        'actual_cost',
        'selling_price',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
