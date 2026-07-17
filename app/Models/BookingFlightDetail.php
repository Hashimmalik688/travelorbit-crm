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
        'ticket_status',
        'flight_type',
        'airline',
        'flight_number',
        'vendor',
        'gds',
        'cabin',
        'rez_class',
        'fare_basis',
        'nvb',
        'nva',
        'ticket_issue_limit',
        'atol',
        'safi',
        'departure_airport',
        'arrival_airport',
        'departure_date',
        'departure_time',
        'dep_terminal',
        'return_date',
        'arrival_time',
        'arr_terminal',
        'arrival_next_day',
        'duration',
        'seat',
        'baggage_allowance',
        'selling_price',
        'cost',
        'sold',
        'passenger_costs',
    ];

    protected function casts(): array
    {
        return [
            'ticket_issue_limit' => 'datetime',
            'departure_date' => 'date',
            'return_date' => 'date',
            'atol' => 'boolean',
            'safi' => 'boolean',
            'arrival_next_day' => 'boolean',
            'selling_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'sold' => 'decimal:2',
            'passenger_costs' => 'array',
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
