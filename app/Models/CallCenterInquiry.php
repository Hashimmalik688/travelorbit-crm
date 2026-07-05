<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CallCenterInquiry extends Model
{
    protected $table = 'callcenter_inquiries';

    protected $fillable = [
        'customer_id', 'user_id', 'type', 'source',
        'adults', 'children', 'infants', 'status', 'details', 'quoted_amount', 'mis_number', 'last_disposition',
    ];

    protected $casts = [
        'details' => 'array',
        'quoted_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CallCenterCustomer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(CallCenterCall::class, 'inquiry_id')->orderByDesc('called_at');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(CallCenterFollowup::class, 'inquiry_id');
    }

    public function detail(string $key, $default = null)
    {
        return data_get($this->details, $key, $default);
    }

    public function totalPax(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * Label lookup for the read-only inquiry drawer. Selects/conditionals for
     * the New Call form itself are rendered explicitly per type in
     * resources/views/livewire/call-center-new-call.blade.php, not generated from this.
     *
     * Each entry: [key, label, html input type]
     */
    public static function detailFieldsFor(string $type): array
    {
        return match ($type) {
            'flight' => [
                ['dep_airport', 'Departure airport', 'text'],
                ['arrival_airport', 'Arrival airport', 'text'],
                ['flight_type', 'Flight type', 'text'],
                ['dep_date', 'Departure date', 'date'],
                ['return_date', 'Return date', 'date'],
                ['cabin_class', 'Cabin class', 'text'],
            ],
            'hotel' => [
                ['destination', 'Destination', 'text'],
                ['check_in', 'Check-in', 'date'],
                ['check_out', 'Check-out', 'date'],
                ['rooms', 'Rooms', 'number'],
                ['star_rating', 'Star rating', 'text'],
            ],
            'holiday' => [
                ['package_name', 'Package name', 'text'],
                ['destination', 'Destination', 'text'],
                ['travel_month', 'Travel month', 'text'],
                ['budget_limit', 'Budget limit', 'text'],
            ],
            'umrah' => [
                ['package_name', 'Package name', 'text'],
                ['travel_date', 'Travel date', 'date'],
                ['package_type', 'Package type (economy/vip)', 'text'],
                ['makkah_nights', 'Makkah nights', 'number'],
                ['madinah_nights', 'Madinah nights', 'number'],
                ['transport', 'Transport', 'text'],
                ['need_flight', 'Need flight', 'text'],
            ],
            'visa' => [
                ['country', 'Country', 'text'],
                ['visa_type', 'Visa type', 'text'],
                ['travel_date', 'Travel date', 'date'],
            ],
            default => [],
        };
    }

    /** Turns raw stored detail values (enums/booleans) into readable labels for display. */
    public static function detailValueLabel(string $key, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($key) {
            'flight_type' => match ($value) {
                'one_way' => 'One way',
                'return' => 'Return',
                default => $value,
            },
            'cabin_class' => match ($value) {
                'economy' => 'Economy',
                'premium_economy' => 'Premium economy',
                'business' => 'Business',
                'first' => 'First class',
                default => $value,
            },
            'transport', 'need_flight' => match ($value) {
                'yes' => 'Yes',
                'no' => 'No',
                default => $value,
            },
            default => (string) $value,
        };
    }

    public static function sources(): array
    {
        return [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'google' => 'Google',
            'referral' => 'Referral',
            'returning' => 'Returning customer',
            'website' => 'Website',
            'email_query' => 'Email query',
            'online_chat_whatsapp' => 'Online chat / WhatsApp',
        ];
    }
}
