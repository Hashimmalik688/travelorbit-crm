<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class BookingPassenger extends Model
{
    protected $table = 'booking_passengers';

    protected $fillable = [
        'booking_id',
        'passenger_type',
        'full_name',
        'first_name',
        'last_name',
        'title',
        'passport_number',
        'national_id_number',
        'nationality',
        'ticket_number',
        'frequent_flyer_number',
        'e_ticket_number',
        'airline_reference',
        'contact_number',
        'cost_per_pax',
        'sold_per_pax',
        'passenger_status_label',
        'ptc',
        'date_of_birth',
        'passport_country_code',
        'passport_issuing_country',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->first_name || $this->last_name) {
                    return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
                }
                return $this->getRawOriginal('full_name') ?? '';
            },
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => ['adult' => 'Adult', 'youth' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'][$this->passenger_type] ?? ucfirst($this->passenger_type),
        );
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
