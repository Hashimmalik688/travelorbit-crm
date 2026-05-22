<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingActivityLog;
use App\Models\BookingComment;
use App\Models\BookingDocument;
use App\Models\BookingFlightCost;
use App\Models\BookingFlightDetail;
use App\Models\BookingHotel;
use App\Models\BookingPassenger;
use App\Models\BookingPayment;
use App\Models\BookingPaymentHistory;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateBooking extends Component
{
    use WithFileUploads;

    public int $step = 1;
    const TOTAL_STEPS = 6;

    // Step 1: Booking Info & Lead Source
    public $lead_source = '';
    public $lead_nature = '';
    public $booking_type = '';
    public $is_returning_or_referral = false;
    public $old_booking_reference = '';
    public $last_payment_date = '';
    public $last_issue_date = '';
    public $referral_name = '';

    // Step 2: Caller Information
    public $booker_title = null;
    public $booker_first_name = '';
    public $booker_last_name = '';
    public $booker_mobile = '';
    public $booker_landline = '';
    public $booker_email = '';
    public $booker_address = '';
    public $booker_postcode = '';
    public $booker_country = 'UK';

    // Step 3: Traveller Information
    public $adultCount = 0;
    public $gbeCount = 0;
    public $childCount = 0;
    public $infantCount = 0;
    public $passengers = [];

    // Step 4: Flight Information
    public $flight_pnr = '';
    public $flight_folder_number = '';
    public $flight_locator = '';
    public $flight_airline_locator = '';
    public $flight_type_issuer = '';
    public $flight_reservation_status = '';
    public $flight_airline = '';
    public $flight_vendor = '';
    public $flight_gds = '';
    public $flight_ticket_issue_limit = '';
    public $flight_atol = false;
    public $flight_safi = false;
    public $flight_city_code = '';
    public $flight_departure_airport = '';
    public $flight_arrival_airport = '';
    public $flight_departure_date = '';
    public $flight_return_date = '';
    public $flight_selling_price = '';
    public $flight_costs = [
        'adult'   => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'child'   => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'gbe'     => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'infant'  => ['cost' => '', 'qty' => 0, 'sold' => ''],
    ];

    // Step 5: Hotel Information
    public $hotel_name = '';
    public $hotel_city = '';
    public $hotel_room_type = '';
    public $hotel_status = 'confirmed';
    public $hotel_check_in = '';
    public $hotel_check_out = '';
    public $hotel_occupants = 1;
    public $hotel_actual_cost = '';
    public $hotel_selling_price = '';

    // Step 6: Payment & Wrap-up
    public $payment_type = '';
    public $payment_mode = '';
    public $payment_mode_2 = '';
    public $amount_paid = '';
    public $installment_period = 'none';
    public $installment_first_amount = '';
    public $debit_card_change = false;
    public $due_date = '';
    public $deposit_amount = '';
    public $payment_history = [];

    // Cost & margins
    public $cc_charges = '';
    public $safi_charges = '';

    // Documents
    public $documents = [];
    public $document_types = [];

    // Activity log
    public $mandatory_comment = '';
    public $activity_log_entries = [];

    // Status
    public $booking_status = 'pending';
    public $issuance_requested = false;
    public $refund_queue = false;
    public $booking_ref = '';

    public function mount(): void
    {
        $this->reconcilePassengers();
    }

    /* ── Navigation ── */
    public function goToStep(int $step): void
    {
        $this->step = max(1, min(self::TOTAL_STEPS, $step));
    }

    public function nextStep(): void
    {
        if ($this->step === 3) $this->syncFlightCostQtys();
        if ($this->validateStep($this->step)) {
            $this->step = min(self::TOTAL_STEPS, $this->step + 1);
        }
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    private function syncFlightCostQtys(): void
    {
        $this->flight_costs['adult']['qty']  = $this->adultCount;
        $this->flight_costs['child']['qty']  = $this->childCount;
        $this->flight_costs['gbe']['qty']    = $this->gbeCount;
        $this->flight_costs['infant']['qty'] = $this->infantCount;
    }

    /* ── Validation ── */
    protected function validateStep(int $step): bool
    {
        $rules = match ($step) {
            1 => [
                'lead_source' => 'required|in:to_returning,to_referral,referral_client,returning_client,fb,wa,email,diaspora_group,instagram,tiktok,website,google',
                'lead_nature' => 'required|in:new_booking,date_change,refund_booking,previous_booking',
                'booking_type' => 'required|in:flight,hotel,umrah,holiday',
                'referral_name' => 'nullable|string|max:255',
                'old_booking_reference' => 'nullable|string|max:255',
                'last_payment_date' => 'nullable|date',
                'last_issue_date' => 'nullable|date',
            ],
            2 => [
                'booker_title' => 'nullable|in:1,2,3,4,5,6',
                'booker_first_name' => 'required|string|max:255',
                'booker_last_name' => 'required|string|max:255',
                'booker_mobile' => 'required|string|max:255',
                'booker_landline' => 'nullable|string|max:255',
                'booker_email' => 'nullable|email|max:255',
                'booker_address' => 'nullable|string',
                'booker_postcode' => 'nullable|string|max:20',
                'booker_country' => 'nullable|string|max:100',
            ],
            3 => [
                'passengers' => 'required|array|min:1',
                'passengers.*.first_name' => 'required|string|max:255',
                'passengers.*.last_name' => 'required|string|max:255',
                'passengers.*.title' => 'nullable|in:Mr.,Ms.,Mrs.,Mstr,Miss,Dr.',
                'passengers.*.passport_number' => 'nullable|string|max:255',
                'passengers.*.passport_country_code' => 'nullable|string|max:3',
                'passengers.*.passport_issuing_country' => 'nullable|string|max:255',
                'passengers.*.national_id_number' => 'nullable|string|max:255',
                'passengers.*.nationality' => 'nullable|string|max:255',
                'passengers.*.date_of_birth' => 'nullable|date',
                'passengers.*.frequent_flyer_number' => 'nullable|string|max:255',
                'passengers.*.passenger_status_label' => 'nullable|string|max:255',
            ],
            4 => [
                'flight_pnr' => 'nullable|string|max:255',
                'flight_folder_number' => 'nullable|string|max:255',
                'flight_locator' => 'nullable|string|max:255',
                'flight_airline_locator' => 'nullable|string|max:255',
                'flight_type_issuer' => 'nullable|string|max:255',
                'flight_reservation_status' => 'nullable|string|max:255',
                'flight_airline' => 'nullable|string|size:2',
                'flight_vendor' => 'nullable|string|max:255',
                'flight_gds' => 'nullable|string|max:255',
                'flight_ticket_issue_limit' => 'nullable|date',
                'flight_city_code' => 'nullable|string|max:5',
                'flight_departure_airport' => 'nullable|string|max:255',
                'flight_arrival_airport' => 'nullable|string|max:255',
                'flight_departure_date' => 'nullable|date',
                'flight_return_date' => 'nullable|date',
                'flight_selling_price' => 'nullable|numeric|min:0',
            ],
            5 => [
                'hotel_name' => 'nullable|string|max:255',
                'hotel_city' => 'nullable|string|max:255',
                'hotel_room_type' => 'nullable|string|max:255',
                'hotel_status' => 'nullable|in:confirmed,on_holding,cancelled',
                'hotel_check_in' => 'nullable|date',
                'hotel_check_out' => 'nullable|date',
                'hotel_occupants' => 'nullable|integer|min:1',
                'hotel_actual_cost' => 'nullable|numeric|min:0',
                'hotel_selling_price' => 'nullable|numeric|min:0',
            ],
            6 => [
                'payment_type' => 'required|in:full,awaiting,payment_plan,dnpl',
                'payment_mode' => 'required',
                'amount_paid' => 'nullable|numeric|min:0',
                'due_date' => 'nullable|date',
                'installment_period' => 'nullable|in:none,30_days,2_months',
                'deposit_amount' => 'nullable|numeric|min:0',
                'mandatory_comment' => 'required|string|min:3',
                'booking_status' => 'required|in:pending,confirmed,cancelled',
            ],
            default => [],
        };

        try {
            $this->validate($rules);
            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
    }

    /* ── Passenger management ── */
    public function updatedAdultCount(): void { $this->reconcilePassengers(); }
    public function updatedGbeCount(): void { $this->reconcilePassengers(); }
    public function updatedChildCount(): void { $this->reconcilePassengers(); }
    public function updatedInfantCount(): void { $this->reconcilePassengers(); }

    public function inc(string $type): void
    {
        $prop = $type . 'Count';
        $this->$prop = ($this->$prop ?? 0) + 1;
        $this->reconcilePassengers();
    }

    public function dec(string $type): void
    {
        $prop = $type . 'Count';
        $this->$prop = max(0, ($this->$prop ?? 0) - 1);
        $this->reconcilePassengers();
    }

    public function reconcilePassengers(): void
    {
        $desired = [];
        foreach (['adult' => 'adultCount', 'gbe' => 'gbeCount', 'child' => 'childCount', 'infant' => 'infantCount'] as $type => $prop) {
            for ($i = 0; $i < max(0, (int) $this->$prop); $i++) {
                $desired[] = ['type' => $type];
            }
        }

        $current = $this->passengers;
        while (count($current) > count($desired)) array_pop($current);

        $result = [];
        foreach ($desired as $d) {
            $found = false;
            foreach ($current as $i => $c) {
                if ($c !== null && $c['type'] === $d['type']) {
                    $result[] = $c;
                    $current[$i] = null;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = [
                    'type' => $d['type'],
                    'title' => '', 'first_name' => '', 'last_name' => '',
                    'passport_number' => '', 'passport_country_code' => '',
                    'passport_issuing_country' => '', 'national_id_number' => '',
                    'nationality' => '', 'date_of_birth' => '',
                    'frequent_flyer_number' => '', 'e_ticket_number' => '',
                    'ptc' => '', 'passenger_status_label' => '',
                ];
            }
        }

        $this->passengers = $result;
    }

    public function passengerTypeLabel(string $type): string
    {
        return ['adult' => 'Adult', 'gbe' => 'GBE', 'child' => 'Child', 'infant' => 'Infant'][$type] ?? ucfirst($type);
    }

    public function getTotalPassengersProperty(): int
    {
        return count($this->passengers);
    }

    /* PTC auto-calculator */
    public function computePtc($dob, $passengerType): string
    {
        if (!$dob) return '';
        $dob = \Carbon\Carbon::parse($dob);
        $today = now();
        $years = $dob->diffInYears($today);

        return match (true) {
            $years >= 12 => 'ADT',   // Adult
            $years >= 2  => 'CNN',   // Child
            default       => 'INF',   // Infant
        };
    }

    /* ── Flight pricing ── */
    public function getTotalFlightCostProperty(): float
    {
        $total = 0;
        foreach (['adult', 'child', 'gbe', 'infant'] as $type) {
            $cost = (float) ($this->flight_costs[$type]['cost'] ?? 0);
            $qty = (int) ($this->flight_costs[$type]['qty'] ?? 0);
            $total += $cost * $qty;
        }
        return $total;
    }

    public function getFlightMarginProperty(): float
    {
        return (float) ($this->flight_selling_price ?: 0) - $this->totalFlightCost;
    }

    public function getTotalCostPriceProperty(): float
    {
        return $this->totalFlightCost + (float) ($this->hotel_actual_cost ?: 0);
    }

    public function getTotalSoldPriceProperty(): float
    {
        return (float) ($this->flight_selling_price ?: 0) + (float) ($this->hotel_selling_price ?: 0);
    }

    public function getTotalMarginProperty(): float
    {
        return $this->totalSoldPrice - $this->totalCostPrice - (float) ($this->cc_charges ?: 0);
    }

    public function getHotelMarginProperty(): float
    {
        return (float) ($this->hotel_selling_price ?: 0) - (float) ($this->hotel_actual_cost ?: 0);
    }

    /* ── Payment history ── */
    public function addPaymentHistory(): void
    {
        $this->payment_history[] = ['date' => '', 'method' => '', 'amount' => '', 'receipt' => ''];
    }

    public function removePaymentHistory(int $index): void
    {
        unset($this->payment_history[$index]);
        $this->payment_history = array_values($this->payment_history);
    }

    /* ── Documents ── */
    public function addDocument(): void
    {
        $this->documents[] = null;
        $this->document_types[] = '';
    }

    public function removeDocument(int $index): void
    {
        unset($this->documents[$index]);
        unset($this->document_types[$index]);
        $this->documents = array_values($this->documents);
        $this->document_types = array_values($this->document_types);
    }

    /* ── Activity log ── */
    public function addActivityEntry(): void
    {
        if (empty(trim($this->mandatory_comment))) return;

        $this->activity_log_entries[] = [
            'agent' => Auth::user()->name,
            'timestamp' => now()->toDateTimeString(),
            'action' => 'comment',
            'comment' => $this->mandatory_comment,
        ];
        $this->mandatory_comment = '';
    }

    /* ── Country list ── */
    public function countries(): array
    {
        return [
            'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AD' => 'Andorra', 'AO' => 'Angola',
            'AG' => 'Antigua', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AU' => 'Australia', 'AT' => 'Austria',
            'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados',
            'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BT' => 'Bhutan',
            'BO' => 'Bolivia', 'BA' => 'Bosnia', 'BW' => 'Botswana', 'BR' => 'Brazil', 'BN' => 'Brunei',
            'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon',
            'CA' => 'Canada', 'CV' => 'Cape Verde', 'CF' => 'Central African Republic', 'TD' => 'Chad',
            'CL' => 'Chile', 'CN' => 'China', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo',
            'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic',
            'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic',
            'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea', 'EE' => 'Estonia', 'SZ' => 'Eswatini', 'ET' => 'Ethiopia', 'FJ' => 'Fiji',
            'FI' => 'Finland', 'FR' => 'France', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia',
            'DE' => 'Germany', 'GH' => 'Ghana', 'GR' => 'Greece', 'GD' => 'Grenada', 'GT' => 'Guatemala',
            'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HN' => 'Honduras',
            'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran',
            'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica',
            'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati',
            'KP' => 'North Korea', 'KR' => 'South Korea', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos',
            'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya',
            'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MG' => 'Madagascar',
            'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta',
            'MH' => 'Marshall Islands', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'MX' => 'Mexico',
            'FM' => 'Micronesia', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro',
            'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru',
            'NP' => 'Nepal', 'NL' => 'Netherlands', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger',
            'NG' => 'Nigeria', 'MK' => 'North Macedonia', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan',
            'PW' => 'Palau', 'PS' => 'Palestine', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay',
            'PE' => 'Peru', 'PH' => 'Philippines', 'PL' => 'Poland', 'PT' => 'Portugal', 'QA' => 'Qatar',
            'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'KN' => 'St Kitts', 'LC' => 'St Lucia',
            'VC' => 'St Vincent', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome',
            'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles',
            'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia',
            'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'SS' => 'South Sudan',
            'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SE' => 'Sweden',
            'CH' => 'Switzerland', 'SY' => 'Syria', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand',
            'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TO' => 'Tonga', 'TT' => 'Trinidad', 'TN' => 'Tunisia',
            'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine',
            'AE' => 'UAE', 'GB' => 'United Kingdom', 'US' => 'United States', 'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican', 'VE' => 'Venezuela', 'VN' => 'Vietnam',
            'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe',
        ];
    }

    public function paymentMethodOptions(): array
    {
        return [
            'epay_debit' => 'Epay Debit',
            'epay_credit' => 'Epay Credit',
            'amex' => 'AMEX',
            'klarna' => 'Klarna',
            'superpay' => 'SuperPay',
            'clearpay' => 'ClearPay',
            'stripe' => 'Stripe',
            'refund' => 'Refund',
            'previous_booking' => 'Previous Booking',
            'dnpl' => 'DNPL',
            'cash' => 'Cash/Bank',
            'debit_card' => 'Debit Card',
            'credit_card' => 'Credit Card',
        ];
    }

    /* ── Save ── */
    public function resetForm(): void
    {
        $this->reset();
        $this->step = 1;
        $this->mount();
    }

    public function save()
    {
        if (empty($this->passengers)) {
            session()->flash('error', 'Add at least one passenger.');
            return;
        }

        // Enforce mandatory comment
        if (empty(trim($this->mandatory_comment))) {
            $this->addError('mandatory_comment', 'A reason/comment is required before saving.');
            return;
        }

        $this->validate([
            'lead_source' => 'required',
            'lead_nature' => 'required',
            'booking_type' => 'required',
            'booker_first_name' => 'required|string|max:255',
            'booker_last_name' => 'required|string|max:255',
            'booker_mobile' => 'required|string|max:255',
            'passengers' => 'required|array|min:1',
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'payment_type' => 'required|in:full,awaiting,payment_plan,dnpl',
            'booking_status' => 'required|in:pending,confirmed,cancelled',
            'mandatory_comment' => 'required|string|min:3',
        ]);

        $booking = DB::transaction(function () {
            // Build activity log entry
            $activityEntry = [
                'agent' => Auth::user()->name,
                'timestamp' => now()->toDateTimeString(),
                'action' => 'created',
                'comment' => $this->mandatory_comment,
            ];

            $booking = Booking::create([
                'booking_ref' => 'BKG-' . strtoupper(uniqid()),
                'customer_id' => 1,
                'user_id' => Auth::id(),
                'booking_type' => $this->booking_type,
                'lead_source' => $this->lead_source,
                'lead_nature' => $this->lead_nature,
                'is_returning_or_referral' => $this->is_returning_or_referral,
                'old_booking_reference' => $this->old_booking_reference ?: null,
                'last_payment_date' => $this->last_payment_date ?: null,
                'last_issue_date' => $this->last_issue_date ?: null,
                'referral_name' => $this->referral_name ?: null,
                'booker_title' => $this->booker_title ?: null,
                'booker_first_name' => $this->booker_first_name,
                'booker_last_name' => $this->booker_last_name,
                'booker_mobile' => $this->booker_mobile,
                'booker_landline' => $this->booker_landline ?: null,
                'booker_email' => $this->booker_email ?: null,
                'booker_address' => $this->booker_address ?: null,
                'booker_postcode' => $this->booker_postcode ?: null,
                'booker_country' => $this->booker_country ?: 'UK',
                'passenger_count' => count($this->passengers),
                'booking_status' => $this->booking_status,
                'issuance_requested_at' => $this->issuance_requested ? now() : null,
                'refund_requested_at' => $this->refund_queue ? now() : null,
                'notes' => $this->mandatory_comment,
                'activity_log' => json_encode([$activityEntry]),
            ]);

            // Activity log
            BookingActivityLog::create([
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'comment' => $this->mandatory_comment,
                'details' => null,
            ]);

            // Mandatory comment as BookingComment
            BookingComment::create([
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'comment' => $this->mandatory_comment,
                'action' => 'created',
                'is_mandatory' => true,
            ]);

            // Passengers
            foreach ($this->passengers as $p) {
                $ptc = $this->computePtc($p['date_of_birth'] ?? null, $p['type']);

                BookingPassenger::create([
                    'booking_id' => $booking->id,
                    'passenger_type' => $p['type'],
                    'title' => $p['title'] ?: null,
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'full_name' => trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')),
                    'passport_number' => $p['passport_number'] ?: null,
                    'passport_country_code' => $p['passport_country_code'] ?: null,
                    'passport_issuing_country' => $p['passport_issuing_country'] ?: null,
                    'national_id_number' => $p['national_id_number'] ?: null,
                    'nationality' => $p['nationality'] ?: null,
                    'date_of_birth' => $p['date_of_birth'] ?: null,
                    'frequent_flyer_number' => $p['frequent_flyer_number'] ?: null,
                    'e_ticket_number' => $p['e_ticket_number'] ?: null,
                    'ptc' => $ptc,
                    'passenger_status_label' => $p['passenger_status_label'] ?: null,
                ]);
            }

            // Flight detail + costs
            $flightTypes = ['flight', 'umrah', 'holiday'];
            if (in_array($this->booking_type, $flightTypes) || $this->flight_airline || $this->flight_pnr) {
                BookingFlightDetail::create([
                    'booking_id' => $booking->id,
                    'pnr' => $this->flight_pnr ?: null,
                    'folder_number' => $this->flight_folder_number ?: null,
                    'locator' => $this->flight_locator ?: null,
                    'airline_locator' => $this->flight_airline_locator ?: null,
                    'type_issuer' => $this->flight_type_issuer ?: null,
                    'reservation_status' => $this->flight_reservation_status ?: null,
                    'airline' => $this->flight_airline ? strtoupper($this->flight_airline) : null,
                    'vendor' => $this->flight_vendor ?: null,
                    'gds' => $this->flight_gds ?: null,
                    'ticket_issue_limit' => $this->flight_ticket_issue_limit ?: null,
                    'atol' => $this->flight_atol,
                    'safi' => $this->flight_safi,
                    'city_code' => $this->flight_city_code ? strtoupper($this->flight_city_code) : null,
                    'departure_airport' => $this->flight_departure_airport ?: null,
                    'arrival_airport' => $this->flight_arrival_airport ?: null,
                    'departure_date' => $this->flight_departure_date ?: null,
                    'return_date' => $this->flight_return_date ?: null,
                    'selling_price' => $this->flight_selling_price ?: 0,
                ]);

                foreach (['adult', 'child', 'gbe', 'infant'] as $type) {
                    $qty = (int) ($this->flight_costs[$type]['qty'] ?? 0);
                    if ($qty > 0) {
                        BookingFlightCost::create([
                            'booking_id' => $booking->id,
                            'cost_type' => $type,
                            'cost' => $this->flight_costs[$type]['cost'] ?: 0,
                            'quantity' => $qty,
                            'sold_price' => $this->flight_costs[$type]['sold'] ?: 0,
                        ]);
                    }
                }
            }

            // Hotel
            if ($this->booking_type === 'hotel' || $this->hotel_name) {
                BookingHotel::create([
                    'booking_id' => $booking->id,
                    'hotel_name' => $this->hotel_name ?: 'Hotel',
                    'city' => $this->hotel_city ?: null,
                    'room_type' => $this->hotel_room_type ?: null,
                    'booking_status' => $this->hotel_status,
                    'check_in' => $this->hotel_check_in ?: null,
                    'check_out' => $this->hotel_check_out ?: null,
                    'occupants' => $this->hotel_occupants ?: 1,
                    'actual_cost' => $this->hotel_actual_cost ?: 0,
                    'selling_price' => $this->hotel_selling_price ?: 0,
                ]);
            }

            // Payment
            $total = (float) ($this->flight_selling_price ?: 0) + (float) ($this->hotel_selling_price ?: 0);
            $b = 0; $a = 0; $d = 0;
            if ($this->payment_type === 'full') { $a = $total; }
            elseif ($this->payment_type === 'awaiting') { $a = $this->amount_paid ?: 0; $b = $total - $a; }
            elseif ($this->payment_type === 'payment_plan') { $a = $this->amount_paid ?: 0; $b = $total - $a; }
            elseif ($this->payment_type === 'dnpl') { $d = $this->deposit_amount ?: 0; $b = $total; }

            BookingPayment::create([
                'booking_id' => $booking->id,
                'payment_type' => $this->payment_type,
                'amount_paid' => $a,
                'total_amount' => $total,
                'balance_remaining' => $b,
                'due_date' => $this->due_date ?: null,
                'installment_period' => $this->payment_type === 'payment_plan' ? $this->installment_period : 'none',
                'installment_first_amount' => $this->installment_first_amount ?: null,
                'debit_card_change' => $this->debit_card_change,
                'deposit_amount' => $d,
                'is_deposit_nonrefundable' => $this->payment_type === 'dnpl',
                'payment_mode' => $this->payment_mode,
                'payment_mode_2' => $this->payment_mode_2 ?: null,
                'cc_charges' => $this->cc_charges ?: 0,
                'invoice_generated' => false,
            ]);

            // Payment history
            foreach ($this->payment_history as $ph) {
                if (!empty($ph['date']) && !empty($ph['amount'])) {
                    BookingPaymentHistory::create([
                        'booking_id' => $booking->id,
                        'user_id' => Auth::id(),
                        'payment_date' => $ph['date'],
                        'payment_method' => $ph['method'] ?: null,
                        'amount' => $ph['amount'],
                        'receipt_number' => $ph['receipt'] ?: null,
                    ]);
                }
            }

            // Documents
            if (!empty($this->documents)) {
                foreach ($this->documents as $i => $f) {
                    if ($f) {
                        $path = $f->store('booking-documents', 'public');
                        BookingDocument::create([
                            'booking_id' => $booking->id,
                            'uploaded_by' => Auth::id(),
                            'file_name' => $f->getClientOriginalName(),
                            'file_path' => $path,
                            'file_type' => $f->getClientMimeType(),
                            'document_type' => $this->document_types[$i] ?? 'other',
                        ]);
                    }
                }
            }

            return $booking;
        });

        $this->booking_ref = $booking->booking_number;
        AuditLogger::log(Auth::user(), $booking, 'created', 'Booking created', null, $booking->toArray());
        $this->resetExcept(['booking_ref']);
        $this->step = 1;
        $this->mount();
        session()->flash('success', "Booking #{$this->booking_ref} created successfully. Comment logged.");
    }

    public function render()
    {
        return view('livewire.create-booking', [
            'countries' => $this->countries(),
            'paymentMethods' => $this->paymentMethodOptions(),
        ]);
    }
}
