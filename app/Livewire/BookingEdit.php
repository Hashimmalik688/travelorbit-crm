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
use App\Models\BookingTransfer;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookingEdit extends Component
{
    use WithFileUploads;

    public Booking $booking;

    // Booking Info
    public $lead_source = '';
    public $lead_nature = '';
    public $is_returning_or_referral = false;
    public $old_booking_reference = '';
    public $last_payment_date = '';
    public $last_issue_date = '';
    public $referral_name = '';
    public $booking_type = '';

    // Caller
    public $booker_title = null;
    public $booker_first_name = '';
    public $booker_last_name = '';
    public $booker_mobile = '';
    public $booker_landline = '';
    public $booker_email = '';
    public $booker_address = '';
    public $booker_postcode = '';
    public $booker_country = 'UK';

    // Passengers
    public $adultCount = 0;
    public $gbeCount = 0;
    public $childCount = 0;
    public $infantCount = 0;
    public $passengers = [];

    // Flight
    public $flight_pnr = '';
    public $flight_folder_number = '';
    public $flight_locator = '';
    public $flight_airline_locator = '';
    public $flight_type_issuer = '';
    public $flight_type = 'return';
    public $flight_reservation_status = '';
    public $flight_airline = '';
    public $flight_vendor = '';
    public $flight_gds = '';
    public $flight_cabin = '';
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
        'adult' => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'child' => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'gbe'   => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'infant'=> ['cost' => '', 'qty' => 0, 'sold' => ''],
    ];

    // Hotel (multi-hotel with rooms)
    public $hotels = [];
    public $hotelCount = 0;

    // Transfers
    public $transferPickups = [];
    public $transferDropoffs = [];

    // Payment
    public $booking_plan = '';
    public $payment_mode = '';
    public $payment_mode_2 = '';
    public $amount_paid = '';
    public $due_date = '';
    public $installment_period = 'none';
    public $installment_first_amount = '';
    public $debit_card_change = false;
    public $deposit_amount = '';
    public $cc_charges = '';

    // Documents
    public $newDocuments = [];
    public $newDocumentTypes = [];
    public $existingDocuments = [];

    // Comment / Activity
    public $newComment = '';
    public $existingComments = [];
    public $mandatory_comment = '';
    public array $activity_log_entries = [];
    public ?int $reasonEditIndex = null;
    public string $reasonEditText = '';

    // Status
    public $booking_status = 'pending';
    public $issuance_requested = false;
    public $refund_queue = false;

    public function mount(Booking $booking)
    {
        $this->booking = $booking->load([
            'passengers', 'payment', 'documents', 'flightDetail', 'flightCosts', 'hotels.rooms', 'transfers',
            'comments' => function ($q) { $q->with('user')->orderBy('created_at'); },
            'activityLogs' => function ($q) { $q->with('user')->orderBy('created_at', 'desc'); },
        ]);

        $this->lead_source = $booking->lead_source ?? '';
        $this->lead_nature = $booking->lead_nature ?? '';
        $this->is_returning_or_referral = $booking->is_returning_or_referral ?? false;
        $this->old_booking_reference = $booking->old_booking_reference ?? '';
        $this->last_payment_date = $booking->last_payment_date ? $booking->last_payment_date->format('Y-m-d') : '';
        $this->last_issue_date = $booking->last_issue_date ? $booking->last_issue_date->format('Y-m-d') : '';
        $this->referral_name = $booking->referral_name ?? '';
        $this->booking_type = $booking->booking_type ?? '';

        $this->booker_title = $booking->booker_title;
        $this->booker_first_name = $booking->booker_first_name ?? '';
        $this->booker_last_name = $booking->booker_last_name ?? '';
        $this->booker_mobile = $booking->booker_mobile ?? '';
        $this->booker_landline = $booking->booker_landline ?? '';
        $this->booker_email = $booking->booker_email ?? '';
        $this->booker_address = $booking->booker_address ?? '';
        $this->booker_postcode = $booking->booker_postcode ?? '';
        $this->booker_country = $booking->booker_country ?? 'UK';

        foreach ($booking->passengers as $pax) {
            $type = $pax->passenger_type ?? 'adult';
            $this->{$type . 'Count'}++;
            $this->passengers[] = [
                'id' => $pax->id,
                'type' => $type,
                'title' => $pax->title ?? '',
                'first_name' => $pax->first_name ?? '',
                'last_name' => $pax->last_name ?? '',
                'passport_number' => $pax->passport_number ?? '',
                'passport_country_code' => $pax->passport_country_code ?? '',
                'passport_issuing_country' => $pax->passport_issuing_country ?? '',
                'national_id_number' => $pax->national_id_number ?? '',
                'nationality' => $pax->nationality ?? '',
                'date_of_birth' => $pax->date_of_birth ? $pax->date_of_birth->format('Y-m-d') : '',
                'frequent_flyer_number' => $pax->frequent_flyer_number ?? '',
                'e_ticket_number' => $pax->e_ticket_number ?? '',
                'ptc' => $pax->ptc ?? '',
                'passenger_status_label' => $pax->passenger_status_label ?? '',
            ];
        }

        if ($booking->flightDetail) {
            $fd = $booking->flightDetail;
            $this->flight_pnr = $fd->pnr ?? '';
            $this->flight_folder_number = $fd->folder_number ?? '';
            $this->flight_locator = $fd->locator ?? '';
            $this->flight_airline_locator = $fd->airline_locator ?? '';
            $this->flight_type_issuer = $fd->type_issuer ?? '';
            $this->flight_type = $fd->flight_type ?? 'return';
            $this->flight_reservation_status = $fd->reservation_status ?? '';
            $this->flight_airline = $fd->airline ?? '';
            $this->flight_vendor = $fd->vendor ?? '';
            $this->flight_gds = $fd->gds ?? '';
            $this->flight_cabin = $fd->cabin ?? '';
            $this->flight_ticket_issue_limit = $fd->ticket_issue_limit ? $fd->ticket_issue_limit->format('Y-m-d\TH:i') : '';
            $this->flight_atol = $fd->atol;
            $this->flight_safi = $fd->safi;
            $this->flight_city_code = $fd->city_code ?? '';
            $this->flight_departure_airport = $fd->departure_airport ?? '';
            $this->flight_arrival_airport = $fd->arrival_airport ?? '';
            $this->flight_departure_date = $fd->departure_date ? $fd->departure_date->format('Y-m-d') : '';
            $this->flight_return_date = $fd->return_date ? $fd->return_date->format('Y-m-d') : '';
            $this->flight_selling_price = $fd->selling_price ?? '';

            foreach ($booking->flightCosts as $c) {
                $this->flight_costs[$c->cost_type] = ['cost' => (string) $c->cost, 'qty' => $c->quantity, 'sold' => (string) ($c->sold_price ?? 0)];
            }
        }

        foreach ($booking->hotels as $hotel) {
            $rooms = [];
            foreach ($hotel->rooms as $room) {
                $rooms[] = [
                    'room_type' => $room->room_type ?? '',
                    'occupants' => $room->occupants ?? 1,
                    'meal_basis' => $room->meal_basis ?? 'room_only',
                ];
            }
            $this->hotels[] = [
                'hotel_id' => $hotel->id,
                'hotel_name' => $hotel->hotel_name,
                'city' => $hotel->city ?? '',
                'status' => $hotel->booking_status,
                'check_in' => $hotel->check_in ? $hotel->check_in->format('Y-m-d') : '',
                'check_out' => $hotel->check_out ? $hotel->check_out->format('Y-m-d') : '',
                'actual_cost' => $hotel->actual_cost ?? '',
                'selling_price' => $hotel->selling_price ?? '',
                'number_of_rooms' => count($rooms),
                'rooms' => $rooms,
            ];
        }
        $this->hotelCount = count($this->hotels);

        // Transfers
        foreach ($booking->transfers as $t) {
            $entry = [
                'location' => $t->location,
                'date_time' => $t->date_time ? $t->date_time->format('Y-m-d\TH:i') : '',
                'flight_number' => $t->flight_number ?? '',
                'route' => $t->route ?? '',
            ];
            if ($t->type === 'pickup') {
                $this->transferPickups[] = $entry;
            } else {
                $this->transferDropoffs[] = $entry;
            }
        }

        if ($booking->payment) {
            $this->booking_plan = $booking->payment->booking_plan ?? '';
            $this->payment_mode = $booking->payment->payment_mode ?? '';
            $this->payment_mode_2 = $booking->payment->payment_mode_2 ?? '';
            $this->amount_paid = $booking->payment->amount_paid ?? '';
            $this->due_date = $booking->payment->due_date ? $booking->payment->due_date->format('Y-m-d') : '';
            $this->installment_period = $booking->payment->installment_period ?? 'none';
            $this->installment_first_amount = $booking->payment->installment_first_amount ?? '';
            $this->debit_card_change = $booking->payment->debit_card_change ?? false;
            $this->deposit_amount = $booking->payment->deposit_amount ?? '';
            $this->cc_charges = $booking->payment->cc_charges ?? '';
        }

        $this->existingDocuments = $booking->documents->map(fn ($d) => ['id' => $d->id, 'file_name' => $d->file_name, 'document_type' => $d->document_type, 'file_path' => $d->file_path])->toArray();
        $this->existingComments = $booking->comments->toArray();
        $this->booking_status = $booking->booking_status ?? 'pending';
        $this->issuance_requested = (bool) $booking->issuance_requested_at;
        $this->refund_queue = (bool) $booking->refund_requested_at;

        // Load existing activity log from JSON field
        $jsonLog = $booking->activity_log ?? [];
        if (is_string($jsonLog)) $jsonLog = json_decode($jsonLog, true) ?? [];
        $this->activity_log_entries = $jsonLog;

        // Log that edit was opened
        $this->logActivity('Opened booking for editing', 'Booking #'.$booking->booking_number, 'navigated');
    }

    // ── Activity log helpers (same as CreateBooking) ─────────────────
    private function logActivity(string $action, string $detail = '', string $type = 'info'): void
    {
        $this->activity_log_entries[] = [
            'agent'     => Auth::user()->name ?? 'System',
            'timestamp' => now()->format('d M Y, g:i A'),
            'action'    => $action,
            'detail'    => $detail,
            'type'      => $type,
        ];
        // Persist merged log back to booking JSON field
        $existing = $this->booking->activity_log ?? [];
        if (is_string($existing)) $existing = json_decode($existing, true) ?? [];
        $existing[] = end($this->activity_log_entries);
        $this->booking->updateQuietly(['activity_log' => $existing]);
    }

    public function addActivityEntry(): void
    {
        if (empty(trim($this->mandatory_comment))) return;
        $this->logActivity('Note', $this->mandatory_comment, 'note');
        $this->mandatory_comment = '';
    }

    public function openReasonEdit(int $index): void
    {
        $this->reasonEditIndex = $index;
        $this->reasonEditText  = '';
    }

    public function saveReasonEdit(): void
    {
        if ($this->reasonEditIndex !== null && !empty(trim($this->reasonEditText))) {
            $this->activity_log_entries[$this->reasonEditIndex]['detail'] = trim($this->reasonEditText);
        }
        $this->reasonEditIndex = null;
        $this->reasonEditText  = '';
    }

    public function cancelReasonEdit(): void { $this->reasonEditIndex = null; $this->reasonEditText = ''; }

    // ── Field change auto-logging ─────────────────────────────────────
    public function updatedLeadSource(): void    { $this->logActivity('Lead source changed to '.($this->lead_source ?: 'none'), '', 'updated'); }
    public function updatedLeadNature(): void    { $this->logActivity('Lead nature changed to '.($this->lead_nature ?: 'none'), '', 'updated'); }
    public function updatedBookingType(): void   { $this->logActivity('Booking type changed to '.($this->booking_type ?: 'none'), '', 'updated'); }
    public function updatedPaymentType(): void   { $this->logActivity('Payment type changed to '.($this->booking_plan ?: 'none'), '', 'updated'); }
    public function updatedBookingStatus(): void { $this->logActivity('Status changed to '.$this->booking_status, '', 'updated'); }
    public function updatedFlightAtol(): void    { $this->logActivity('ATOL '.($this->flight_atol?'enabled':'disabled'), '', $this->flight_atol?'added':'removed'); }
    public function updatedFlightSafi(): void    { $this->logActivity('SAFI '.($this->flight_safi?'enabled':'disabled'), '', $this->flight_safi?'added':'removed'); }

    public function updatedAdultCount(): void  { $this->reconcilePassengers(); $this->logActivity('Adult passenger count changed to '.$this->adultCount, '', $this->adultCount>0?'added':'removed'); }
    public function updatedGbeCount(): void    { $this->reconcilePassengers(); $this->logActivity('Youth passenger count changed to '.$this->gbeCount, '', $this->gbeCount>0?'added':'removed'); }
    public function updatedChildCount(): void  { $this->reconcilePassengers(); $this->logActivity('Child passenger count changed to '.$this->childCount, '', $this->childCount>0?'added':'removed'); }
    public function updatedInfantCount(): void { $this->reconcilePassengers(); $this->logActivity('Infant passenger count changed to '.$this->infantCount, '', $this->infantCount>0?'added':'removed'); }

    public function inc(string $type): void { $prop = $type.'Count'; $this->$prop = ($this->$prop ?? 0) + 1; $this->reconcilePassengers(); $labels=['adult'=>'Adult','gbe'=>'Youth','child'=>'Child','infant'=>'Infant']; $this->logActivity('Added '.($labels[$type]??$type).' passenger', '', 'added'); }
    public function dec(string $type): void { $prop = $type.'Count'; $persisted=count(array_filter($this->passengers,fn($p)=>!empty($p['id'])&&$p['type']===$type)); $before=(int)$this->$prop; $this->$prop=max($persisted,$before-1); $this->reconcilePassengers(); if($before>0&&$before>$persisted){$labels=['adult'=>'Adult','gbe'=>'Youth','child'=>'Child','infant'=>'Infant'];$this->logActivity('Removed '.($labels[$type]??$type).' passenger','','removed');} }

    public function reconcilePassengers(): void
    {
        $persistedByType = [];
        foreach ($this->passengers as $p) {
            if (!empty($p['id'])) {
                $t = $p['type'];
                $persistedByType[$t] = ($persistedByType[$t] ?? 0) + 1;
            }
        }
        $desired = [];
        foreach (['adult' => 'adultCount', 'gbe' => 'gbeCount', 'child' => 'childCount', 'infant' => 'infantCount'] as $type => $prop) {
            $count = max(0, (int) $this->$prop);
            $min = $persistedByType[$type] ?? 0;
            if ($count < $min) {
                $count = $min;
                $this->$prop = $count;
            }
            for ($i = 0; $i < $count; $i++) { $desired[] = ['type' => $type]; }
        }
        $current = $this->passengers;
        while (count($current) > count($desired)) { array_pop($current); }
        $result = [];
        foreach ($desired as $d) {
            $found = false;
            foreach ($current as $i => $c) {
                if ($c !== null && $c['type'] === $d['type']) { $result[] = $c; $current[$i] = null; $found = true; break; }
            }
            if (!$found) {
                $result[] = ['id' => null, 'type' => $d['type'], 'title' => '', 'first_name' => '', 'last_name' => '', 'passport_number' => '', 'passport_country_code' => '', 'passport_issuing_country' => '', 'national_id_number' => '', 'nationality' => '', 'date_of_birth' => '', 'contact_number' => '', 'ticket_number' => '', 'passenger_status_label' => ''];
            }
        }
        $this->passengers = $result;
    }

    public function updatedPassengers($value, $key): void
    {
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

    public function passengerTypeLabel(string $type): string { return ['adult' => 'Adult', 'gbe' => 'GBE', 'child' => 'Child', 'infant' => 'Infant'][$type] ?? ucfirst($type); }

    public function getTotalPassengersProperty(): int { return count($this->passengers); }

    public function getTotalFlightCostProperty(): float
    {
        $total = 0;
        foreach (['adult', 'child', 'infant'] as $type) {
            $total += (float) ($this->flight_costs[$type]['cost'] ?? 0) * (int) ($this->flight_costs[$type]['qty'] ?? 0);
        }
        return $total;
    }

    public function getFlightMarginProperty(): float { return (float) ($this->flight_selling_price ?: 0) - $this->totalFlightCost; }

    public function countries(): array
    {
        return ['AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AD' => 'Andorra', 'AO' => 'Angola', 'AG' => 'Antigua', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia', 'BW' => 'Botswana', 'BR' => 'Brazil', 'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'SZ' => 'Eswatini', 'ET' => 'Ethiopia', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GR' => 'Greece', 'GD' => 'Grenada', 'GT' => 'Guatemala', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HN' => 'Honduras', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KP' => 'North Korea', 'KR' => 'South Korea', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'MX' => 'Mexico', 'FM' => 'Micronesia', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'MK' => 'North Macedonia', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestine', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PL' => 'Poland', 'PT' => 'Portugal', 'QA' => 'Qatar', 'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'KN' => 'St Kitts', 'LC' => 'St Lucia', 'VC' => 'St Vincent', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'SS' => 'South Sudan', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TO' => 'Tonga', 'TT' => 'Trinidad', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'UAE', 'GB' => 'United Kingdom', 'US' => 'United States', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe'];
    }

    public function addDocument(): void { $this->newDocuments[] = null; $this->newDocumentTypes[] = ''; }
    public function removeDocument(int $i): void { unset($this->newDocuments[$i]); unset($this->newDocumentTypes[$i]); $this->newDocuments = array_values($this->newDocuments); $this->newDocumentTypes = array_values($this->newDocumentTypes); }
    public function deleteExistingDocument(int $id): void { $d = BookingDocument::find($id); if ($d) { Storage::disk('public')->delete($d->file_path); $d->delete(); $this->existingDocuments = array_values(array_filter($this->existingDocuments, fn ($x) => ($x['id'] ?? null) !== $id)); } }

    public function addHotel(): void
    {
        $this->hotels[] = [
            'hotel_id' => null,
            'hotel_name' => '',
            'city' => '',
            'status' => 'confirmed',
            'check_in' => '',
            'check_out' => '',
            'actual_cost' => '',
            'selling_price' => '',
            'number_of_rooms' => 1,
            'rooms' => [
                ['room_type' => '', 'occupants' => 1, 'meal_basis' => 'room_only'],
            ],
        ];
        $this->hotelCount = count($this->hotels);
    }

    public function removeHotel(int $index): void
    {
        if (count($this->hotels) > 0) {
            unset($this->hotels[$index]);
            $this->hotels = array_values($this->hotels);
            $this->hotelCount = count($this->hotels);
        }
    }

    public function updatedHotels($value, $key): void
    {
        if (preg_match('/^hotels\.(\d+)\.number_of_rooms$/', $key, $m)) {
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

    public function addPickup(): void
    {
        $this->transferPickups[] = ['location' => '', 'date_time' => '', 'flight_number' => '', 'route' => ''];
    }

    public function removePickup(int $index): void
    {
        unset($this->transferPickups[$index]);
        $this->transferPickups = array_values($this->transferPickups);
    }

    public function addDropoff(): void
    {
        $this->transferDropoffs[] = ['location' => '', 'date_time' => '', 'flight_number' => '', 'route' => ''];
    }

    public function removeDropoff(int $index): void
    {
        unset($this->transferDropoffs[$index]);
        $this->transferDropoffs = array_values($this->transferDropoffs);
    }

    public function save()
    {
        if (empty($this->passengers)) { session()->flash('error', 'Add at least one passenger.'); return; }
        if (empty(trim($this->mandatory_comment))) {
            $this->addError('mandatory_comment', 'A reason/comment is required before saving.');
            return;
        }

        $this->validate([
            'lead_source'              => 'required',
            'lead_nature'              => 'required',
            'booking_type'             => 'required',
            'last_payment_date'        => 'required|date',
            'last_issue_date'          => 'required|date',
            'booker_title'             => 'required',
            'booker_first_name'        => 'required|string|max:255',
            'booker_last_name'         => 'required|string|max:255',
            'booker_mobile'            => 'required|string|max:255|regex:/^[0-9]+$/',
            'booker_landline'          => 'required|string|max:255',
            'booker_email'             => 'required|email|max:255',
            'booker_address'           => 'required|string',
            'booker_postcode'          => 'required|string|max:20',
            'passengers'               => 'required|array|min:1',
            'passengers.*.title'       => 'required',
            'passengers.*.first_name'  => 'required|string|max:255',
            'passengers.*.last_name'   => 'required|string|max:255',
            'passengers.*.date_of_birth'   => 'required|date',
            'passengers.*.passport_number' => 'required|string|max:255',
            'passengers.*.contact_number'  => 'required|string|max:255|regex:/^[0-9]+$/',
            'booking_plan'             => 'required',
            'booking_status'           => 'required|in:pending,payment_charge_request,issuance_queue,ticket_in_process,invoiced,confirmed,cancelled,refund_queue,issued,issued_payment_awaiting,issued_payment_plan',
            'mandatory_comment'        => 'required|string|min:3',
        ]);

        $oldBooking = $this->booking->replicate();
        DB::transaction(function () use ($oldBooking) {
            $statusChanged = $this->booking->booking_status !== $this->booking_status;

            // Append to activity log JSON
            $activityLog = $this->booking->activity_log ?? [];
            $activityLog[] = [
                'agent' => Auth::user()->name,
                'timestamp' => now()->toDateTimeString(),
                'action' => 'updated',
                'comment' => $this->mandatory_comment,
            ];

            $this->booking->update([
                'booking_type' => $this->booking_type,
                'lead_source' => $this->lead_source,
                'lead_nature' => $this->lead_nature ?: null,
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
                'activity_log' => $activityLog,
            ]);

            // Activity log record
            BookingActivityLog::create([
                'booking_id' => $this->booking->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'comment' => $this->mandatory_comment,
            ]);

            // Mandatory comment
            BookingComment::create([
                'booking_id' => $this->booking->id,
                'user_id' => Auth::id(),
                'comment' => $this->mandatory_comment,
                'action' => 'updated',
                'is_mandatory' => true,
            ]);

            $updatedIds = [];
            foreach ($this->passengers as $p) {
                $data = [
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
                    'passenger_status_label' => $p['passenger_status_label'] ?: null,
                ];
                if (!empty($p['id'])) {
                    BookingPassenger::where('id', $p['id'])->update($data);
                    $updatedIds[] = $p['id'];
                } else {
                    $data['booking_id'] = $this->booking->id;
                    $np = BookingPassenger::create($data);
                    $updatedIds[] = $np->id;
                }
            }
            BookingPassenger::where('booking_id', $this->booking->id)->whereNotIn('id', $updatedIds)->delete();

            // Flight detail - only Manager/Admin can save flight changes
            $canEditFlightHotel = in_array(Auth::user()->role, ['admin', 'manager', 'operations', 'issuance']);
            if ($canEditFlightHotel && ($this->flight_airline || $this->flight_pnr || $this->flight_selling_price)) {
                $fdData = [
                    'pnr' => $this->flight_pnr ?: null,
                    'folder_number' => $this->flight_folder_number ?: null,
                    'locator' => $this->flight_locator ?: null,
                    'airline_locator' => $this->flight_airline_locator ?: null,
                    'type_issuer' => $this->flight_type_issuer ?: null,
                    'reservation_status' => $this->flight_reservation_status ?: null,
                    'flight_type' => $this->flight_type,
                    'airline' => $this->flight_airline ? strtoupper($this->flight_airline) : null,
                    'vendor' => $this->flight_vendor ?: null,
                    'gds' => $this->flight_gds ?: null,
                    'cabin' => $this->flight_cabin ?: null,
                    'ticket_issue_limit' => $this->flight_ticket_issue_limit ?: null,
                    'atol' => $this->flight_atol,
                    'safi' => $this->flight_safi,
                    'city_code' => $this->flight_city_code ?: null,
                    'departure_airport' => $this->flight_departure_airport ?: null,
                    'arrival_airport' => $this->flight_arrival_airport ?: null,
                    'departure_date' => $this->flight_departure_date ?: null,
                    'return_date' => $this->flight_return_date ?: null,
                    'selling_price' => $this->flight_selling_price ?: 0,
                ];
                if ($this->booking->flightDetail) {
                    $this->booking->flightDetail->update($fdData);
                } else {
                    $fdData['booking_id'] = $this->booking->id;
                    BookingFlightDetail::create($fdData);
                }
                BookingFlightCost::where('booking_id', $this->booking->id)->delete();
                foreach (['adult', 'child', 'gbe', 'infant'] as $type) {
                    $qty = (int) ($this->flight_costs[$type]['qty'] ?? 0);
                    if ($qty > 0) {
                        BookingFlightCost::create([
                            'booking_id' => $this->booking->id,
                            'cost_type' => $type,
                            'cost' => $this->flight_costs[$type]['cost'] ?: 0,
                            'quantity' => $qty,
                            'sold_price' => $this->flight_costs[$type]['sold'] ?: 0,
                        ]);
                    }
                }
            }

            // Hotels - only Manager/Admin
            if ($canEditFlightHotel) {
                $keptHotelIds = [];
                foreach ($this->hotels as $hotel) {
                    $hotelData = [
                        'hotel_name' => $hotel['hotel_name'] ?: 'Hotel',
                        'city' => $hotel['city'] ?: null,
                        'booking_status' => $hotel['status'] ?? 'confirmed',
                        'check_in' => $hotel['check_in'] ?: null,
                        'check_out' => $hotel['check_out'] ?: null,
                        'actual_cost' => $hotel['actual_cost'] ?: 0,
                        'selling_price' => $hotel['selling_price'] ?: 0,
                    ];
                    if (!empty($hotel['hotel_id'])) {
                        BookingHotel::where('id', $hotel['hotel_id'])->update($hotelData);
                        $bookingHotel = BookingHotel::find($hotel['hotel_id']);
                        $keptHotelIds[] = $bookingHotel->id;
                    } else {
                        $hotelData['booking_id'] = $this->booking->id;
                        $bookingHotel = BookingHotel::create($hotelData);
                        $keptHotelIds[] = $bookingHotel->id;
                    }
                    // Sync rooms
                    $bookingHotel->rooms()->delete();
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
                BookingHotel::where('booking_id', $this->booking->id)->whereNotIn('id', $keptHotelIds)->delete();
            }

            // Transfers - only Manager/Admin
            if ($canEditFlightHotel) {
                $this->booking->transfers()->delete();
                foreach ($this->transferPickups as $pickup) {
                    if (!empty($pickup['location'])) {
                        BookingTransfer::create([
                            'booking_id' => $this->booking->id,
                            'type' => 'pickup',
                            'location' => $pickup['location'],
                            'date_time' => $pickup['date_time'] ?: null,
                            'flight_number' => $pickup['flight_number'] ?: null,
                            'route' => $pickup['route'] ?: null,
                        ]);
                    }
                }
                foreach ($this->transferDropoffs as $dropoff) {
                    if (!empty($dropoff['location'])) {
                        BookingTransfer::create([
                            'booking_id' => $this->booking->id,
                            'type' => 'dropoff',
                            'location' => $dropoff['location'],
                            'date_time' => $dropoff['date_time'] ?: null,
                            'flight_number' => $dropoff['flight_number'] ?: null,
                            'route' => $dropoff['route'] ?: null,
                        ]);
                    }
                }
            }

            // Payment
            $hotelTotalSold = collect($this->hotels)->sum(fn($h) => (float) ($h['selling_price'] ?? 0));
            $total = (float) ($this->flight_selling_price ?: 0) + $hotelTotalSold;
            $b = 0; $a = 0; $d = 0;
            if ($this->booking_plan === 'full') { $a = $total; }
            elseif ($this->booking_plan === 'awaiting') { $a = $this->amount_paid ?: 0; $b = $total - $a; }
            elseif ($this->booking_plan === 'payment_plan') { $a = $this->amount_paid ?: 0; $b = $total - $a; }
            elseif ($this->booking_plan === 'dnpl') { $d = $this->deposit_amount ?: 0; $b = $total; }

            if ($this->booking->payment) {
                $this->booking->payment->update([
                    'booking_plan' => $this->booking_plan,
                    'amount_paid' => $a,
                    'total_amount' => $total,
                    'balance_remaining' => $b,
                    'due_date' => $this->due_date ?: null,
                    'installment_period' => $this->booking_plan === 'payment_plan' ? $this->installment_period : 'none',
                    'installment_first_amount' => $this->installment_first_amount ?: null,
                    'debit_card_change' => $this->debit_card_change,
                    'deposit_amount' => $d,
                    'is_deposit_nonrefundable' => $this->booking_plan === 'dnpl',
                    'payment_mode' => $this->payment_mode,
                    'payment_mode_2' => $this->payment_mode_2 ?: null,
                    'cc_charges' => $this->cc_charges ?: 0,
                ]);
            }

            // Documents
            if (!empty($this->newDocuments)) {
                foreach ($this->newDocuments as $i => $f) {
                    if ($f) {
                        $path = $f->store('booking-documents', 'public');
                        BookingDocument::create(['booking_id' => $this->booking->id, 'uploaded_by' => Auth::id(), 'file_name' => $f->getClientOriginalName(), 'file_path' => $path, 'file_type' => $f->getClientMimeType(), 'document_type' => $this->newDocumentTypes[$i] ?? 'other']);
                    }
                }
            }

            if ($this->newComment) { BookingComment::create(['booking_id' => $this->booking->id, 'user_id' => Auth::id(), 'comment' => $this->newComment]); }

            if ($statusChanged) { AuditLogger::log(Auth::user(), $this->booking, 'status_changed', "Status changed to {$this->booking_status}", ['booking_status' => $oldBooking->booking_status], ['booking_status' => $this->booking_status]); }
        });

        $this->newDocuments = []; $this->newDocumentTypes = []; $this->newComment = ''; $this->mandatory_comment = '';
        session()->flash('success', "Booking #{$this->booking->booking_number} updated successfully.");
        return redirect()->route('bookings.show', $this->booking);
    }

    public function render()
    {
        return view('livewire.booking-edit', [
            'countries' => $this->countries(),
            'vendorOptions' => \App\Models\Setting::getValue('vendor_options', [
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
        ]);
    }
}
