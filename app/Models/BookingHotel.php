<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHotel extends Model
{
    protected $table = 'booking_hotels';

    protected $fillable = [
        'booking_id',
        'hotel_name',
        'city',
        'room_type',
        'booking_status',
        'check_in',
        'check_out',
        'occupants',
        'actual_cost',
        'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'actual_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
