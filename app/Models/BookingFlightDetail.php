<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingFlightDetail extends Model
{
    protected $table = 'booking_flight_details';

    protected $fillable = [
        'booking_id',
        'pnr',
        'folder_number',
        'locator',
        'airline_locator',
        'type_issuer',
        'reservation_status',
        'airline',
        'vendor',
        'gds',
        'ticket_issue_limit',
        'atol',
        'safi',
        'city_code',
        'departure_airport',
        'arrival_airport',
        'departure_date',
        'return_date',
        'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'ticket_issue_limit' => 'datetime',
            'departure_date' => 'date',
            'return_date' => 'date',
            'atol' => 'boolean',
            'safi' => 'boolean',
            'selling_price' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function flightCosts(): HasMany
    {
        return $this->hasMany(BookingFlightCost::class);
    }
}
