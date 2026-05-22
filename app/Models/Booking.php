<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Booking extends Model
{
    use SoftDeletes;

    const TITLES = [
        1 => 'Mr.',
        2 => 'Ms.',
        3 => 'Mrs.',
        4 => 'Mstr',
        5 => 'Miss',
        6 => 'Dr.',
    ];

    protected $fillable = [
        'booking_ref',
        'booking_number',
        'customer_id',
        'user_id',
        'booking_type',
        'lead_source',
        'lead_nature',
        'is_returning_or_referral',
        'old_booking_reference',
        'last_payment_date',
        'last_issue_date',
        'referral_name',
        'booker_title',
        'booker_first_name',
        'booker_last_name',
        'booker_mobile',
        'booker_landline',
        'booker_email',
        'booker_whatsapp',
        'booker_address',
        'booker_postcode',
        'booker_country',
        'passenger_count',
        'booking_status',
        'issuance_requested_at',
        'refund_requested_at',
        'refund_reason',
        'notes',
        'activity_log',
    ];

    protected function casts(): array
    {
        return [
            'issuance_requested_at' => 'datetime',
            'refund_requested_at' => 'datetime',
            'is_returning_or_referral' => 'boolean',
            'last_payment_date' => 'date',
            'last_issue_date' => 'date',
            'activity_log' => 'array',
        ];
    }

    protected $appends = ['total_cost_price', 'total_sale_price', 'total_margin'];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_number)) {
                $lastNumber = static::max('booking_number');
                $booking->booking_number = $lastNumber ? $lastNumber + 1 : 1;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(BookingPayment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BookingComment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(BookingActivityLog::class);
    }

    public function paymentHistory(): HasMany
    {
        return $this->hasMany(BookingPaymentHistory::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function flightDetail(): HasOne
    {
        return $this->hasOne(BookingFlightDetail::class);
    }

    public function flightCosts(): HasMany
    {
        return $this->hasMany(BookingFlightCost::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(BookingHotel::class);
    }

    protected function bookerName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(($this->booker_first_name ?? '') . ' ' . ($this->booker_last_name ?? '')),
        );
    }

    protected function totalCostPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->flightCosts()->exists()) {
                    return $this->flightCosts()->get()->sum(fn ($c) => $c->cost * $c->quantity);
                }
                return 0;
            },
        );
    }

    protected function totalSalePrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = 0;
                if ($this->flightDetail) {
                    $total += $this->flightDetail->selling_price;
                }
                $total += $this->hotels()->sum('selling_price');
                return $total;
            },
        );
    }

    protected function totalMargin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_sale_price - $this->total_cost_price,
        );
    }
}
