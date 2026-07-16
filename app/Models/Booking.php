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

    // ── Workflow states ──────────────────────────────────────────────
    const STATUS_PENDING                  = 'pending';
    const STATUS_PAYMENT_CHARGE_REQUEST   = 'payment_charge_request';
    const STATUS_ISSUANCE_QUEUE           = 'issuance_queue';
    const STATUS_TICKET_IN_PROCESS        = 'ticket_in_process';
    const STATUS_INVOICED                 = 'invoiced';
    const STATUS_CONFIRMED                = 'confirmed';
    const STATUS_CANCELLED                = 'cancelled';
    const STATUS_REFUND_QUEUE             = 'refund_queue';
    const STATUS_ISSUED                   = 'issued';
    const STATUS_ISSUED_PAYMENT_AWAITING  = 'issued_payment_awaiting';
    const STATUS_ISSUED_PAYMENT_PLAN      = 'issued_payment_plan';

    const STATUS_LABELS = [
        'pending'                 => 'Pending',
        'payment_charge_request'  => 'Pending - Payment Charge',
        'issuance_queue'          => 'Issuance Queue',
        'ticket_in_process'       => 'Ticket In Process',
        'invoiced'                => 'Invoiced',
        'confirmed'               => 'Confirmed',
        'cancelled'               => 'Cancelled',
        'refund_queue'            => 'Refund Queue',
        'issued'                  => 'Issued',
        'issued_payment_awaiting' => 'Issued - Payment Awaiting',
        'issued_payment_plan'     => 'Issued - Payment Plan',
        'awaiting_issuance'       => 'Issuance Queue',
    ];

    // Status icons for the banner
    const STATUS_ICONS = [
        'pending'                 => 'ph-clock',
        'payment_charge_request'  => 'ph-credit-card',
        'issuance_queue'          => 'ph-ticket',
        'ticket_in_process'       => 'ph-airplane-takeoff',
        'invoiced'                => 'ph-receipt',
        'confirmed'               => 'ph-check-circle',
        'cancelled'               => 'ph-x-circle',
        'refund_queue'            => 'ph-arrows-counter-clockwise',
        'issued'                  => 'ph-check-fat',
        'issued_payment_awaiting' => 'ph-hourglass',
        'issued_payment_plan'     => 'ph-calendar-check',
        'awaiting_issuance'       => 'ph-ticket',
    ];

    const STATUS_COLORS = [
        'pending'                 => ['bg' => '#332E9E',  'text' => '#fff', 'badge_bg' => 'rgba(51,46,158,.12)',  'badge_color' => '#332E9E'],
        'payment_charge_request'  => ['bg' => '#D83F87',  'text' => '#fff', 'badge_bg' => 'rgba(216,63,135,.12)',   'badge_color' => '#D83F87'],
        'issuance_queue'          => ['bg' => '#B45309',  'text' => '#fff', 'badge_bg' => 'rgba(245,158,11,.12)', 'badge_color' => '#B45309'],
        'ticket_in_process'       => ['bg' => '#0369A1',  'text' => '#fff', 'badge_bg' => 'rgba(14,165,233,.12)', 'badge_color' => '#0369A1'],
        'invoiced'                => ['bg' => '#15803D',  'text' => '#fff', 'badge_bg' => 'rgba(22,163,74,.12)',  'badge_color' => '#15803D'],
        'confirmed'               => ['bg' => '#16A34A',  'text' => '#fff', 'badge_bg' => 'rgba(22,163,74,.10)',  'badge_color' => '#16A34A'],
        'cancelled'               => ['bg' => '#DC2626',  'text' => '#fff', 'badge_bg' => 'rgba(220,38,38,.10)', 'badge_color' => '#DC2626'],
        'refund_queue'            => ['bg' => '#B91C1C',  'text' => '#fff', 'badge_bg' => 'rgba(220,38,38,.08)', 'badge_color' => '#B91C1C'],
        'issued'                  => ['bg' => '#047857',  'text' => '#fff', 'badge_bg' => 'rgba(16,185,129,.12)', 'badge_color' => '#047857'],
        'issued_payment_awaiting' => ['bg' => '#C2410C',  'text' => '#fff', 'badge_bg' => 'rgba(194,65,12,.12)',  'badge_color' => '#C2410C'],
        'issued_payment_plan'     => ['bg' => '#0E7490',  'text' => '#fff', 'badge_bg' => 'rgba(14,116,144,.12)', 'badge_color' => '#0E7490'],
        'awaiting_issuance'       => ['bg' => '#B45309',  'text' => '#fff', 'badge_bg' => 'rgba(245,158,11,.12)', 'badge_color' => '#B45309'],
    ];

    // ── Which roles can edit this booking in which states ──────────
    // Returns true if the given role is LOCKED OUT of editing
    public function isLockedForRole(string $role): bool
    {
        if (in_array($role, ['admin', 'manager'])) return false;

        $status = $this->booking_status;

        if ($role === 'agent') {
            // Fully locked while charge awaiting approval or in issuance queue
            return in_array($status, [
                self::STATUS_PAYMENT_CHARGE_REQUEST,
                self::STATUS_ISSUANCE_QUEUE,
            ]);
        }

        if ($role === 'accounts') {
            return !in_array($status, [
                self::STATUS_TICKET_IN_PROCESS,
                self::STATUS_INVOICED,
                self::STATUS_PENDING,
                self::STATUS_CONFIRMED,
                self::STATUS_ISSUED,
                self::STATUS_ISSUED_PAYMENT_PLAN,
                self::STATUS_ISSUED_PAYMENT_AWAITING,
            ]);
        }

        if ($role === 'issuance') {
            // Issuance manager works on issuance_queue and ticket_in_process
            return !in_array($status, [
                self::STATUS_ISSUANCE_QUEUE,
                self::STATUS_TICKET_IN_PROCESS,
            ]);
        }

        return false;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->booking_status] ?? ucfirst($this->booking_status);
    }

    public function statusBadgeHtml(): string
    {
        $colors = self::STATUS_COLORS[$this->booking_status] ?? ['badge_bg' => 'rgba(148,163,184,0.12)', 'badge_color' => '#64748B'];
        $label  = $this->statusLabel();
        return "<span style=\"background:{$colors['badge_bg']};color:{$colors['badge_color']};padding:2px 10px;border-radius:20px;font-size:0.68rem;font-weight:700;\">{$label}</span>";
    }

    // ── Workflow transitions ──────────────────────────────────────────
    public function canQueueForIssuance(): bool
    {
        return in_array($this->booking_status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function canMarkTicketInProcess(): bool
    {
        return $this->booking_status === self::STATUS_ISSUANCE_QUEUE;
    }

    public function canInvoice(): bool
    {
        return $this->booking_status === self::STATUS_ISSUED;
    }

    public function canIssue(): bool
    {
        return $this->booking_status === self::STATUS_TICKET_IN_PROCESS;
    }

    public function canRestoreToPending(): bool
    {
        return in_array($this->booking_status, [
            self::STATUS_ISSUANCE_QUEUE,
            self::STATUS_TICKET_IN_PROCESS,
        ]);
    }

    const TITLES = [
        1 => 'Mr.',
        2 => 'Ms.',
        3 => 'Mrs.',
        4 => 'Mstr',
        5 => 'Miss',
        6 => 'Dr.',
    ];

    protected $fillable = [
        'booking_number',
        'customer_id',
        'user_id',
        'booking_type',
        'lead_source',
        'lead_nature',
        'is_returning_or_referral',
        'old_booking_reference',
        'previous_booking_type',
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
        'issuance_queued_at',
        'ticket_processed_at',
        'invoiced_at',
        'locked_by_role',
        'refund_requested_at',
        'refund_reason',
        'notes',
        'activity_log',
        'excursion_data',
        'payment_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'issuance_requested_at' => 'datetime',
            'refund_requested_at' => 'datetime',
            'is_returning_or_referral' => 'boolean',
            'last_payment_date' => 'date',
            'last_issue_date' => 'date',
            'activity_log'  => 'array',
            'excursion_data' => 'array',
            'payment_reviewed' => 'boolean',
        ];
    }

    protected $appends = ['total_cost_price', 'total_sale_price', 'total_margin'];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_number)) {
                // withTrashed ensures soft-deleted numbers are never reused
                $lastNumber = static::withTrashed()->max('booking_number');
                $booking->booking_number = $lastNumber ? $lastNumber + 1 : 1;
            }
        });

        // God-mode admins (CEO) operate untracked — never persist activity_log
        // changes they make. The booking's own data still saves; only the trail
        // is suppressed. Reverting to the stored value keeps the attribute clean
        // (not dirty) so it is left out of the write entirely.
        static::saving(function (Booking $booking) {
            if (User::actingAsGodMode() && $booking->isDirty('activity_log')) {
                $booking->activity_log = $booking->getOriginal('activity_log');
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

    /**
     * Actual amount received, from the approved-payments ledger — the same
     * source BookingShow uses for "Balance Due". NOT booking_payments.amount_paid,
     * which is a one-time snapshot taken at creation/edit time and never kept
     * in sync with real payments recorded afterwards.
     */
    public function totalReceived(): float
    {
        return (float) $this->paymentHistory->where('status', 'approved')->sum('amount');
    }

    public function marginShares(): HasMany
    {
        return $this->hasMany(BookingMarginShare::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function flightDetail(): HasOne
    {
        return $this->hasOne(BookingFlightDetail::class);
    }

    public function flightDetails(): HasMany
    {
        return $this->hasMany(BookingFlightDetail::class);
    }

    public function flightCosts(): HasMany
    {
        return $this->hasMany(BookingFlightCost::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(BookingHotel::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(BookingTransfer::class);
    }

    public function visas(): HasMany
    {
        return $this->hasMany(BookingVisa::class);
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
                $total = 0;

                // Flight: per-passenger costs from passenger_costs JSON (matches booking show)
                if ($this->flightDetail && $this->flightDetail->passenger_costs) {
                    $total += collect($this->flightDetail->passenger_costs)->sum(fn ($pc) => (float) ($pc['cost'] ?? 0));
                }

                // ATOL/SAFI: £2.50 per non-infant passenger (matches booking show)
                if ($this->flightDetail) {
                    $nonInfant = $this->passengers->filter(fn ($p) => $p->passenger_type !== 'infant')->count();
                    if ($this->flightDetail->atol) $total += 2.50 * $nonInfant;
                    if ($this->flightDetail->safi) $total += 2.50 * $nonInfant;
                }

                // Hotels
                $total += $this->hotels()->sum('actual_cost');

                // Visas
                $total += $this->visas()->sum('actual_cost');

                // Transfers (pickups + dropoffs)
                $total += $this->transfers()->sum('actual_cost');

                // Excursion
                if ($this->excursion_data && $cost = ($this->excursion_data['actual_cost'] ?? null)) {
                    $total += (float) $cost;
                }

                return $total;
            },
        );
    }

    protected function totalSalePrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = 0;

                // Flight: per-passenger sold from passenger_costs JSON (matches booking show)
                if ($this->flightDetail && $this->flightDetail->passenger_costs) {
                    $total += collect($this->flightDetail->passenger_costs)->sum(fn ($pc) => (float) ($pc['sold'] ?? 0));
                }

                // Hotels
                $total += $this->hotels()->sum('selling_price');

                // Visas
                $total += $this->visas()->sum('selling_price');

                // Transfers (pickups + dropoffs)
                $total += $this->transfers()->sum('selling_price');

                // Excursion
                if ($this->excursion_data && $sp = ($this->excursion_data['selling_price'] ?? null)) {
                    $total += (float) $sp;
                }

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

    /**
     * Margin after card charges — what the agency actually keeps. total_margin
     * is the gross figure (sale - cost); the CC fee comes off the top of it, so
     * anything reporting real earnings should use this.
     */
    public function netMargin(): float
    {
        return round($this->total_margin - (float) ($this->payment->cc_charges ?? 0), 2);
    }
}
