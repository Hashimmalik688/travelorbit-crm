<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHotelRoom extends Model
{
    protected $table = 'booking_hotel_rooms';

    protected $fillable = [
        'booking_hotel_id',
        'room_number',
        'room_type',
        'occupants',
        'meal_basis',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(BookingHotel::class, 'booking_hotel_id');
    }
}
