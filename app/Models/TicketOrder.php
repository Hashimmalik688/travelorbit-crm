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
        'cost_adult' => 0, 'cost_child' => 0, 'cost_infant' => 0,
        'safi_charges' => 0,
    ];

    protected $fillable = [
        'booking_id',
        'requested_by',
        'issued_to',
        'cost_adult',
        'cost_child',
        'cost_infant',
        'atol',
        'safi',
        'safi_charges',
        'sent_at',
        'sent_to',
        'sent_cc',
        'sent_bcc',
    ];

    protected function casts(): array
    {
        return [
            'atol' => 'boolean',
            'safi' => 'boolean',
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

    public function getTotalCostAttribute(): float
    {
        return (float) $this->cost_adult + (float) $this->cost_child + (float) $this->cost_infant + (float) $this->safi_charges;
    }

    /** "ATOL Charges" / "SAFI Charges" / "ATOL & SAFI Charges" — whichever actually applies; null when neither does. */
    public function getAtolSafiLabelAttribute(): ?string
    {
        if ($this->atol && $this->safi) return 'ATOL & SAFI Charges';
        if ($this->atol) return 'ATOL Charges';
        if ($this->safi) return 'SAFI Charges';
        return null;
    }
}
