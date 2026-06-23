<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingVisa extends Model
{
    protected $fillable = [
        'booking_id',
        'passenger_name',
        'visa_type',
        'visa_reference',
        'visa_number',
        'application_date',
        'issue_date',
        'expiry_date',
        'status',
        'actual_cost',
        'selling_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'issue_date'       => 'date',
            'expiry_date'      => 'date',
            'actual_cost'      => 'decimal:2',
            'selling_price'    => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
