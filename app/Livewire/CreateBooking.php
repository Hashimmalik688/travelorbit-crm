<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingActivityLog;
use App\Models\BookingComment;
use App\Models\BookingDocument;
use App\Models\BookingFlightCost;
use App\Models\BookingFlightDetail;
use App\Models\BookingHotel;
use App\Models\BookingHotelRoom;
use App\Models\BookingPassenger;
use App\Models\BookingPayment;
use App\Models\BookingPaymentHistory;
use App\Models\BookingTransfer;
use App\Models\BookingVisa;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateBooking extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public int $totalSteps = 3;
    public string $currentStepId = 'lead-caller';

    // Step 1: Booking Info & Lead Source
    public $lead_source = '';
    public $lead_nature = '';
    public $booking_type = '';
    public $last_payment_date = '';
    public $last_issue_date = '';
    public $is_returning_or_referral = true;
    public $old_booking_reference = '';
    public $previous_booking_type = '';

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
    public $flight_folder_number = '';
    public $flight_atol = false;
    public $flight_safi = false;
    public $flight_selling_price = '';
    public $flightSegments = [
        [
            'pnr'                => '',
            'locator'            => '',
            'airline_locator'    => '',
            'type_issuer'        => '',
            'reservation_status' => '',
            'flight_type'        => 'return',
            'airline'            => '',
            'vendor'             => '',
            'gds'                => '',
            'cabin'              => '',
            'ticket_issue_limit' => '',
            'passenger_costs'    => [],

            'departure_airport'  => '',
            'arrival_airport'    => '',
            'departure_date'     => '',
            'return_date'        => '',
        ],
    ];
    public $flight_costs = [
        'adult'   => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'child'   => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'gbe'     => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'infant'  => ['cost' => '', 'qty' => 0, 'sold' => ''],
    ];

    // Step 5: Hotel Information (multi-hotel)
    public $hotels = [];

    // Transfers
    public $transferPickups = [];
    public $transferDropoffs = [];

    // Visas (Umrah)
    public $visas = [];

    // Excursion
    public $showExcursionStep = false;

    // Excursion
    public $excursion_name = '';
    public $excursion_destination = '';
    public $excursion_date = '';
    public $excursion_duration = '';
    public $excursion_supplier = '';
    public $excursion_meeting_point = '';
    public $excursion_reference = '';
    public $excursion_status = 'confirmed';
    public $excursion_actual_cost = '';
    public $excursion_selling_price = '';
    public $excursion_inclusions = '';
    public $excursion_notes = '';

    // Step 6: Payment & Wrap-up
    public $booking_plan     = '';
    public $payment_date     = '';
    public $payment_method   = '';
    public $amount_paid      = '';
    public $receipt_number   = '';
    public $due_date         = '';
    public $deposit_amount   = '';
    public $installment_count  = 2;   // how many instalments
    public $instalments        = [];  // per-instalment rows
    public $payment_history    = [];  // legacy (kept for save compat)
    // kept for DB compat
    public $payment_mode    = '';
    public $payment_mode_2  = '';
    public $installment_period = 'none';
    public $installment_first_amount = '';
    public $debit_card_change = false;

    // Cost & margins
    public $cc_charges = '';
    public $cc_charge_rate = '';
    public $safi_charges = '';

    // Card payment fields
    public $card_number = '';
    public $card_expiry = '';
    public $card_cvv = '';
    public $card_holder_name = '';
    public $billing_address = '';
    public $billing_postcode = '';

    // Documents
    public $documents = [];
    public $document_types = [];

    // Activity log
    public $mandatory_comment = '';
    public $activity_log_entries = [];

    // Status
    public $booking_status = 'pending';

    // Inline reason editor
    public ?int $reasonEditIndex = null;
    public string $reasonEditText = '';
    public $issuance_requested = false;
    public $refund_queue = false;
    public $booking_ref = '';

    public function mount(): void
    {
        $this->reconcilePassengers();
        $this->recalcSteps();
        $this->updateCurrentStepId();
        $this->bookingCountCache = Booking::where('user_id', Auth::id())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count() + 1;
        $this->logActivity('Opened new booking form', '', 'navigated');
    }

    public function getBookingCountThisMonthProperty(): int
    {
        return $this->bookingCountCache;
    }

    private int $bookingCountCache = 1;

    public function updatedBookingType(): void
    {
        $this->recalcSteps();
        $this->updateCurrentStepId();
        $labels = ['flight'=>'Flight','hotel'=>'Hotel','umrah'=>'Umrah','holiday'=>'Holiday','transfers'=>'Transfers','excursion'=>'Excursion','visa'=>'Visa'];
        $this->logActivity('Booking type set to ' . ($labels[$this->booking_type] ?? $this->booking_type), '', 'updated');
    }

    public function updatedLeadSource(): void
    {
        $labels = ['to_returning'=>'TO Returning','to_referral'=>'TO Referral','referral_client'=>'Referral Client','returning_client'=>'Returning Client','fb'=>'Facebook','wa'=>'WhatsApp','email'=>'Email','diaspora_group'=>'Diaspora Group','instagram'=>'Instagram','tiktok'=>'TikTok','website'=>'Website','google'=>'Google','personal'=>'Personal'];
        $this->logActivity('Lead source set to ' . ($labels[$this->lead_source] ?? $this->lead_source), '', 'updated');
    }

    public function updatedLeadNature(): void
    {
        $labels = ['new_booking'=>'New Booking','date_change'=>'Date Change','refund_booking'=>'Refund Booking','previous_booking'=>'Previous Booking'];
        $this->logActivity('Lead nature set to ' . ($labels[$this->lead_nature] ?? $this->lead_nature), '', 'updated');
    }

    public function updatedBookingPlan(): void
    {
        $labels = ['full'=>'Full Payment','awaiting'=>'Awaiting Payment','payment_plan'=>'Payment Plan','dnpl'=>'DNPL'];
        $this->logActivity('Payment type set to ' . ($labels[$this->booking_plan] ?? $this->booking_plan), '', 'updated');
        if ($this->booking_plan === 'payment_plan') {
            $this->generateInstalments();
        }
    }

    public function updatedInstallmentCount(): void
    {
        $this->generateInstalments();
    }

    public function generateInstalments(): void
    {
        $count = max(2, min(12, (int) $this->installment_count));
        $existing = $this->instalments;
        $this->instalments = [];
        for ($i = 0; $i < $count; $i++) {
            $this->instalments[] = [
                'number'  => $i + 1,
                'total'   => $count,
                'amount'  => $existing[$i]['amount'] ?? '',
                'method'  => $existing[$i]['method'] ?? '',
                'date'    => $existing[$i]['date']   ?? now()->addMonths($i)->format('d/m/Y'),
                'receipt' => $existing[$i]['receipt'] ?? '',
            ];
        }
    }

    public function updatedPaymentMode(): void
    {
        if ($this->payment_mode) {
            $this->logActivity('Payment mode 1 set to ' . $this->payment_mode, '', 'updated');
        }
    }

    public function updatedPaymentMode2(): void
    {
        if ($this->payment_mode_2) {
            $this->logActivity('Payment mode 2 set to ' . $this->payment_mode_2, '', 'updated');
        }
    }

    public function updatedPaymentMethod(): void
    {
        $cardRates = [
            'debit_card'  => 1.5,
            'credit_card' => 2.5,
            'amex'        => 2.5,
        ];
        if (isset($cardRates[$this->payment_method])) {
            $this->cc_charge_rate = $cardRates[$this->payment_method];
            $this->cc_charges     = round($this->totalSoldPrice * $this->cc_charge_rate / 100, 2);
        } else {
            $this->cc_charge_rate = '';
            $this->cc_charges     = 0;
        }
    }

    public function updatedCcChargeRate(): void
    {
        $rate = (float) $this->cc_charge_rate;
        $this->cc_charges = $rate > 0 ? round($this->totalSoldPrice * $rate / 100, 2) : 0;
    }

    public function updatedDebitCardChange(): void
    {
        $this->logActivity('Debit Card Change ' . ($this->debit_card_change ? 'enabled' : 'disabled'), '', 'updated');
    }

    public function updatedFlightAtol(): void
    {
        $this->logActivity('ATOL ' . ($this->flight_atol ? 'enabled' : 'disabled'), '+£2.50 per non-infant pax', $this->flight_atol ? 'added' : 'removed');
    }

    public function updatedFlightSafi(): void
    {
        $this->logActivity('SAFI ' . ($this->flight_safi ? 'enabled' : 'disabled'), '+£2.50 per non-infant pax', $this->flight_safi ? 'added' : 'removed');
    }



    public function updatedBookingStatus(): void
    {
        $this->logActivity('Booking status set to ' . ucfirst($this->booking_status), '', 'updated');
    }

    public function updatedAmountPaid(): void
    {
        if ($this->amount_paid) {
            $this->logActivity('Amount paid set to £' . number_format((float)$this->amount_paid, 2), '', 'updated');
        }
    }

    public function updatedHotels($value, $key): void
    {
        // Match both "hotels.X.number_of_rooms" and "X.number_of_rooms" formats
        if (preg_match('/(?:\d+\.)*?(\d+)\.number_of_rooms$/', (string) $key, $m)) {
            $idx = (int) $m[1];
            $desired = max(1, (int) $value);
            $this->hotels[$idx]['rooms'] = $this->hotels[$idx]['rooms'] ?? [];
            while (count($this->hotels[$idx]['rooms']) > $desired) {
                array_pop($this->hotels[$idx]['rooms']);
            }
            while (count($this->hotels[$idx]['rooms']) < $desired) {
                $this->hotels[$idx]['rooms'][] = [
                    'room_type' => '',
                    'occupants' => 1,
                    'meal_basis' => 'room_only',
                ];
            }
        }
    }

    public function addFlightSegment(): void
    {
        $this->flightSegments[] = [
            'pnr'                => '',
            'locator'            => '',
            'airline_locator'    => '',
            'type_issuer'        => '',
            'reservation_status' => '',
            'flight_type'        => 'return',
            'airline'            => '',
            'vendor'             => '',
            'gds'                => '',
            'cabin'              => '',
            'ticket_issue_limit' => '',
            'passenger_costs'    => [],

            'departure_airport'  => '',
            'arrival_airport'    => '',
            'departure_date'     => '',
            'return_date'        => '',
        ];
        $this->syncPassengerCosts();
        $this->logActivity('Added flight segment ' . count($this->flightSegments), '', 'added');
    }

    public function syncPassengerCosts(): void
    {
        $paxLabels = [];
        foreach ($this->passengers as $pi => $p) {
            $type = $p['type'] ?? 'adult';
            $typeCount = 1;
            for ($j = 0; $j < $pi; $j++) {
                if (($this->passengers[$j]['type'] ?? '') === $type) $typeCount++;
            }
            $labels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
            $paxLabels[$pi] = ($labels[$type] ?? 'Pax') . ' ' . $typeCount;
        }

        foreach ($this->flightSegments as $si => &$seg) {
            $existing = $seg['passenger_costs'] ?? [];
            $updated = [];
            foreach ($this->passengers as $pi => $p) {
                if (isset($existing[$pi])) {
                    $updated[$pi] = $existing[$pi];
                } else {
                    $updated[$pi] = ['cost' => '', 'sold' => ''];
                }
            }
            $seg['passenger_costs'] = array_values($updated);
        }
    }

    public function removeFlightSegment(int $index): void
    {
        if (count($this->flightSegments) > 1) {
            unset($this->flightSegments[$index]);
            $this->flightSegments = array_values($this->flightSegments);
        }
    }

    public function addHotel(): void
    {
        $this->hotels[] = [
            'hotel_name' => '',
            'city' => '',
            'status' => 'confirmed',
            'check_in' => '',
            'check_out' => '',
            'actual_cost' => '',
            'selling_price' => '',
            'number_of_rooms' => 1,
            'rooms' => [
                [
                    'room_type' => '',
                    'occupants' => 1,
                    'meal_basis' => 'room_only',
                ],
            ],
        ];
        $this->recalcSteps();
        $this->updateCurrentStepId();
        $this->logActivity('Added hotel ' . count($this->hotels), '', 'added');
    }

    public function removeHotel(int $index): void
    {
        if (count($this->hotels) > 0) {
            unset($this->hotels[$index]);
            $this->hotels = array_values($this->hotels);
            if (empty($this->hotels)) {
                $this->recalcSteps();
                if ($this->currentStepId === 'hotel') {
                    $this->step = max(1, $this->step - 1);
                    $this->updateCurrentStepId();
                }
            }
        }
    }

    public function addPickup(): void
    {
        $this->transferPickups[] = ['location' => '', 'date_time' => '', 'flight_number' => '', 'route' => '', 'vehicle_type' => '', 'supplier' => '', 'actual_cost' => '', 'selling_price' => '', 'status' => 'confirmed', 'notes' => ''];
        $this->recalcSteps();
        $this->updateCurrentStepId();
    }

    public function removePickup(int $index): void
    {
        unset($this->transferPickups[$index]);
        $this->transferPickups = array_values($this->transferPickups);
    }

    public function addDropoff(): void
    {
        $this->transferDropoffs[] = ['location' => '', 'date_time' => '', 'flight_number' => '', 'route' => '', 'vehicle_type' => '', 'supplier' => '', 'actual_cost' => '', 'selling_price' => '', 'status' => 'confirmed', 'notes' => ''];
        $this->recalcSteps();
        $this->updateCurrentStepId();
    }

    public function removeDropoff(int $index): void
    {
        unset($this->transferDropoffs[$index]);
        $this->transferDropoffs = array_values($this->transferDropoffs);
    }

    public function addVisa(): void
    {
        $this->visas[] = [
            'passenger_name'   => '',
            'visa_type'        => 'umrah',
            'visa_reference'   => '',
            'visa_number'      => '',
            'application_date' => '',
            'issue_date'       => '',
            'expiry_date'      => '',
            'status'           => 'pending',
            'actual_cost'      => '',
            'selling_price'    => '',
            'notes'            => '',
        ];
        $this->recalcSteps();
        $this->logActivity('Added visa entry ' . count($this->visas), '', 'added');
    }

    public function removeVisa(int $index): void
    {
        unset($this->visas[$index]);
        $this->visas = array_values($this->visas);
        $this->recalcSteps();
        if (empty($this->visas) && $this->currentStepId === 'visa' && $this->booking_type === 'visa') {
            $this->step = max(1, $this->step - 1);
            $this->updateCurrentStepId();
        }
    }



    public function openReasonEdit(int $index): void
    {
        $this->reasonEditIndex = $index;
        $this->reasonEditText = '';
    }

    public function saveReasonEdit(): void
    {
        if ($this->reasonEditIndex !== null && !empty(trim($this->reasonEditText))) {
            $this->addReasonToEntry($this->reasonEditIndex, $this->reasonEditText);
        }
        $this->reasonEditIndex = null;
        $this->reasonEditText = '';
    }

    public function cancelReasonEdit(): void
    {
        $this->reasonEditIndex = null;
        $this->reasonEditText = '';
    }

    public function recalcSteps(): void
    {
        $this->totalSteps = count($this->stepLabels);
    }

    /* ── Auto-detect previous booking ── */
    private ?string $lastMatchedBookerKey = null;

    public function checkPreviousBooking(): void
    {
        $firstName = trim($this->booker_first_name);
        $lastName  = trim($this->booker_last_name);
        $mobile    = trim($this->booker_mobile);
        $email     = trim($this->booker_email);

        if (empty($firstName) && empty($lastName) && empty($mobile)) return;

        // Skip if we already ran against these exact values
        $key = $firstName.'|'.$lastName.'|'.$mobile.'|'.$email;
        if ($this->lastMatchedBookerKey === $key) return;
        $this->lastMatchedBookerKey = $key;

        $query = Booking::query()->latest();

        if (!empty($mobile)) {
            $match = $query->where('booker_mobile', $mobile)->first();
            if ($match) { $this->applyMatch($match); return; }
        }

        if (!empty($email)) {
            $match = Booking::where('booker_email', $email)->latest()->first();
            if ($match) { $this->applyMatch($match); return; }
        }

        if (!empty($firstName) && !empty($lastName)) {
            $match = Booking::where('booker_first_name', 'ILIKE', $firstName)
                ->where('booker_last_name', 'ILIKE', $lastName)
                ->latest()
                ->first();
            if ($match) { $this->applyMatch($match); return; }
        }
    }

    private function applyMatch(Booking $booking): void
    {
        if (empty($this->old_booking_reference)) {
            $this->old_booking_reference = (string) $booking->booking_number;
        }
        if (empty($this->previous_booking_type)) {
            $this->previous_booking_type = $booking->booking_type;
        }
    }

    /* ── Navigation ── */
    public function getStepLabelsProperty(): array
    {
        $labels = [
            ['id' => 'lead-caller',  'label' => 'Lead & Caller'],
            ['id' => 'travellers',   'label' => 'Travellers'],
        ];

        $bt = $this->booking_type ?: '';

        if (in_array($bt, ['flight', 'umrah', 'holiday'])) {
            $labels[] = ['id' => 'flight', 'label' => 'Flight'];
        }
        if ($bt === 'umrah' || $bt === 'holiday') {
            $labels[] = ['id' => 'visa', 'label' => 'Visa'];
        }
        if (in_array($bt, ['hotel', 'holiday']) || !empty($this->hotels)) {
            $labels[] = ['id' => 'hotel', 'label' => 'Hotel'];
        }
        if ($bt === 'transfers' || ($bt === 'holiday' && (!empty($this->transferPickups) || !empty($this->transferDropoffs)))) {
            $labels[] = ['id' => 'transfers', 'label' => 'Transfers'];
        }
        if ($bt === 'excursion') {
            $labels[] = ['id' => 'excursion', 'label' => 'Excursion'];
        }
        if ($bt === 'visa') {
            $labels[] = ['id' => 'visa', 'label' => 'Visa'];
        }

        $labels[] = ['id' => 'payment', 'label' => 'Payment'];
        return $labels;
    }

    private function stepIdMap(): array
    {
        $labels = $this->stepLabels;
        $map = [];
        foreach ($labels as $i => $l) {
            $map[$i + 1] = $l['id'];
        }
        return $map;
    }

    public function nextStep(): void
    {
        // Always clear stale DOB errors first
        foreach ($this->passengers as $i => $p) {
            $this->resetErrorBag("passengers.{$i}.date_of_birth");
        }
        $this->resetErrorBag('passengers');

        $this->recalcSteps();
        $sid = $this->currentStepId;

        if ($sid === 'lead-caller') {
            $this->checkPreviousBooking();
        }

        // ── Travellers step: DOB-type mismatch is a hard block ─────────────
        if ($sid === 'travellers') {
            $this->syncFlightCostQtys();

            $typeRanges = [
                'adult'  => ['min' => 16, 'max' => 999, 'label' => 'Adult (16+)'],
                'gbe'    => ['min' => 12, 'max' => 15,  'label' => 'Youth (12-15)'],
                'child'  => ['min' => 2,  'max' => 11,  'label' => 'Child (2-11)'],
                'infant' => ['min' => 0,  'max' => 1,   'label' => 'Infant (0-1)'],
            ];
            $hasError = false;

            foreach ($this->passengers as $i => $p) {
                if (empty($p['date_of_birth'])) continue;

                try {
                    $years = \Carbon\Carbon::parse($p['date_of_birth'])->diffInYears(now());
                } catch (\Exception) {
                    continue;
                }

                $type  = $p['type'] ?? 'adult';
                $range = $typeRanges[$type] ?? null;
                if (!$range) continue;

                if ($years < $range['min'] || $years > $range['max']) {
                    $computed = match (true) {
                        $years >= 16 => 'Adult (ADT)',
                        $years >= 12 => 'Youth (GBE)',
                        $years >= 2  => 'Child (CNN)',
                        default      => 'Infant (INF)',
                    };
                    $this->addError(
                        "passengers.{$i}.date_of_birth",
                        "DOB shows {$computed} - passenger is booked as {$range['label']}. Please correct the type or the date."
                    );
                    $hasError = true;
                }
            }

            if ($hasError) return; // Block navigation
        }

        $this->validateStep($this->step);
        $this->step = min($this->totalSteps, $this->step + 1);
        $this->updateCurrentStepId();
        $stepLabels = ['lead-caller' => 'Lead & Caller', 'travellers' => 'Travellers', 'flight' => 'Flight', 'hotel' => 'Hotel', 'payment' => 'Payment'];
        $this->logActivity('Moved to ' . ($stepLabels[$this->currentStepId] ?? $this->currentStepId) . ' step', '', 'navigated');
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
        $this->updateCurrentStepId();
        $stepLabels = ['lead-caller' => 'Lead & Caller', 'travellers' => 'Travellers', 'flight' => 'Flight', 'hotel' => 'Hotel', 'payment' => 'Payment'];
        $this->logActivity('Went back to ' . ($stepLabels[$this->currentStepId] ?? $this->currentStepId) . ' step', '', 'navigated');
    }

    private function updateCurrentStepId(): void
    {
        $stepIdMap = $this->stepIdMap();
        $this->currentStepId = $stepIdMap[$this->step] ?? 'lead-caller';
    }

    private function syncFlightCostQtys(): void
    {
        $this->flight_costs['adult']['qty']  = $this->adultCount;
        $this->flight_costs['child']['qty']  = $this->childCount;
        $this->flight_costs['gbe']['qty']    = $this->gbeCount;
        $this->flight_costs['infant']['qty'] = $this->infantCount;
    }

    /* ── Validation ── */
    protected function validateStep(int $step): void
    {
        $stepId = $this->stepLabels[$step - 1]['id'] ?? '';

        $rules = match ($stepId) {
            'lead-caller' => [
                'lead_source'              => 'required|in:to_returning,to_referral,referral_client,returning_client,fb,wa,email,diaspora_group,instagram,tiktok,website,google,personal',
                'lead_nature'              => 'required|in:new_booking,date_change,refund_booking,previous_booking',
                'booking_type'             => 'required|in:flight,hotel,umrah,holiday,transfers,excursion,visa',
                'last_payment_date'        => 'required|date',
                'last_issue_date'          => 'required|date',
                'old_booking_reference'    => 'nullable|string|max:255',
                'previous_booking_type'    => 'nullable|in:flight,hotel,holiday,umrah,transfers,excursion,visa',
                'booker_title'             => 'required|in:1,2,3,4,5,6',
                'booker_first_name'        => 'required|string|max:255',
                'booker_last_name'         => 'required|string|max:255',
                'booker_mobile'            => 'required|string|max:255|regex:/^[0-9]+$/',
                'booker_landline'          => 'nullable|string|max:255',
                'booker_email'             => 'required|email:rfc|max:255',
                'booker_address'           => 'required|string',
                'booker_postcode'          => 'required|string|max:20',
            ],
            'travellers' => [
                'passengers'                     => 'required|array|min:1',
                'passengers.*.title'             => 'required|in:Mr.,Ms.,Mrs.,Mstr,Miss,Dr.',
                'passengers.*.first_name'        => 'required|string|max:255',
                'passengers.*.last_name'         => 'required|string|max:255',
                'passengers.*.date_of_birth'     => 'required|date',
                'passengers.*.passport_number'   => 'required|string|max:255',
                'passengers.*.contact_number'    => 'required|string|max:255|regex:/^[0-9+]+$/',
            ],
            'flight' => [
                'flightSegments'                              => [
                    'required', 'array', 'min:1',
                    function (string $attribute, mixed $value, $fail) {
                        foreach ($value as $i => $seg) {
                            if (($seg['flight_type'] ?? 'return') === 'return' && empty($seg['return_date'])) {
                                $fail("PNR " . ($i+1) . " return date is required for round-trip flights.");
                            }
                        }
                    },
                ],
                'flightSegments.*.locator'                    => 'required|string|max:20',
                'flightSegments.*.airline_locator'            => 'required|string|max:20',
                'flightSegments.*.vendor'                     => 'required|string|max:255',
                'flightSegments.*.airline'                    => 'required|string|max:3',
                'flightSegments.*.gds'                        => 'required|string',
                'flightSegments.*.cabin'                      => 'required|string',
                'flightSegments.*.flight_type'                => 'required|in:one_way,return',
                'flightSegments.*.reservation_status'         => 'required|string',
                'flightSegments.*.ticket_issue_limit'         => 'required|date',
                'flightSegments.*.departure_airport'          => 'required|string|max:5',
                'flightSegments.*.arrival_airport'            => 'required|string|max:5',
                'flightSegments.*.departure_date'             => 'required|date',
                'flightSegments.*.return_date'                => 'nullable|date',
            ],
            'visa' => [
                'visas'                     => 'nullable|array',
                'visas.*.passenger_name'    => 'nullable|string|max:255',
                'visas.*.visa_type'         => 'nullable|string',
                'visas.*.status'            => 'nullable|string',
            ],
            'transfers' => [
                // transfers are optional items; validate only if added
            ],
            'excursion' => [
                'excursion_name'        => 'required|string|max:255',
                'excursion_destination' => 'required|string|max:255',
                'excursion_date'        => 'required|date',
            ],
            'hotel' => count($this->hotels) ? [
                'hotels.*.hotel_name' => 'required|string|max:255',
                'hotels.*.city'       => 'required|string|max:255',
                'hotels.*.check_in'   => 'required|date',
                'hotels.*.check_out'  => 'required|date',
            ] : [],
            'payment' => [
                'payment_method'    => 'required',
                'mandatory_comment' => 'required|string|min:3',
            ],
            default => [],
        };

        $messages = [];
        $typeLabels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
        $typeCounters = [];
        foreach ($this->passengers as $i => $p) {
            $type = $p['type'] ?? 'adult';
            $typeCounters[$type] = ($typeCounters[$type] ?? 0) + 1;
            $label = ($typeLabels[$type] ?? ucfirst($type)) . ' ' . $typeCounters[$type];
            foreach (['title' => 'title', 'first_name' => 'first name', 'last_name' => 'last name', 'date_of_birth' => 'date of birth', 'passport_number' => 'passport number', 'contact_number' => 'contact number'] as $field => $human) {
                $messages["passengers.{$i}.{$field}.required"]  = "{$label} {$human} is required.";
                $messages["passengers.{$i}.{$field}.date"]      = "{$label} {$human} must be a valid date.";
                $messages["passengers.{$i}.{$field}.in"]        = "{$label} title is invalid.";
                $messages["passengers.{$i}.{$field}.regex"]     = "{$label} {$human} must contain only digits.";
            }
        }

        foreach ($this->flightSegments as $i => $seg) {
            $pnr = 'PNR ' . ($i + 1);
            $messages["flightSegments.{$i}.locator.required"]               = "{$pnr} locator is required.";
            $messages["flightSegments.{$i}.airline_locator.required"]       = "{$pnr} airline locator is required.";
            $messages["flightSegments.{$i}.vendor.required"]                = "{$pnr} vendor is required.";
            $messages["flightSegments.{$i}.airline.required"]               = "{$pnr} airline is required.";
            $messages["flightSegments.{$i}.airline.max"]                    = "{$pnr} airline code must be 3 characters or less.";
            $messages["flightSegments.{$i}.gds.required"]                   = "{$pnr} GDS is required.";
            $messages["flightSegments.{$i}.cabin.required"]                 = "{$pnr} cabin class is required.";
            $messages["flightSegments.{$i}.reservation_status.required"]    = "{$pnr} reservation status is required.";
            $messages["flightSegments.{$i}.ticket_issue_limit.required"]    = "{$pnr} ticket limit date is required.";
            $messages["flightSegments.{$i}.ticket_issue_limit.date"]        = "{$pnr} ticket limit must be a valid date.";
            $messages["flightSegments.{$i}.departure_airport.required"]     = "{$pnr} departure airport is required.";
            $messages["flightSegments.{$i}.departure_airport.max"]          = "{$pnr} departure airport code must be 5 characters or less.";
            $messages["flightSegments.{$i}.arrival_airport.required"]       = "{$pnr} arrival airport is required.";
            $messages["flightSegments.{$i}.arrival_airport.max"]            = "{$pnr} arrival airport code must be 5 characters or less.";
            $messages["flightSegments.{$i}.departure_date.required"]        = "{$pnr} departure date is required.";
            $messages["flightSegments.{$i}.departure_date.date"]            = "{$pnr} departure date must be a valid date.";
        }

        $messages['payment_method.required']    = 'Please select a payment method.';
        $messages['mandatory_comment.required'] = 'Booking notes are required.';
        $messages['mandatory_comment.min']      = 'Booking notes must be at least 3 characters.';

        foreach ($this->hotels as $i => $hotel) {
            $hl = 'Hotel ' . ($i + 1);
            $messages["hotels.{$i}.hotel_name.required"] = "{$hl} hotel name is required.";
            $messages["hotels.{$i}.city.required"]       = "{$hl} city is required.";
            $messages["hotels.{$i}.check_in.required"]   = "{$hl} check-in date is required.";
            $messages["hotels.{$i}.check_in.date"]       = "{$hl} check-in must be a valid date.";
            $messages["hotels.{$i}.check_out.required"]  = "{$hl} check-out date is required.";
            $messages["hotels.{$i}.check_out.date"]      = "{$hl} check-out must be a valid date.";
        }

        $messages['excursion_name.required']        = 'Excursion name is required.';
        $messages['excursion_destination.required'] = 'Excursion destination is required.';
        $messages['excursion_date.required']        = 'Excursion date is required.';
        $messages['excursion_date.date']            = 'Excursion date must be a valid date.';

        if (!empty($rules)) {
            $this->validate($rules, $messages);
        }
    }

    /* ── Passenger management ── */
    public function updatedAdultCount(): void { $this->reconcilePassengers(); $this->syncFlightCostQtys(); }
    public function updatedGbeCount(): void { $this->reconcilePassengers(); $this->syncFlightCostQtys(); }
    public function updatedChildCount(): void { $this->reconcilePassengers(); $this->syncFlightCostQtys(); }
    public function updatedInfantCount(): void { $this->reconcilePassengers(); $this->syncFlightCostQtys(); }

    public function updatedPassengers($value, $key): void
    {
        // Only validate DOB when date_of_birth changes — and only that specific passenger
        if (preg_match('/^passengers\.(\d+)\.date_of_birth$/', (string)$key, $m)) {
            $this->validatePassengerDob((int)$m[1]);
        }
    }

    public function validatePassengerDob(int $index): void
    {
        $p = $this->passengers[$index] ?? null;
        if (!$p || empty($p['date_of_birth']) || empty($p['type'])) return;

        $this->resetErrorBag("passengers.{$index}.date_of_birth");

        try {
            $dobDate = \Carbon\Carbon::parse($p['date_of_birth']);
        } catch (\Exception $e) {
            $this->addError("passengers.{$index}.date_of_birth", 'Invalid date format');
            return;
        }

        if ($dobDate->gt(now())) {
            $this->addError("passengers.{$index}.date_of_birth", 'Date of birth cannot be in the future');
            return;
        }

        $years = $dobDate->diffInYears(now());
        $type  = $p['type'];
        $valid = match ($type) {
            'adult'  => $years >= 16,
            'gbe'    => $years >= 12 && $years < 16,
            'child'  => $years >= 2  && $years < 12,
            'infant' => $years < 2,
            default  => true,
        };
        if (!$valid) {
            $this->addError("passengers.{$index}.date_of_birth", match ($type) {
                'adult'  => 'Adult must be 16+ years old',
                'gbe'    => 'Youth must be 12-15 years old',
                'child'  => 'Child must be 2-11 years old',
                'infant' => 'Infant must be under 2 years old',
                default  => 'Age does not match passenger type',
            });
        }
    }

    private function logActivity(string $action, string $detail = '', string $type = 'info'): void
    {
        // God-mode admins (CEO) operate untracked — no activity log entry at all.
        if (Auth::user()?->isGodMode()) {
            return;
        }
        $this->activity_log_entries[] = [
            'agent'     => Auth::user()->name ?? 'System',
            'timestamp' => now()->format('d M Y, g:i A'),
            'action'    => $action,
            'detail'    => $detail,
            'type'      => $type,
            'comment'   => '',
            'color'     => $this->getActivityColorConfig($action, $type),
        ];
    }

    public function getActivityColorConfig(string $action, string $type = 'info'): array
    {
        if (stripos($action, 'cancelled') !== false || stripos($action, 'cancel') !== false || stripos($action, 'refund') !== false) {
            return ['dot' => '#DC2626', 'bg' => 'rgba(220,38,38,.06)', 'border' => '#DC2626', 'label' => 'Cancel/Refund'];
        }
        if (stripos($action, 'approved') !== false || stripos($action, 'received') !== false || stripos($action, 'paid') !== false) {
            return ['dot' => '#16A34A', 'bg' => 'rgba(22,163,74,.06)', 'border' => '#16A34A', 'label' => 'Payment'];
        }
        if (stripos($action, 'requested') !== false || stripos($action, 'charged') !== false || stripos($action, 'raised') !== false) {
            return ['dot' => '#F59E0B', 'bg' => 'rgba(245,158,11,.06)', 'border' => '#F59E0B', 'label' => 'Charge'];
        }
        if ($type === 'added' || stripos($action, 'created') !== false || stripos($action, 'opened') !== false) {
            return ['dot' => '#0EA5E9', 'bg' => 'rgba(14,165,233,.06)', 'border' => '#0EA5E9', 'label' => 'Created'];
        }
        if (stripos($action, 'ticket') !== false || stripos($action, 'issued') !== false || stripos($action, 'document') !== false || stripos($action, 'voucher') !== false) {
            return ['dot' => '#7C3AED', 'bg' => 'rgba(124,58,237,.06)', 'border' => '#7C3AED', 'label' => 'Docs'];
        }
        if (stripos($action, 'hotel') !== false || stripos($action, 'transfer') !== false) {
            return ['dot' => '#0891B2', 'bg' => 'rgba(8,145,178,.06)', 'border' => '#0891B2', 'label' => 'Hotel/Transfer'];
        }
        if ($type === 'navigated') {
            return ['dot' => '#94A3B8', 'bg' => 'rgba(148,163,184,.06)', 'border' => '#94A3B8', 'label' => 'Navigation'];
        }
        return ['dot' => '#94A3B8', 'bg' => 'transparent', 'border' => '#94A3B8', 'label' => 'Update'];
    }

    public function inc(string $type): void
    {
        $prop = $type . 'Count';
        $this->$prop = ($this->$prop ?? 0) + 1;
        $this->reconcilePassengers();
        $this->syncFlightCostQtys();
        $this->syncPassengerCosts();
        $labels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
        $this->logActivity('Added ' . ($labels[$type] ?? $type) . ' passenger', 'Total: ' . $this->totalPassengers . ' passenger(s)', 'added');
    }

    public function dec(string $type): void
    {
        $prop = $type . 'Count';
        $before = (int) $this->$prop;
        $this->$prop = max(0, $before - 1);
        $this->reconcilePassengers();
        $this->syncFlightCostQtys();
        if ($before > 0) {
            $labels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
            $this->logActivity('Removed ' . ($labels[$type] ?? $type) . ' passenger', 'Total: ' . $this->totalPassengers . ' passenger(s)', 'removed');
        }
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
                    'contact_number' => '',
                    'cost_per_pax' => '',
                    'sold_per_pax' => '',
                    'frequent_flyer_number' => '', 'e_ticket_number' => '',
                    'ptc' => '', 'passenger_status_label' => '',
                ];
            }
        }

        $this->passengers = $result;
    }

    public function passengerTypeLabel(string $type): string
    {
        return ['adult' => 'ADT', 'gbe' => 'GBE', 'child' => 'CNN', 'infant' => 'INF'][$type] ?? ucfirst($type);
    }

    public function passengerTypeFullName(string $type): string
    {
        return ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'][$type] ?? ucfirst($type);
    }

    public function getPassengerNumber(int $index, string $type): int
    {
        $count = 1;
        for ($i = 0; $i < $index; $i++) {
            if (($this->passengers[$i]['type'] ?? '') === $type) {
                $count++;
            }
        }
        return $count;
    }

    public function computeAgeInfo(string $dob): array
    {
        if (!$dob) return ['years' => 0, 'months' => 0, 'days' => 0, 'current_type' => 'infant', 'next_ptc' => null, 'next_ptc_date' => null];

        $dobDate = \Carbon\Carbon::parse($dob);
        $today = now();

        $totalDays = $dobDate->diffInDays($today);
        $years = (int) floor($totalDays / 365);
        $remainingDays = $totalDays - ($years * 365);
        $months = (int) floor($remainingDays / 30);
        $days = round($remainingDays - ($months * 30));

        // Categories:
        // Infant: 0 to 1y 364d (under 2 years)
        // Child: 2 to 11 years (from 2nd birthday up to day before 12th)
        // Youth: 12 to 15 years (from 12th birthday up to day before 16th)
        // Adult: 16+ years (from 16th birthday onwards)

        $currentType = $years >= 16 ? 'adult' : ($years >= 12 ? 'youth' : ($years >= 2 ? 'child' : 'infant'));

        $nextPtc = null;
        $nextPtcDate = null;

        if ($currentType === 'infant') {
            $nextPtc = 'Child (CNN)';
            $nextPtcDate = $dobDate->copy()->addYears(2)->format('d M Y');
        } elseif ($currentType === 'child') {
            $nextPtc = 'Youth (GBE)';
            $nextPtcDate = $dobDate->copy()->addYears(12)->format('d M Y');
        } elseif ($currentType === 'youth') {
            $nextPtc = 'Adult (ADT)';
            $nextPtcDate = $dobDate->copy()->addYears(16)->format('d M Y');
        }

        return [
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'current_type' => $currentType,
            'next_ptc' => $nextPtc,
            'next_ptc_date' => $nextPtcDate,
        ];
    }

    public function getPnrLabel(int $index): string
    {
        $types = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
        $pi = 0;
        foreach (['adult', 'gbe', 'child', 'infant'] as $type) {
            $count = (int)($this->{$type . 'Count'} ?? 0);
            for ($i = 0; $i < $count; $i++) {
                if ($pi === $index) return $types[$type] . ' x ' . ($i + 1);
                $pi++;
            }
        }
        return 'PNR ' . ($index + 1);
    }

    public function getTotalPassengersProperty(): int
    {
        return count($this->passengers);
    }

    /* PTC auto-calculator */
    public function computePtc($dob, $passengerType): string
    {
        // Youth (GBE) always returns GBE
        if ($passengerType === 'gbe') return 'GBE';

        if (!$dob) return '';
        $dob = \Carbon\Carbon::parse($dob);
        $today = now();
        $years = $dob->diffInYears($today);

        return match (true) {
            $years >= 16 => 'ADT',   // Adult (16+)
            $years >= 12 => 'GBE',   // Youth (12-15)
            $years >= 2  => 'CNN',   // Child (2-11)
            default       => 'INF',   // Infant (0-1y364d)
        };
    }

    /* ── Flight pricing ── */
    public function getTotalFlightCostProperty(): float
    {
        $total = 0;
        foreach ($this->flightSegments as $seg) {
            foreach ($seg['passenger_costs'] ?? [] as $pc) {
                $total += (float) ($pc['cost'] ?? 0);
            }
        }

        $taxableCount = $this->nonInfantPassengerCount;
        if ($taxableCount > 0) {
            $taxPerPerson = 0;
            if ($this->flight_atol) $taxPerPerson += 2.50;
            if ($this->flight_safi) $taxPerPerson += 2.50;
            $total += ($taxPerPerson * $taxableCount);
        }

        return $total;
    }

    public function getTotalFlightSoldProperty(): float
    {
        $total = 0;
        foreach ($this->flightSegments as $seg) {
            foreach ($seg['passenger_costs'] ?? [] as $pc) {
                $total += (float) ($pc['sold'] ?? 0);
            }
        }
        return $total;
    }

    public function getNonInfantPassengerCountProperty(): int
    {
        return count(array_filter($this->passengers, fn($p) => ($p['type'] ?? '') !== 'infant'));
    }

    public function getAtolTaxProperty(): float
    {
        if (!$this->flight_atol) return 0;
        $taxableCount = $this->nonInfantPassengerCount;
        return $taxableCount > 0 ? 2.50 * $taxableCount : 0;
    }

    public function getSafiTaxProperty(): float
    {
        if (!$this->flight_safi) return 0;
        $taxableCount = $this->nonInfantPassengerCount;
        return $taxableCount > 0 ? 2.50 * $taxableCount : 0;
    }

    public function getAtolSafiTaxProperty(): float
    {
        return $this->atolTax + $this->safiTax;
    }

    public function getFlightMarginProperty(): float
    {
        return $this->totalFlightSold - $this->totalFlightCost;
    }

    public function getTotalCostPriceProperty(): float
    {
        return $this->totalFlightCost
            + collect($this->hotels)->sum(fn($h) => (float) ($h['actual_cost'] ?? 0))
            + collect($this->visas)->sum(fn($v) => (float) ($v['actual_cost'] ?? 0))
            + $this->totalTransferCost
            + (float) ($this->excursion_actual_cost ?: 0);
    }

    /** Transfers are charged per leg — pickups and dropoffs both carry their own cost/sold. */
    public function getTotalTransferCostProperty(): float
    {
        return collect($this->transferPickups)->merge($this->transferDropoffs)
            ->sum(fn($t) => (float) ($t['actual_cost'] ?? 0));
    }

    public function getTotalTransferSoldProperty(): float
    {
        return collect($this->transferPickups)->merge($this->transferDropoffs)
            ->sum(fn($t) => (float) ($t['selling_price'] ?? 0));
    }

    public function getTotalSoldPriceProperty(): float
    {
        return $this->totalFlightSold
            + collect($this->hotels)->sum(fn($h) => (float) ($h['selling_price'] ?? 0))
            + collect($this->visas)->sum(fn($v) => (float) ($v['selling_price'] ?? 0))
            + $this->totalTransferSold
            + (float) ($this->excursion_selling_price ?: 0);
    }

    public function getGrossMarginProperty(): float
    {
        return $this->totalSoldPrice - $this->totalCostPrice;
    }

    public function getTotalMarginProperty(): float
    {
        return $this->totalSoldPrice - $this->totalCostPrice - (float) ($this->cc_charges ?: 0);
    }

    public function getHotelMarginProperty(): float
    {
        return collect($this->hotels)->sum(function ($h) {
            return (float) ($h['selling_price'] ?? 0) - (float) ($h['actual_cost'] ?? 0);
        });
    }

    /* ── Payment history ── */
    public function addPaymentHistory(): void
    {
        $idx = count($this->payment_history);
        // The draft entry was already being filled into index $idx via wire:model
        // Make sure it has at least a method or amount, otherwise don't commit
        $draft = $this->payment_history[$idx] ?? [];
        if (empty($draft['method']) && empty($draft['amount'])) return;

        // Ensure the entry exists and is properly formed
        if (!isset($this->payment_history[$idx])) {
            $this->payment_history[$idx] = ['date' => '', 'method' => '', 'amount' => '', 'receipt' => ''];
        }
        $this->payment_history = array_values($this->payment_history);

        if (!empty($draft['amount'])) {
            $this->logActivity(
                'Payment of £' . number_format((float)$draft['amount'], 2) . ' recorded',
                ($draft['method'] ?? '') . (!empty($draft['receipt']) ? ' · Receipt: ' . $draft['receipt'] : ''),
                'added'
            );
        }
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

        $this->logActivity('Note', $this->mandatory_comment, 'note');
        $this->mandatory_comment = '';
    }

    public function addReasonToEntry(int $index, string $reason): void
    {
        if (isset($this->activity_log_entries[$index]) && !empty(trim($reason))) {
            $this->activity_log_entries[$index]['detail'] = trim($reason);
        }
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
            'epay_debit'      => 'Epay Debit',
            'epay_credit'     => 'Epay Credit',
            'amex'            => 'AMEX',
            'klarna'          => 'Klarna',
            'superpay'        => 'SuperPay',
            'clearpay'        => 'ClearPay',
            'stripe'          => 'Stripe',
            'refund'          => 'Refund',
            'previous_booking'=> 'Previous Booking',
            'bank_transfer'   => 'Bank Transfer',
            'debit_card'      => 'Debit Card',
            'credit_card'     => 'Credit Card',
        ];
    }

    public function isCardPayment(): bool
    {
        return in_array($this->payment_method, ['amex', 'credit_card', 'debit_card']);
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
        // Final validation — the step validation on the payment step already runs,
        // but we guard here too in case save is reached via other paths.
        $this->validateStep(count($this->stepLabels));

        $booking = DB::transaction(function () {
            // Build activity log entry
            $activityEntry = [
                'agent' => Auth::user()->name,
                'timestamp' => now()->toDateTimeString(),
                'action' => 'created',
                'comment' => $this->mandatory_comment,
            ];

            $booking = Booking::create([
                'customer_id' => 1,
                'user_id' => Auth::id(),
                'booking_type' => $this->booking_type ?: 'flight',
                'lead_source' => $this->lead_source ?: null,
                'lead_nature' => $this->lead_nature ?: null,
                'is_returning_or_referral' => !empty($this->old_booking_reference),
                'old_booking_reference' => $this->old_booking_reference ?: null,
                'previous_booking_type' => $this->previous_booking_type ?: null,
                'last_payment_date' => $this->last_payment_date ?: null,
                'last_issue_date' => $this->last_issue_date ?: null,
                'booker_title' => $this->booker_title ?: null,
                'booker_first_name' => $this->booker_first_name,
                'booker_last_name' => $this->booker_last_name,
                'booker_mobile' => $this->booker_mobile,
                'booker_landline' => $this->booker_landline ?: null,
                'booker_email' => $this->booker_email ?: null,
                'booker_address' => $this->booker_address ?: null,
                'booker_postcode' => $this->booker_postcode ? strtoupper($this->booker_postcode) : null,
                'booker_country' => $this->booker_country ?: 'UK',
                'passenger_count' => count($this->passengers),
                'booking_status' => $this->booking_status,
                'issuance_requested_at' => $this->issuance_requested ? now() : null,
                'refund_requested_at' => $this->refund_queue ? now() : null,
                'notes' => $this->mandatory_comment,
                'activity_log' => json_encode([$activityEntry]),
                'excursion_data' => $this->booking_type === 'excursion' || $this->showExcursionStep ? [
                    'name'          => $this->excursion_name,
                    'destination'   => $this->excursion_destination,
                    'date'          => $this->excursion_date,
                    'duration'      => $this->excursion_duration,
                    'supplier'      => $this->excursion_supplier,
                    'meeting_point' => $this->excursion_meeting_point,
                    'reference'     => $this->excursion_reference,
                    'status'        => $this->excursion_status,
                    'actual_cost'   => $this->excursion_actual_cost,
                    'selling_price' => $this->excursion_selling_price,
                    'inclusions'    => $this->excursion_inclusions,
                    'notes'         => $this->excursion_notes,
                ] : null,
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
                    'contact_number' => $p['contact_number'] ?: null,
                    'cost_per_pax' => is_numeric($p['cost_per_pax'] ?? '') ? (float) $p['cost_per_pax'] : null,
                    'sold_per_pax' => is_numeric($p['sold_per_pax'] ?? '') ? (float) $p['sold_per_pax'] : null,
                    'frequent_flyer_number' => $p['frequent_flyer_number'] ?: null,
                    'e_ticket_number' => $p['e_ticket_number'] ?: null,
                    'ptc' => $ptc,
                    'passenger_status_label' => $p['passenger_status_label'] ?: null,
                ]);
            }

            // Flight detail + costs (one record per segment)
            $flightTypes = ['flight', 'umrah', 'holiday'];
            $hasFlightData = in_array($this->booking_type, $flightTypes) ||
                collect($this->flightSegments)->contains(fn($s) => $s['airline'] || $s['pnr'] || $s['departure_airport']);
            if ($hasFlightData) {
                foreach ($this->flightSegments as $seg) {
                    BookingFlightDetail::create([
                        'booking_id'         => $booking->id,
                        'pnr'                => $seg['pnr'] ?: null,
                        'folder_number'      => $this->flight_folder_number ?: null,
                        'locator'            => $seg['locator'] ?: null,
                        'airline_locator'    => $seg['airline_locator'] ?: null,
                        'type_issuer'        => $seg['type_issuer'] ?: null,
                        'reservation_status' => $seg['reservation_status'] ?: null,
                        'flight_type'        => $seg['flight_type'] ?? 'return',
                        'airline'            => $seg['airline'] ? strtoupper($seg['airline']) : null,
                        'vendor'             => $seg['vendor'] ?: null,
                        'gds'                => $seg['gds'] ?: null,
                        'cabin'              => $seg['cabin'] ?: null,
                        'ticket_issue_limit' => $seg['ticket_issue_limit'] ? preg_replace('/(\d{2}:\d{2}):\d{2}(:\d{2})+$/', '$1:00', $seg['ticket_issue_limit']) : null,
                        'atol'               => $this->flight_atol,
                        'safi'               => $this->flight_safi,
                        'departure_airport'  => $seg['departure_airport'] ?: null,
                        'arrival_airport'    => $seg['arrival_airport'] ?: null,
                        'departure_date'     => $seg['departure_date'] ?: null,
                        'return_date'        => $seg['return_date'] ?: null,
                        'selling_price'      => $this->flight_selling_price ?: 0,
                        'cost'               => is_numeric($seg['cost'] ?? '') ? (float) $seg['cost'] : null,
                        'sold'               => is_numeric($seg['sold'] ?? '') ? (float) $seg['sold'] : null,
                        'passenger_costs'    => !empty($seg['passenger_costs']) ? $seg['passenger_costs'] : null,
                    ]);
                }

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

            // Hotels (multi-hotel with rooms)
            foreach ($this->hotels as $hotel) {
                $bookingHotel = BookingHotel::create([
                    'booking_id' => $booking->id,
                    'hotel_name' => $hotel['hotel_name'] ?: 'Hotel',
                    'city' => $hotel['city'] ?: null,
                    'booking_status' => $hotel['status'] ?? 'confirmed',
                    'check_in' => $hotel['check_in'] ?: null,
                    'check_out' => $hotel['check_out'] ?: null,
                    'actual_cost' => $hotel['actual_cost'] ?: 0,
                    'selling_price' => $hotel['selling_price'] ?: 0,
                ]);
                foreach ($hotel['rooms'] ?? [] as $ri => $room) {
                    BookingHotelRoom::create([
                        'booking_hotel_id' => $bookingHotel->id,
                        'room_number' => $ri + 1,
                        'room_type' => $room['room_type'] ?: null,
                        'occupants' => $room['occupants'] ?? 1,
                        'meal_basis' => $room['meal_basis'] ?? 'room_only',
                    ]);
                }
            }

            // Transfers
            foreach ($this->transferPickups as $pickup) {
                if (!empty($pickup['location'])) {
                    BookingTransfer::create([
                        'booking_id'    => $booking->id,
                        'type'          => 'pickup',
                        'location'      => $pickup['location'],
                        'date_time'     => $pickup['date_time'] ?: null,
                        'flight_number' => $pickup['flight_number'] ?: null,
                        'route'         => $pickup['route'] ?: null,
                        'vehicle_type'  => $pickup['vehicle_type'] ?: null,
                        'supplier'      => $pickup['supplier'] ?: null,
                        'actual_cost'   => $pickup['actual_cost'] !== '' ? $pickup['actual_cost'] : null,
                        'selling_price' => $pickup['selling_price'] !== '' ? $pickup['selling_price'] : null,
                        'status'        => $pickup['status'] ?: 'confirmed',
                        'notes'         => $pickup['notes'] ?: null,
                    ]);
                }
            }
            foreach ($this->transferDropoffs as $dropoff) {
                if (!empty($dropoff['location'])) {
                    BookingTransfer::create([
                        'booking_id'    => $booking->id,
                        'type'          => 'dropoff',
                        'location'      => $dropoff['location'],
                        'date_time'     => $dropoff['date_time'] ?: null,
                        'flight_number' => $dropoff['flight_number'] ?: null,
                        'route'         => $dropoff['route'] ?: null,
                        'vehicle_type'  => $dropoff['vehicle_type'] ?: null,
                        'supplier'      => $dropoff['supplier'] ?: null,
                        'actual_cost'   => $dropoff['actual_cost'] !== '' ? $dropoff['actual_cost'] : null,
                        'selling_price' => $dropoff['selling_price'] !== '' ? $dropoff['selling_price'] : null,
                        'status'        => $dropoff['status'] ?: 'confirmed',
                        'notes'         => $dropoff['notes'] ?: null,
                    ]);
                }
            }

            // Visas (Umrah)
            foreach ($this->visas as $visa) {
                if (!empty($visa['passenger_name']) || !empty($visa['visa_reference'])) {
                    BookingVisa::create([
                        'booking_id'      => $booking->id,
                        'passenger_name'  => $visa['passenger_name'] ?: null,
                        'visa_type'       => $visa['visa_type'] ?? 'umrah',
                        'visa_reference'  => $visa['visa_reference'] ?: null,
                        'visa_number'     => $visa['visa_number'] ?: null,
                        'application_date'=> $visa['application_date'] ?: null,
                        'issue_date'      => $visa['issue_date'] ?: null,
                        'expiry_date'     => $visa['expiry_date'] ?: null,
                        'status'          => $visa['status'] ?? 'pending',
                        'actual_cost'     => is_numeric($visa['actual_cost'] ?? '') ? (float) $visa['actual_cost'] : 0,
                        'selling_price'   => is_numeric($visa['selling_price'] ?? '') ? (float) $visa['selling_price'] : 0,
                        'notes'           => $visa['notes'] ?: null,
                    ]);
                }
            }

            // Payment
            $total = $this->totalFlightSold + collect($this->hotels)->sum(fn($h) => (float) ($h['selling_price'] ?? 0))
                   + collect($this->visas)->sum(fn($v) => (float) ($v['selling_price'] ?? 0));
            $a = 0; $b = 0; $d = 0;
            if ($this->booking_plan === 'full')             { $a = (float)($this->amount_paid ?: 0); }
            elseif ($this->booking_plan === 'awaiting')     { $a = (float)($this->amount_paid ?: 0); $b = $total - $a; }
            elseif ($this->booking_plan === 'payment_plan') { $a = (float)($this->instalments[0]['amount'] ?? 0); $b = $total - $a; }
            elseif ($this->booking_plan === 'dnpl')         { $d = (float)($this->deposit_amount ?: 0); $b = $total; }

            $primaryMethod = $this->payment_method ?: ($this->instalments[0]['method'] ?? null);

            BookingPayment::create([
                'booking_id'     => $booking->id,
                'booking_plan'   => $this->booking_plan,
                'amount_paid'    => $a,
                'total_amount'   => $total,
                'balance_remaining' => $b,
                'due_date'       => $this->due_date ?: null,
                'installment_period' => $this->booking_plan === 'payment_plan' ? ($this->installment_period !== 'none' ? $this->installment_period : '30_days') : 'none',
                'installment_first_amount' => $this->instalments[0]['amount'] ?? null,
                'debit_card_change' => false,
                'deposit_amount' => $d,
                'is_deposit_nonrefundable' => $this->booking_plan === 'dnpl',
                'payment_mode'   => $primaryMethod ?: 'none',
                'payment_mode_2' => null,
                'cc_charges'     => $this->cc_charges ?: 0,
                'invoice_generated' => false,
            ]);

            // Auto-build payment history from form data
            $historyEntries = $this->booking_plan === 'payment_plan'
                ? $this->instalments
                : [[
                    'date'    => $this->payment_date ?: now()->toDateString(),
                    'method'  => $this->payment_method,
                    'amount'  => $this->booking_plan === 'dnpl' ? $this->deposit_amount : $this->amount_paid,
                    'receipt' => $this->receipt_number,
                ]];

            $cardRates = [
                'epay_debit'  => 1.5, 'epay_credit' => 2.5,
                'debit_card'  => 1.5, 'credit_card' => 2.5,
                'amex'        => 2.5,
            ];
            foreach ($historyEntries as $ph) {
                $amt = (float)($ph['amount'] ?? 0);
                if ($amt > 0) {
                    $method        = $ph['method'] ?? null;
                    $entryRate     = $method && isset($cardRates[$method]) ? $cardRates[$method] : null;
                    $entryCcCharge = $entryRate ? round($amt * $entryRate / 100, 2) : 0;
                    $paymentDetails = $entryCcCharge > 0
                        ? ['cc_charge' => $entryCcCharge, 'cc_charge_rate' => $entryRate]
                        : [];

                    BookingPaymentHistory::create([
                        'booking_id'      => $booking->id,
                        'user_id'         => Auth::id(),
                        'payment_date'    => !empty($ph['date']) ? \Carbon\Carbon::parse($ph['date'])->toDateString() : now()->toDateString(),
                        'payment_method'  => $method,
                        'amount'          => $amt,
                        'receipt_number'  => $ph['receipt'] ?: null,
                        'payment_details' => $paymentDetails ?: null,
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

        AuditLogger::log(Auth::user(), $booking, 'created', 'Booking created', null, $booking->toArray());
        session()->flash('success', "Booking #{$booking->booking_number} created successfully.");
        return redirect()->route('bookings.show', $booking);
    }

    public function render(): View
    {
        return view('livewire.create-booking', [
            'countries' => $this->countries(),
            'paymentMethods' => $this->paymentMethodOptions(),
            'paymentMethodSelectOptions' => collect($this->paymentMethodOptions())
                ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->toArray(),
            'vendorOptions' => Setting::getValue('vendor_options', [
                ['value' => 'Direct',        'label' => 'Direct (Airline)'],
                ['value' => 'Dnata',         'label' => 'Dnata'],
                ['value' => 'Midlands Air',  'label' => 'Midlands Air'],
                ['value' => 'HFG',           'label' => 'HFG'],
                ['value' => 'Global Travel', 'label' => 'Global Travel'],
                ['value' => 'Jac Travel',    'label' => 'Jac Travel'],
                ['value' => 'Portman',       'label' => 'Portman'],
                ['value' => 'Hays Travel',   'label' => 'Hays Travel'],
                ['value' => 'Trailfinders',  'label' => 'Trailfinders'],
                ['value' => 'Other',         'label' => 'Other'],
            ]),
            'gdsOptions' => array_merge(
                [['value' => '', 'label' => 'GDS']],
                Setting::getValue('gds_options', [
                    ['value' => 'AMADEUS',    'label' => 'Amadeus'],
                    ['value' => 'GALILEO',    'label' => 'Galileo'],
                    ['value' => 'SABRE',      'label' => 'Sabre'],
                    ['value' => 'WORLDSPAN',  'label' => 'Worldspan'],
                    ['value' => 'APOLLO',     'label' => 'Apollo'],
                    ['value' => 'TRAVELPORT', 'label' => 'Travelport'],
                ])
            ),
        ]);
    }
}
