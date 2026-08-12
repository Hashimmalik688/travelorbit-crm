<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketOrder extends Model
{
    // Matches the migration's DB-level defaults so an in-memory instance
    // (e.g. before a fresh insert re-reads from the DB) never exposes null
    // for these — number_format() in the email view expects a number.
    protected $attributes = [
        'sold_adult' => 0, 'sold_child' => 0, 'sold_infant' => 0,
        'cost_adult' => 0, 'cost_child' => 0, 'cost_infant' => 0,
        'safi_charges' => 0, 'payment_amount' => 0,
    ];

    protected $fillable = [
        'booking_id',
        'requested_by',
        'ref_number',
        'issued_to',
        'sold_adult',
        'sold_child',
        'sold_infant',
        'cost_adult',
        'cost_child',
        'cost_infant',
        'safi_charges',
        'payment_amount',
        'clearance_date',
        'notes',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'clearance_date' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(TicketOrderPassenger::class)->orderBy('sort_order');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(TicketOrderSegment::class)->orderBy('sort_order');
    }

    public function getTotalSoldAttribute(): float
    {
        return (float) $this->sold_adult + (float) $this->sold_child + (float) $this->sold_infant;
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->cost_adult + (float) $this->cost_child + (float) $this->cost_infant + (float) $this->safi_charges;
    }

    public function getMarginAttribute(): float
    {
        return $this->total_sold - $this->total_cost;
    }
}
