<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketOrderSegment extends Model
{
    protected $fillable = [
        'ticket_order_id',
        'flight_detail_id',
        'locator',
        'folder',
        'type',
        'booked_in',
        'issue_from',
        'airline',
        'sort_order',
    ];

    public function ticketOrder(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class);
    }

    public function flightDetail(): BelongsTo
    {
        return $this->belongsTo(BookingFlightDetail::class, 'flight_detail_id');
    }
}
