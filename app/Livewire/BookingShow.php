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
use App\Models\BookingMarginShare;
use App\Models\BookingPaymentHistory;
use App\Models\BookingTransfer;
use App\Models\Refund;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BookingShow extends Component
{
    use WithFileUploads;

    public Booking $booking;
    public $newComment = '';
    public $commentPreset = '';   // key from BookingComment::PRESETS, '' = plain comment
    public $bookingStatus;

    // Refund modal
    public $showRefundModal = false;
    public $refundAmount = '';
    public $refundReason = '';
    public $refundMethod = 'bank_transfer';

    public array $activityLog = [];

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
    public $flightSegments = [];
    public $flight_folder_number = '';
    public $flight_atol = false;
    public $flight_safi = false;
    public $flight_selling_price = '';
    public $flight_costs = [
        'adult' => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'child' => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'gbe'   => ['cost' => '', 'qty' => 0, 'sold' => ''],
        'infant' => ['cost' => '', 'qty' => 0, 'sold' => ''],
    ];

    // Hotel
    public $hotels = [];

    // Visas
    public array $visas = [];

    // Excursion
    public bool $showExcursion = false;
    public $excursion_name = '';
    public $excursion_destination = '';
    public $excursion_date = '';
    public $excursion_supplier = '';
    public $excursion_actual_cost = '';
    public $excursion_selling_price = '';
    public $excursion_notes = '';
    public $excursion_status = 'confirmed';

    // Transfers
    public $transferPickups = [];
    public $transferDropoffs = [];

    // Payment
    public $payment_method = '';
    public $booking_plan = '';
    public $amount_paid = '';
    public $due_date = '';
    public $installment_count = 2;
    public $payment_history = [];
    public $payment_date = '';
    public $receipt_number = '';
    public $deposit_amount = '';
    public $payment_mode = '';
    public $payment_mode_2 = '';
    public $installment_period = 'none';
    public $installment_first_amount = '';
    public $debit_card_change = false;
    public $cc_charges = '';
    public $selected_payment_method = '';
    public $payment_instalments = [];

    // Charge popup
    public $showChargeModal = false;
    public $chargeAmount = '';
    public $chargeMethod = '';
    public $chargeReceipt = '';
    public $chargeCcRate = '';
    public $chargeCcAmount = '';

    // Card payment fields
    public $card_number = '';
    public $card_expiry = '';
    public $card_cvv = '';
    public $card_holder_name = '';

    // Instalment payment tracking
    public $instalment_paid = [];
    public $flightSectionEditing = false;
    public $paymentSectionEditing = false;
    public $paymentReviewed = false;
    public $billing_address = '';
    public $billing_postcode = '';

    // Documents
    public $newDocuments = [];
    public $newDocumentTypes = [];

    // Document preview
    public $previewDoc = null;

    // Cached static data (loaded once at mount, not on every render)
    public array $cachedVendorOptions = [];
    public array $cachedGdsOptions = [];

    // Activity
    public $mandatory_comment = '';
    public array $activity_log_entries = [];
    public bool $activityLogDirty = false;
    public ?int $reasonEditIndex = null;
    public string $reasonEditText = '';

    // Margin sharing
    public bool $shareMarginOpen = false;
    public $shareUserId = '';
    public $shareAmount = '';
    public $shareNote = '';

    public function mount(Booking $booking)
    {
        $this->booking = $booking->load([
            'passengers', 'payment', 'paymentHistory', 'documents',
            'flightDetail', 'flightDetails', 'flightCosts', 'hotels.rooms', 'transfers', 'visas',
            'marginShares.sharedWith', 'marginShares.sharedBy',
            'comments' => fn($q) => $q->with('user')->orderBy('created_at'),
        ]);
        $this->bookingStatus = $booking->booking_status;
        $this->activityLog = $this->buildActivityLog($booking);
        $this->cachedVendorOptions = \App\Models\Setting::getValue('vendor_options', []);
        $this->cachedGdsOptions = \App\Models\Setting::getValue('gds_options', [
            ['value' => 'AMADEUS',    'label' => 'Amadeus'],
            ['value' => 'GALILEO',    'label' => 'Galileo'],
            ['value' => 'SABRE',      'label' => 'Sabre'],
            ['value' => 'WORLDSPAN',  'label' => 'Worldspan'],
            ['value' => 'APOLLO',     'label' => 'Apollo'],
            ['value' => 'TRAVELPORT', 'label' => 'Travelport'],
        ]);
        $this->loadBookingData();

        // If status is payment_charge_request but no pending charge requests exist, revert to pending
        if ($booking->booking_status === 'payment_charge_request') {
            $hasPending = $booking->paymentHistory->contains(fn($h) => $h->status === 'pending');
            if (!$hasPending) {
                $booking->update(['booking_status' => 'pending']);
                $this->bookingStatus = 'pending';
            }
        }

        // Always log "Opened Booking" — every view is tracked
        $this->logActivity('Opened Booking', 'Booking opened for viewing', 'navigated', bypassViewerCheck: true);
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

    public function getActivityColorConfig(string $action, string $type = 'info'): array
    {
        // Full-row highlight for major lifecycle events
        if (stripos($action, 'created a new booking') !== false) {
            return ['dot'=>'#0EA5E9','bg'=>'rgba(14,165,233,.10)','border'=>'#0EA5E9','label'=>'Created','full_row'=>true,'icon'=>'ph-sparkle'];
        }
        if (stripos($action, 'opened booking') !== false) {
            return ['dot'=>'#64748B','bg'=>'rgba(100,116,139,.06)','border'=>'#94A3B8','label'=>'View','full_row'=>false,'icon'=>'ph-eye'];
        }
        if (stripos($action, 'request issuance') !== false || stripos($action, 'issuance queue') !== false || stripos($action, 'moved to issuance') !== false) {
            return ['dot'=>'#7C3AED','bg'=>'rgba(124,58,237,.12)','border'=>'#7C3AED','label'=>'Issuance','full_row'=>true,'icon'=>'ph-ticket'];
        }
        if (stripos($action, 'ticket in process') !== false || stripos($action, 'processing ticket') !== false) {
            return ['dot'=>'#0369A1','bg'=>'rgba(3,105,161,.11)','border'=>'#0369A1','label'=>'Processing','full_row'=>true,'icon'=>'ph-airplane-takeoff'];
        }
        if (stripos($action, 'issued') !== false || stripos($action, 'ticket issued') !== false) {
            return ['dot'=>'#047857','bg'=>'rgba(4,120,87,.11)','border'=>'#047857','label'=>'Issued','full_row'=>true,'icon'=>'ph-check-fat'];
        }
        if (stripos($action, 'request payment charge') !== false || stripos($action, 'payment charge') !== false) {
            return ['dot'=>'#FF6B35','bg'=>'rgba(255,107,53,.11)','border'=>'#FF6B35','label'=>'Payment Req.','full_row'=>true,'icon'=>'ph-credit-card'];
        }
        if (stripos($action, 'payment approved') !== false || stripos($action, 'payment received') !== false) {
            return ['dot'=>'#16A34A','bg'=>'rgba(22,163,74,.11)','border'=>'#16A34A','label'=>'Paid','full_row'=>true,'icon'=>'ph-check-circle'];
        }
        if (stripos($action, 'payment declined') !== false || stripos($action, 'rejected') !== false || stripos($action, 'payment rejected') !== false) {
            return ['dot'=>'#DC2626','bg'=>'rgba(220,38,38,.10)','border'=>'#DC2626','label'=>'Declined','full_row'=>true,'icon'=>'ph-x-circle'];
        }
        if (stripos($action, 'refund') !== false || stripos($action, 'refund queue') !== false) {
            return ['dot'=>'#DC2626','bg'=>'rgba(220,38,38,.10)','border'=>'#DC2626','label'=>'Refund','full_row'=>true,'icon'=>'ph-arrows-counter-clockwise'];
        }
        if (stripos($action, 'cancelled') !== false || stripos($action, 'cancel') !== false) {
            return ['dot'=>'#DC2626','bg'=>'rgba(220,38,38,.08)','border'=>'#DC2626','label'=>'Cancelled','full_row'=>true,'icon'=>'ph-x-circle'];
        }
        // Subtle highlights for field edits and notes
        if (stripos($action, 'added a note') !== false || $type === 'note') {
            return ['dot'=>'#64748B','bg'=>'rgba(100,116,139,.06)','border'=>'#94A3B8','label'=>'Note','full_row'=>false,'icon'=>'ph-note'];
        }
        if (stripos($action, 'approved') !== false || stripos($action, 'received') !== false || stripos($action, 'paid') !== false) {
            return ['dot'=>'#16A34A','bg'=>'rgba(22,163,74,.06)','border'=>'#16A34A','label'=>'Payment','full_row'=>false,'icon'=>'ph-money'];
        }
        if (stripos($action, 'requested') !== false || stripos($action, 'charged') !== false) {
            return ['dot'=>'#F59E0B','bg'=>'rgba(245,158,11,.06)','border'=>'#F59E0B','label'=>'Charge','full_row'=>false,'icon'=>'ph-lightning'];
        }
        if ($type === 'added' || stripos($action, 'created') !== false) {
            return ['dot'=>'#0EA5E9','bg'=>'rgba(14,165,233,.05)','border'=>'#0EA5E9','label'=>'Added','full_row'=>false,'icon'=>'ph-plus-circle'];
        }
        if (stripos($action, 'ticket') !== false || stripos($action, 'document') !== false || stripos($action, 'voucher') !== false) {
            return ['dot'=>'#7C3AED','bg'=>'rgba(124,58,237,.05)','border'=>'#7C3AED','label'=>'Docs','full_row'=>false,'icon'=>'ph-file-text'];
        }
        if (stripos($action, 'hotel') !== false || stripos($action, 'transfer') !== false) {
            return ['dot'=>'#0891B2','bg'=>'rgba(8,145,178,.05)','border'=>'#0891B2','label'=>'Service','full_row'=>false,'icon'=>'ph-buildings'];
        }
        if ($type === 'update' || stripos($action, 'edited') !== false) {
            return ['dot'=>'#94A3B8','bg'=>'rgba(148,163,184,.04)','border'=>'#CBD5E1','label'=>'Edit','full_row'=>false,'icon'=>'ph-pencil-simple'];
        }
        return ['dot'=>'#94A3B8','bg'=>'transparent','border'=>'rgba(148,163,184,.3)','label'=>'','full_row'=>false,'icon'=>'ph-circle'];
    }

    protected function buildActivityLog(Booking $booking): array
    {
        $entries = [];

        $jsonLog = $booking->activity_log ?? [];
        if (is_string($jsonLog)) $jsonLog = json_decode($jsonLog, true) ?? [];

        foreach ($jsonLog as $eIdx => $e) {
            $rawAction = $e['action'] ?? '';
            $comment   = $e['comment'] ?? $e['detail'] ?? '';
            $action = match ($rawAction) {
                'created'          => 'created a new booking',
                'comment_added'    => 'added a note',
                'Note'             => 'added a note',
                'updated'          => 'saved booking changes',
                'payment_requested'=> 'Request Payment Charge',
                'payment_approved' => 'Payment Approved',
                'payment_rejected' => 'Payment Declined',
                'status_changed'   => match(true) {
                    str_contains($comment, 'Issuance Queue')                                                       => 'Request Issuance',
                    str_contains($comment, 'Ticket In Process') || str_contains($comment, 'Ticket in Process')     => 'Ticket In Process',
                    str_contains($comment, 'Issued')                                                               => 'Ticket Issued',
                    str_contains($comment, 'Refund')                                                               => 'Refund Requested',
                    str_contains($comment, 'Cancelled')                                                            => 'Booking Cancelled',
                    default                                                                                        => 'Status Changed',
                },
                default => $rawAction ? ucfirst(str_replace('_', ' ', $rawAction)) : '',
            };
            $type      = $e['type'] ?? 'info';
            $agentName = $e['agent'] ?? 'System';

            // Use the snapshotted avatar/initials stored at log time — never look up the current user record
            $avatarUrl      = $e['avatar_url'] ?? null;
            $avatarInitials = $e['avatar_initials'] ?? null;
            if (!$avatarInitials) {
                $avatarInitials = strtoupper(substr($agentName, 0, 1));
                if (($sp = strpos($agentName, ' ')) !== false) $avatarInitials .= strtoupper(substr($agentName, $sp + 1, 1));
            }

            $entries[] = [
                'agent'           => $agentName,
                'avatar_url'      => $avatarUrl,
                'avatar_initials' => $avatarInitials,
                'action'          => $action,
                'detail'          => $comment,
                'reasons'         => $e['reasons'] ?? (isset($e['reason']) ? [['text'=>$e['reason'],'agent'=>null,'at'=>null]] : []),
                'type'            => $type,
                'json_index'      => $eIdx,
                'ts_raw'          => $e['timestamp'] ?? '',
                'timestamp'       => $e['timestamp'] ? \Carbon\Carbon::parse($e['timestamp'])->format('d M Y, H:i') : null,
                'ts_sort'         => strtotime($e['timestamp'] ?? '0'),
                'color'           => $this->getActivityColorConfig($action, $type),
                'is_json'         => true,
            ];
        }

        foreach ($booking->comments as $c) {
            $rawAction = $c->action ?? 'note';
            $cComment  = $c->comment ?? '';
            $action    = match ($rawAction) {
                'created'          => 'created a new booking',
                'comment_added'    => 'added a note',
                'note'             => 'added a note',
                'Note'             => 'added a note',
                'updated'          => 'saved booking changes',
                'payment_requested'=> 'Request Payment Charge',
                'status_changed'   => match(true) {
                    str_contains($cComment, 'Issuance Queue')   => 'Request Issuance',
                    str_contains($cComment, 'Ticket In Process') => 'Ticket In Process',
                    str_contains($cComment, 'Issued')            => 'Ticket Issued',
                    str_contains($cComment, 'Refund')            => 'Refund Requested',
                    str_contains($cComment, 'Cancelled')         => 'Booking Cancelled',
                    default                                      => 'Status Changed',
                },
                default => ucfirst(str_replace('_', ' ', $rawAction)),
            };
            $typeMap = ['created'=>'added','comment_added'=>'note','status_changed'=>'update','payment_requested'=>'update'];
            $type    = $typeMap[$rawAction] ?? 'info';

            // Use stored snapshot — never look up current user record for old log entries
            $agentName      = $c->agent_name ?? ($c->user?->name ?? 'System');
            $avatarUrl      = $c->avatar_url ?? null;
            $avatarInitials = null;
            if (!$avatarInitials) {
                $avatarInitials = strtoupper(substr($agentName, 0, 1));
                if (($sp = strpos($agentName, ' ')) !== false) $avatarInitials .= strtoupper(substr($agentName, $sp + 1, 1));
            }

            $entries[] = [
                'agent'           => $agentName,
                'avatar_url'      => $avatarUrl,
                'avatar_initials' => $avatarInitials,
                'action'          => $action,
                'detail'          => $cComment,
                'reason'          => null,
                'type'            => $type,
                'ts_raw'          => '',
                'timestamp'       => $c->created_at?->format('d M Y, H:i'),
                'ts_sort'         => $c->created_at?->timestamp ?? 0,
                'color'           => $this->getActivityColorConfig($action, $type),
                'preset'          => BookingComment::PRESETS[$c->preset] ?? null,
                'is_json'         => false,
            ];
        }

        usort($entries, fn($a, $b) => $a['ts_sort'] <=> $b['ts_sort']);
        return $entries;
    }

    private function loadBookingData(): void
    {
        $b = $this->booking;

        $this->lead_source = $b->lead_source ?? '';
        $this->lead_nature = $b->lead_nature ?? '';
        $this->is_returning_or_referral = $b->is_returning_or_referral ?? false;
        $this->old_booking_reference = $b->old_booking_reference ?? '';
        $this->last_payment_date = $b->last_payment_date ? $b->last_payment_date->format('Y-m-d') : '';
        $this->last_issue_date = $b->last_issue_date ? $b->last_issue_date->format('Y-m-d') : '';
        $this->referral_name = $b->referral_name ?? '';
        $this->booking_type = $b->booking_type ?? '';

        $this->booker_title = $b->booker_title;
        $this->booker_first_name = $b->booker_first_name ?? '';
        $this->booker_last_name = $b->booker_last_name ?? '';
        $this->booker_mobile = $b->booker_mobile ?? '';
        $this->booker_landline = $b->booker_landline ?? '';
        $this->booker_email = $b->booker_email ?? '';
        $this->booker_address = $b->booker_address ?? '';
        $this->booker_postcode = $b->booker_postcode ?? '';
        $this->booker_country = $b->booker_country ?? 'UK';

        $this->passengers = [];
        $this->adultCount = 0;
        $this->gbeCount = 0;
        $this->childCount = 0;
        $this->infantCount = 0;
        foreach ($b->passengers as $pax) {
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
                'contact_number' => $pax->contact_number ?? '',
                'cost_per_pax' => $pax->cost_per_pax ?? '',
                'sold_per_pax' => $pax->sold_per_pax ?? '',
                'frequent_flyer_number' => $pax->frequent_flyer_number ?? '',
                'e_ticket_number' => $pax->e_ticket_number ?? '',
                'ptc' => $pax->ptc ?? '',
                'passenger_status_label' => $pax->passenger_status_label ?? '',
            ];
        }

        $this->flightSegments = [];
        $allSegments = $b->flightDetails ?? collect();
        if ($allSegments->isNotEmpty()) {
            $firstFd = $allSegments->first();
            $this->flight_folder_number = $firstFd->folder_number ?? '';
            $this->flight_atol = $firstFd->atol;
            $this->flight_safi = $firstFd->safi;
            $this->flight_selling_price = $firstFd->selling_price ?? '';
            foreach ($allSegments as $fd) {
                $this->flightSegments[] = [
                    'pnr' => $fd->pnr ?? '',
                    'locator' => $fd->locator ?? '',
                    'airline_locator' => $fd->airline_locator ?? '',
                    'type_issuer' => $fd->type_issuer ?? '',
                    'reservation_status' => $fd->reservation_status ?? '',
                    'flight_type' => $fd->flight_type ?? 'return',
                    'airline' => $fd->airline ?? '',
                    'vendor' => $fd->vendor ?? '',
                    'gds' => $fd->gds ?? '',
                    'cabin' => $fd->cabin ?? '',
                    'ticket_issue_limit' => $fd->ticket_issue_limit ? $fd->ticket_issue_limit->format('Y-m-d\TH:i') : '',
                    'cost' => $fd->cost ?? '',
                    'sold' => $fd->sold ?? '',
                    'passenger_costs' => $fd->passenger_costs ?? [],
                    'departure_airport' => $fd->departure_airport ?? '',
                    'arrival_airport' => $fd->arrival_airport ?? '',
                    'departure_date' => $fd->departure_date ? $fd->departure_date->format('Y-m-d') : '',
                    'return_date' => $fd->return_date ? $fd->return_date->format('Y-m-d') : '',
                ];
            }
        } else {
            $this->flightSegments = [[
                'pnr' => '', 'locator' => '', 'airline_locator' => '', 'type_issuer' => '',
                'reservation_status' => '', 'flight_type' => 'return', 'airline' => '', 'vendor' => '', 'gds' => '',
                'cabin' => '', 'ticket_issue_limit' => '', 'cost' => '', 'sold' => '', 'passenger_costs' => [],
                'departure_airport' => '',
                'arrival_airport' => '', 'departure_date' => '', 'return_date' => '',
            ]];
        }

        foreach ($b->flightCosts as $c) {
            $this->flight_costs[$c->cost_type] = [
                'cost' => (string)$c->cost,
                'qty' => $c->quantity,
                'sold' => (string)($c->sold_price ?? 0),
            ];
        }

        $this->hotels = [];
        foreach ($b->hotels as $hotel) {
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
                'check_in' => $hotel->check_in?->format('Y-m-d') ?? '',
                'check_out' => $hotel->check_out?->format('Y-m-d') ?? '',
                'actual_cost' => $hotel->actual_cost ?? '',
                'selling_price' => $hotel->selling_price ?? '',
                'number_of_rooms' => count($rooms),
                'rooms' => $rooms,
            ];
        }

        // Visas
        $this->visas = [];
        foreach ($b->visas as $visa) {
            $this->visas[] = [
                'id'               => $visa->id,
                'passenger_name'   => $visa->passenger_name ?? '',
                'visa_type'        => $visa->visa_type ?? 'umrah',
                'visa_reference'   => $visa->visa_reference ?? '',
                'visa_number'      => $visa->visa_number ?? '',
                'application_date' => $visa->application_date?->format('Y-m-d') ?? '',
                'issue_date'       => $visa->issue_date?->format('Y-m-d') ?? '',
                'expiry_date'      => $visa->expiry_date?->format('Y-m-d') ?? '',
                'status'           => $visa->status ?? 'pending',
                'actual_cost'      => (string) $visa->actual_cost,
                'selling_price'    => (string) $visa->selling_price,
                'notes'            => $visa->notes ?? '',
            ];
        }

        // Excursion
        $exc = $b->excursion_data ?? [];
        $this->excursion_name         = $exc['name'] ?? '';
        $this->excursion_destination  = $exc['destination'] ?? '';
        $this->excursion_date         = $exc['date'] ?? '';
        $this->excursion_supplier     = $exc['supplier'] ?? '';
        $this->excursion_actual_cost  = isset($exc['actual_cost']) ? (string)$exc['actual_cost'] : '';
        $this->excursion_selling_price= isset($exc['selling_price']) ? (string)$exc['selling_price'] : '';
        $this->excursion_notes        = $exc['notes'] ?? '';
        $this->excursion_status       = $exc['status'] ?? 'confirmed';
        $this->showExcursion          = !empty($this->excursion_name) || ($this->excursion_actual_cost !== '' && $this->excursion_actual_cost > 0);

        $this->transferPickups = [];
        $this->transferDropoffs = [];
        foreach ($b->transfers as $t) {
            $entry = [
                'id'            => $t->id,
                'location'      => $t->location,
                'date_time'     => $t->date_time?->format('Y-m-d H:i') ?? '',
                'flight_number' => $t->flight_number ?? '',
                'route'         => $t->route ?? '',
                'vehicle_type'  => $t->vehicle_type ?? '',
                'supplier'      => $t->supplier ?? '',
                'actual_cost'   => $t->actual_cost !== null ? (string)$t->actual_cost : '',
                'selling_price' => $t->selling_price !== null ? (string)$t->selling_price : '',
                'status'        => $t->status ?? 'confirmed',
                'notes'         => $t->notes ?? '',
            ];
            if ($t->type === 'pickup') $this->transferPickups[] = $entry;
            else $this->transferDropoffs[] = $entry;
        }

        if ($b->payment) {
            $this->booking_plan = $b->payment->booking_plan ?? '';
            $this->selected_payment_method = $b->payment->booking_plan ?? '';
            $this->payment_mode = $b->payment->payment_mode ?? '';
            $this->payment_mode_2 = $b->payment->payment_mode_2 ?? '';
            $this->amount_paid = $b->payment->amount_paid ?? '';
            $this->due_date = $b->payment->due_date ? $b->payment->due_date->format('Y-m-d') : '';
            $this->installment_period = $b->payment->installment_period ?? 'none';
            $this->installment_first_amount = $b->payment->installment_first_amount ?? '';
            $this->debit_card_change = $b->payment->debit_card_change ?? false;
            $this->deposit_amount = $b->payment->deposit_amount ?? '';
            $this->payment_method = $b->payment->payment_mode ?? '';

            $saved = $b->payment->payment_instalments;
            if ($saved && is_array($saved)) {
                $instData = isset($saved['instalments']) ? $saved['instalments'] : $saved;
                $this->payment_instalments = array_map(fn($inst) => array_merge(['amount' => '', 'date' => ''], (array) $inst), $instData);
                $this->instalment_paid = $saved['paid'] ?? [];
            } elseif (empty($this->payment_instalments)) {
                $this->payment_instalments = [['amount' => '', 'date' => '']];
            }
        }

        // Calculate total CC charges from payment history records
        // Each charge request stores its CC fee in payment_details['cc_charge']
        $this->refreshCcChargesFromHistory();

        $this->paymentReviewed = (bool)($b->payment_reviewed ?? false);

        $jsonLog = $b->activity_log ?? [];
        if (is_string($jsonLog)) $jsonLog = json_decode($jsonLog, true) ?? [];
        $this->activity_log_entries = $jsonLog;
    }

    // ── Passenger type labels ──────────────────────────────────────────
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
            if (($this->passengers[$i]['type'] ?? '') === $type) $count++;
        }
        return $count;
    }

    public function computePtc($dob, $passengerType): string
    {
        if ($passengerType === 'gbe') return 'GBE';
        if (!$dob) return '';
        $dobDate = \Carbon\Carbon::parse($dob);
        $years = $dobDate->diffInYears(now());
        return match (true) {
            $years >= 16 => 'ADT',
            $years >= 12 => 'GBE',
            $years >= 2  => 'CNN',
            default      => 'INF',
        };
    }

    public function computeAgeInfo(string $dob): array
    {
        if (!$dob) return ['years' => 0, 'months' => 0, 'days' => 0, 'current_type' => 'infant', 'next_ptc' => null, 'next_ptc_date' => null];
        $dobDate = \Carbon\Carbon::parse($dob);
        $today = now();
        $totalDays = $dobDate->diffInDays($today);
        $years = (int)floor($totalDays / 365);
        $remainingDays = $totalDays - ($years * 365);
        $months = (int)floor($remainingDays / 30);
        $days = round($remainingDays - ($months * 30));
        $currentType = $years >= 16 ? 'adult' : ($years >= 12 ? 'youth' : ($years >= 2 ? 'child' : 'infant'));
        $nextPtc = null;
        $nextPtcDate = null;
        if ($currentType === 'infant') { $nextPtc = 'Child (CNN)'; $nextPtcDate = $dobDate->copy()->addYears(2)->format('d M Y'); }
        elseif ($currentType === 'child') { $nextPtc = 'Youth (GBE)'; $nextPtcDate = $dobDate->copy()->addYears(12)->format('d M Y'); }
        elseif ($currentType === 'youth') { $nextPtc = 'Adult (ADT)'; $nextPtcDate = $dobDate->copy()->addYears(16)->format('d M Y'); }
        return ['years' => $years, 'months' => $months, 'days' => $days, 'current_type' => $currentType, 'next_ptc' => $nextPtc, 'next_ptc_date' => $nextPtcDate];
    }

    public function getTotalPassengersProperty(): int { return count($this->passengers); }

    public function getNonInfantPassengerCountProperty(): int
    {
        return count(array_filter($this->passengers, fn($p) => ($p['type'] ?? '') !== 'infant'));
    }

    // Flight pricing computed
    public function getTotalFlightCostProperty(): float
    {
        $total = 0;
        foreach ($this->flightSegments as $seg) {
            foreach ($seg['passenger_costs'] ?? [] as $pc) {
                $total += (float)($pc['cost'] ?? 0);
            }
        }
        return $total;
    }

    public function getTotalFlightSoldProperty(): float
    {
        $total = 0;
        foreach ($this->flightSegments as $seg) {
            foreach ($seg['passenger_costs'] ?? [] as $pc) {
                $total += (float)($pc['sold'] ?? 0);
            }
        }
        return $total;
    }

    public function getAtolTaxProperty(): float
    {
        if (!$this->flight_atol) return 0;
        $tc = $this->nonInfantPassengerCount;
        return $tc > 0 ? 2.50 * $tc : 0;
    }

    public function getSafiTaxProperty(): float
    {
        if (!$this->flight_safi) return 0;
        $tc = $this->nonInfantPassengerCount;
        return $tc > 0 ? 2.50 * $tc : 0;
    }

    public function getAtolSafiTaxProperty(): float { return $this->atolTax + $this->safiTax; }

    public function getFlightMarginProperty(): float { return $this->totalFlightSold - $this->totalFlightCost; }

    public function getTotalCostPriceProperty(): float
    {
        return $this->totalFlightCost
            + $this->atolSafiTax
            + collect($this->hotels)->sum(fn($h) => (float)($h['actual_cost'] ?? 0))
            + collect($this->visas)->sum(fn($v) => (float)($v['actual_cost'] ?? 0))
            + $this->totalTransferCost
            + (float)($this->excursion_actual_cost ?: 0);
    }

    /** Transfers are charged per leg — pickups and dropoffs both carry their own cost/sold. */
    public function getTotalTransferCostProperty(): float
    {
        return collect($this->transferPickups)->merge($this->transferDropoffs)
            ->sum(fn($t) => (float)($t['actual_cost'] ?? 0));
    }

    public function getTotalTransferSoldProperty(): float
    {
        return collect($this->transferPickups)->merge($this->transferDropoffs)
            ->sum(fn($t) => (float)($t['selling_price'] ?? 0));
    }

    public function getTotalSoldPriceProperty(): float
    {
        return $this->totalFlightSold
            + collect($this->hotels)->sum(fn($h) => (float)($h['selling_price'] ?? 0))
            + collect($this->visas)->sum(fn($v) => (float)($v['selling_price'] ?? 0))
            + $this->totalTransferSold
            + (float)($this->excursion_selling_price ?: 0);
    }

    public function getGrossMarginProperty(): float
    {
        return $this->totalSoldPrice - $this->totalCostPrice;
    }

    public function getTotalMarginProperty(): float
    {
        return $this->totalSoldPrice - $this->totalCostPrice - (float)($this->cc_charges ?: 0);
    }

    public function getHotelMarginProperty(): float
    {
        return collect($this->hotels)->sum(fn($h) => (float)($h['selling_price'] ?? 0) - (float)($h['actual_cost'] ?? 0));
    }

    public function getAgentMonthlyNumberProperty(): int
    {
        $b = $this->booking;
        return \App\Models\Booking::withTrashed()
            ->where('user_id', $b->user_id)
            ->whereYear('created_at', $b->created_at->year)
            ->whereMonth('created_at', $b->created_at->month)
            ->where('booking_number', '<=', $b->booking_number)
            ->count();
    }

    public function getIsLockedProperty(): bool
    {
        return $this->booking->booking_status !== 'pending';
    }

    public function getCanEditProperty(): bool
    {
        return !$this->isLocked && Auth::user()->can('update', $this->booking);
    }

    public function getIsViewerOnlyProperty(): bool
    {
        return Auth::id() !== $this->booking->user_id && !Auth::user()->hasPermission('bookings.edit_any');
    }

    private function abortIfViewer(): void
    {
        if ($this->isViewerOnly) {
            abort(403, 'You do not have permission to modify this booking.');
        }
    }

    public function getTotalPaidProperty(): float
    {
        return (float) $this->booking->paymentHistory()
            ->where('status', 'approved')
            ->sum('amount');
    }

    public function getRunningBalanceProperty(): float
    {
        return $this->totalSoldPrice - $this->totalPaid;
    }

    // ── Passenger management ───────────────────────────────────────────
    public function updatedAdultCount() { $this->reconcilePassengers(); }
    public function updatedGbeCount() { $this->reconcilePassengers(); }
    public function updatedChildCount() { $this->reconcilePassengers(); }
    public function updatedInfantCount() { $this->reconcilePassengers(); }

    public function inc(string $type): void
    {
        $this->abortIfViewer();
        $this->suppressLogging = true;
        $prop = $type . 'Count';
        $this->$prop = ($this->$prop ?? 0) + 1;
        $this->reconcilePassengers();
        $this->suppressLogging = false;
        $typeLabel = ['adult'=>'Adult','gbe'=>'Youth','child'=>'Child','infant'=>'Infant'][$type] ?? $type;
        $this->logActivity("Added Passenger", "{$typeLabel} — now {$this->$prop}", 'update');
    }

    public function dec(string $type): void
    {
        $this->abortIfViewer();
        $this->suppressLogging = true;
        $prop = $type . 'Count';
        $persisted = count(array_filter($this->passengers, fn($p) => !empty($p['id']) && $p['type'] === $type));
        $before = (int)$this->$prop;
        $this->$prop = max($persisted, $before - 1);
        $this->reconcilePassengers();
        $this->suppressLogging = false;
        if ($this->$prop < $before) {
            $typeLabel = ['adult'=>'Adult','gbe'=>'Youth','child'=>'Child','infant'=>'Infant'][$type] ?? $type;
            $this->logActivity("Removed Passenger", "{$typeLabel} — now {$this->$prop}", 'update');
        }
    }

    public function reconcilePassengers(): void
    {
        $this->suppressLogging = true;
        $persistedByType = [];
        foreach ($this->passengers as $p) {
            if (!empty($p['id'])) {
                $t = $p['type'];
                $persistedByType[$t] = ($persistedByType[$t] ?? 0) + 1;
            }
        }

        $desired = [];
        foreach (['adult' => 'adultCount', 'gbe' => 'gbeCount', 'child' => 'childCount', 'infant' => 'infantCount'] as $type => $prop) {
            $count = max(0, (int)$this->$prop);
            $min = $persistedByType[$type] ?? 0;
            if ($count < $min) {
                $count = $min;
                $this->$prop = $count;
            }
            for ($i = 0; $i < $count; $i++) $desired[] = ['type' => $type];
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
                    'id' => null, 'type' => $d['type'], 'title' => '', 'first_name' => '', 'last_name' => '',
                    'passport_number' => '', 'passport_country_code' => '', 'passport_issuing_country' => '',
                    'national_id_number' => '', 'nationality' => '', 'date_of_birth' => '',
                    'contact_number' => '', 'cost_per_pax' => '', 'sold_per_pax' => '',
                    'frequent_flyer_number' => '', 'e_ticket_number' => '',
                    'ptc' => '', 'passenger_status_label' => '',
                ];
            }
        }
        $this->passengers = $result;
        $this->suppressLogging = false;
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

    public function togglePaymentSectionEditing(): void
    {
        $this->abortIfViewer();
        if ($this->paymentSectionEditing) {
            $this->paymentSectionEditing = false;
            $this->savePaymentStructure();
        } else {
            $this->paymentSectionEditing = true;
        }
    }

    public function savePaymentStructure(): void
    {
        $instalments = array_values(array_filter($this->payment_instalments, fn($i) => $i['amount'] !== '' || $i['date'] !== ''));
        BookingPayment::updateOrCreate(
            ['booking_id' => $this->booking->id],
            [
                'booking_plan'        => $this->selected_payment_method ?: null,
                'payment_instalments' => count($instalments) ? ['instalments' => $instalments, 'paid' => $this->instalment_paid] : null,
            ]
        );
        $this->booking->refresh();
    }

    public function updatedSelectedPaymentMethod(string $value): void
    {
        $this->abortIfViewer();
        // Only reset when the plan type actually differs from what's saved
        $saved = $this->booking->payment?->booking_plan;
        if ($value !== $saved) {
            $this->payment_instalments = [['amount' => '', 'date' => '']];
            $this->instalment_paid = [false];
        }
    }

    public function addPaymentInstalment(): void
    {
        $this->abortIfViewer();
        $this->payment_instalments[] = ['amount' => '', 'date' => ''];
        $this->instalment_paid[] = false;
    }

    public function removePaymentInstalment(): void
    {
        $this->abortIfViewer();
        if (count($this->payment_instalments) > 1) {
            array_pop($this->payment_instalments);
            array_pop($this->instalment_paid);
        }
    }

    public function togglePaymentReviewed(): void
    {
        if (!Auth::user()->hasPermission('accounts.access')) return;
        $this->paymentReviewed = !$this->paymentReviewed;
        $this->booking->update(['payment_reviewed' => $this->paymentReviewed]);
    }

    public function toggleFlightSectionEditing(): void
    {
        $this->abortIfViewer();
        if ($this->flightSectionEditing) {
            $this->flightSectionEditing = false;
            $this->autoSave();
        } else {
            $this->flightSectionEditing = true;
        }
    }

    // ── Flight segments ────────────────────────────────────────────────
    public function addFlightSegment(): void
    {
        $this->abortIfViewer();
        $this->suppressLogging = true;
        $this->flightSegments[] = [
            'pnr' => '', 'locator' => '', 'airline_locator' => '', 'type_issuer' => '',
            'reservation_status' => '', 'flight_type' => 'return', 'airline' => '', 'vendor' => '', 'gds' => '',
            'cabin' => '', 'ticket_issue_limit' => '', 'cost' => '', 'sold' => '',
            'departure_airport' => '',
            'arrival_airport' => '', 'departure_date' => '', 'return_date' => '',
        ];
        $this->suppressLogging = false;
        $this->logActivity('Added PNR segment', 'PNR #'.count($this->flightSegments).' added', 'update');
    }

    public function removeFlightSegment(int $index): void
    {
        $this->abortIfViewer();
        if (count($this->flightSegments) > 1) {
            $label = 'PNR #'.($index+1);
            unset($this->flightSegments[$index]);
            $this->flightSegments = array_values($this->flightSegments);
            $this->logActivity('Removed PNR segment', $label.' removed', 'update');
        }
    }

    public function updatedFlightSegments($value, $key): void
    {
        // When cost or sold changes for one passenger, sync to all passengers of the same type
        if (!preg_match('/^(\d+)\.passenger_costs\.(\d+)\.(cost|sold)$/', $key, $m)) {
            return;
        }
        $si    = (int) $m[1];
        $pi    = (int) $m[2];
        $field = $m[3];
        $type  = $this->passengers[$pi]['type'] ?? null;
        if (!$type) return;

        foreach ($this->passengers as $otherPi => $pax) {
            if ($otherPi === $pi) continue;
            if (($pax['type'] ?? '') !== $type) continue;
            $this->flightSegments[$si]['passenger_costs'][$otherPi][$field] = $value;
        }
    }

    // ── Hotels ─────────────────────────────────────────────────────────
    public function addHotel(): void
    {
        $this->suppressLogging = true;
        $this->hotels[] = [
            'hotel_id' => null, 'hotel_name' => '', 'city' => '', 'status' => 'confirmed',
            'check_in' => '', 'check_out' => '', 'actual_cost' => '', 'selling_price' => '',
            'number_of_rooms' => 1,
            'rooms' => [['room_type' => '', 'occupants' => 1, 'meal_basis' => 'room_only']],
        ];
        $this->suppressLogging = false;
        $this->logActivity('Added Hotel', 'Hotel #'.count($this->hotels).' added', 'update');
    }

    public function removeHotel(int $index): void
    {
        $this->abortIfViewer();
        if (count($this->hotels) > 0) {
            $name = $this->hotels[$index]['hotel_name'] ?: 'Hotel #'.($index+1);
            unset($this->hotels[$index]);
            $this->hotels = array_values($this->hotels);
            $this->logActivity('Removed Hotel', $name.' removed', 'update');
        }
    }

    public function updatedHotels($value, $key): void
    {
        if (preg_match('/^hotels\.(\d+)\.number_of_rooms$/', $key, $m)) {
            $idx = (int)$m[1];
            $desired = max(1, (int) $value);
            $this->hotels[$idx]['rooms'] = $this->hotels[$idx]['rooms'] ?? [];
            while (count($this->hotels[$idx]['rooms']) > $desired) array_pop($this->hotels[$idx]['rooms']);
            while (count($this->hotels[$idx]['rooms']) < $desired) {
                $this->hotels[$idx]['rooms'][] = ['room_type' => '', 'occupants' => 1, 'meal_basis' => 'room_only'];
            }
        }
    }

    // ── Excursion ─────────────────────────────────────────────────────
    public function addExcursion(): void
    {
        $this->showExcursion = true;
        if (empty($this->excursion_status)) $this->excursion_status = 'confirmed';
        $this->logActivity('Added Excursion section', '', 'update');
    }

    // ── Visas ──────────────────────────────────────────────────────────
    public function addVisa(): void
    {
        $this->suppressLogging = true;
        $this->visas[] = [
            'id'               => null,
            'passenger_name'   => '',
            'visa_type'        => 'tourist',
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
        $this->suppressLogging = false;
        $this->logActivity('Added Visa', 'Visa #'.count($this->visas).' added', 'update');
    }

    public function removeVisa(int $index): void
    {
        $label = $this->visas[$index]['passenger_name'] ?: 'Visa #'.($index+1);
        if (isset($this->visas[$index]['id']) && $this->visas[$index]['id']) {
            \App\Models\BookingVisa::find($this->visas[$index]['id'])?->delete();
        }
        unset($this->visas[$index]);
        $this->visas = array_values($this->visas);
        $this->logActivity('Removed Visa', $label.' removed', 'update');
    }

    // ── Transfers ──────────────────────────────────────────────────────
    public function addPickup(): void
    {
        $this->suppressLogging = true;
        $this->transferPickups[] = ['id'=>null,'location'=>'','date_time'=>'','flight_number'=>'','route'=>'','vehicle_type'=>'','supplier'=>'','actual_cost'=>'','selling_price'=>'','status'=>'confirmed','notes'=>''];
        $this->suppressLogging = false;
        $this->logActivity('Added Transfer Pickup', 'Pickup #'.count($this->transferPickups).' added', 'update');
    }

    public function removePickup(int $index): void
    {
        $loc = $this->transferPickups[$index]['location'] ?: 'Pickup #'.($index+1);
        unset($this->transferPickups[$index]);
        $this->transferPickups = array_values($this->transferPickups);
        $this->logActivity('Removed Transfer Pickup', $loc.' removed', 'update');
    }

    public function addDropoff(): void
    {
        $this->suppressLogging = true;
        $this->transferDropoffs[] = ['id'=>null,'location'=>'','date_time'=>'','flight_number'=>'','route'=>'','vehicle_type'=>'','supplier'=>'','actual_cost'=>'','selling_price'=>'','status'=>'confirmed','notes'=>''];
        $this->suppressLogging = false;
        $this->logActivity('Added Transfer Dropoff', 'Dropoff #'.count($this->transferDropoffs).' added', 'update');
    }

    public function removeDropoff(int $index): void
    {
        $loc = $this->transferDropoffs[$index]['location'] ?: 'Dropoff #'.($index+1);
        unset($this->transferDropoffs[$index]);
        $this->transferDropoffs = array_values($this->transferDropoffs);
        $this->logActivity('Removed Transfer Dropoff', $loc.' removed', 'update');
    }

    // ── Payment management ─────────────────────────────────────────────
    public function sendToAccounts(int $historyId): void
    {
        $ph = BookingPaymentHistory::findOrFail($historyId);
        if ($ph->booking_id !== $this->booking->id) return;
        $ph->update(['status' => 'sent_to_accounts']);
        AuditLogger::log(Auth::user(), $this->booking, 'payment_sent', "Payment {$historyId} sent to accounts for approval");
        $this->logActivity('Payment Sent to Accounts', 'Payment #' . $historyId . ' forwarded for approval', 'update', bypassViewerCheck: true);
        session()->flash('success', 'Payment sent to accounts for approval.');
        $this->refreshBooking();
    }

    public function deletePaymentHistory(int $historyId): void
    {
        if (Auth::user()->role !== 'admin') return;

        $ph = BookingPaymentHistory::where('booking_id', $this->booking->id)->find($historyId);
        if (!$ph) return;

        $amount = $ph->amount;
        $ph->delete();

        AuditLogger::log(Auth::user(), $this->booking, 'payment_deleted', "Payment history #{$historyId} deleted (£" . number_format($amount, 2) . ')');
        $this->logActivity('Payment Deleted', '£' . number_format($amount, 2) . ' payment record removed', 'update', bypassViewerCheck: true);

        $this->booking->load('paymentHistory');
        $this->refreshCcChargesFromHistory();

        if ($this->booking->booking_status === 'payment_charge_request') {
            $hasPending = $this->booking->paymentHistory->contains(fn($h) => $h->status === 'pending');
            if (!$hasPending) {
                $this->booking->update(['booking_status' => 'pending']);
                $this->bookingStatus = 'pending';
            }
        }

        session()->flash('success', 'Payment record deleted.');
    }

    // ── Margin sharing ─────────────────────────────────────────────────
    /** Other users this booking's margin can be shared with. */
    public function getShareCandidateUsersProperty()
    {
        return User::where('id', '!=', Auth::id())->orderBy('name')->get();
    }

    public function openShareMargin(): void
    {
        abort_unless(Auth::user()->hasPermission('bookings.share_margin'), 403);

        $this->shareUserId = '';
        $this->shareAmount = '';
        $this->shareNote = '';
        $this->resetErrorBag();
        $this->shareMarginOpen = true;
    }

    public function saveMarginShare(): void
    {
        abort_unless(Auth::user()->hasPermission('bookings.share_margin'), 403);

        $this->validate([
            'shareUserId' => 'required|exists:users,id',
            'shareAmount' => 'required|numeric|min:0.01',
            'shareNote'   => 'nullable|string|max:255',
        ]);

        $recipient = User::find($this->shareUserId);

        BookingMarginShare::create([
            'booking_id'          => $this->booking->id,
            'shared_with_user_id' => $this->shareUserId,
            'shared_by_user_id'   => Auth::id(),
            'amount'              => $this->shareAmount,
            'note'                => $this->shareNote ?: null,
        ]);

        AuditLogger::log(Auth::user(), $this->booking, 'margin_shared', "Shared £" . number_format((float) $this->shareAmount, 2) . " margin with {$recipient?->name}");
        $this->logActivity('Margin Shared', '£' . number_format((float) $this->shareAmount, 2) . " shared with {$recipient?->name}", 'update', bypassViewerCheck: true);

        $this->booking->load('marginShares.sharedWith', 'marginShares.sharedBy');
        $this->shareMarginOpen = false;
        session()->flash('success', 'Margin share recorded.');
    }

    public function removeMarginShare(int $shareId): void
    {
        abort_unless(Auth::user()->hasPermission('bookings.share_margin'), 403);

        $share = BookingMarginShare::where('booking_id', $this->booking->id)->find($shareId);
        if (!$share) return;

        $amount = $share->amount;
        $recipientName = $share->sharedWith?->name;
        $share->delete();

        AuditLogger::log(Auth::user(), $this->booking, 'margin_share_removed', "Removed £" . number_format((float) $amount, 2) . " margin share with {$recipientName}");
        $this->logActivity('Margin Share Removed', '£' . number_format((float) $amount, 2) . " share with {$recipientName} removed", 'update', bypassViewerCheck: true);

        $this->booking->load('marginShares.sharedWith', 'marginShares.sharedBy');
        session()->flash('success', 'Margin share removed.');
    }

    private function refreshBooking(): void
    {
        $this->booking->refresh();
        $this->booking->load([
            'passengers', 'payment', 'paymentHistory', 'documents',
            'flightDetail', 'flightDetails', 'flightCosts', 'hotels.rooms', 'transfers', 'visas',
            'marginShares.sharedWith', 'marginShares.sharedBy',
            'comments' => fn($q) => $q->with('user')->orderBy('created_at'),
        ]);
        $this->activityLog = $this->buildActivityLog($this->booking);
    }

    // ── Documents ──────────────────────────────────────────────────────
    public function addDocument(): void { $this->abortIfViewer(); $this->newDocuments[] = null; $this->newDocumentTypes[] = ''; $this->logActivity('Added Document Upload', '', 'update'); }
    public function removeDocument(int $i): void { $this->abortIfViewer(); unset($this->newDocuments[$i]); unset($this->newDocumentTypes[$i]); $this->newDocuments = array_values($this->newDocuments); $this->newDocumentTypes = array_values($this->newDocumentTypes); $this->logActivity('Removed Document Upload', '', 'update'); }

    public function saveDocument(int $i): void
    {
        $this->abortIfViewer();
        if (!isset($this->newDocuments[$i]) || !$this->newDocuments[$i]) {
            session()->flash('error', 'Please select a file first.');
            return;
        }
        if (empty($this->newDocumentTypes[$i])) {
            session()->flash('error', 'Please select a document type.');
            return;
        }

        try {
            $f = $this->newDocuments[$i];
            $type = $this->newDocumentTypes[$i];
            $path = $f->store('booking-documents', 'public');
            \App\Models\BookingDocument::create([
                'booking_id' => $this->booking->id,
                'uploaded_by' => Auth::id(),
                'file_name' => $f->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $f->getClientMimeType(),
                'document_type' => $type,
            ]);
            $this->logActivity('Document Uploaded', $f->getClientOriginalName() . ' (' . $type . ')', 'update', bypassViewerCheck: true);
            unset($this->newDocuments[$i]);
            unset($this->newDocumentTypes[$i]);
            $this->newDocuments = array_values($this->newDocuments);
            $this->newDocumentTypes = array_values($this->newDocumentTypes);
            $this->booking->load('documents');
            session()->flash('success', 'Document saved.');
        } catch (\Throwable $e) {
            \Log::error('BookingShow.saveDocument failed: '.$e->getMessage());
            session()->flash('error', 'Failed to save document.');
        }
    }

    public function deleteDocument(int $docId): void
    {
        if (Auth::user()->role !== 'admin') return;
        $doc = \App\Models\BookingDocument::where('booking_id', $this->booking->id)->find($docId);
        if (!$doc) return;
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->file_path);
        $doc->delete();
        $this->logActivity('Document Deleted', $doc->file_name, 'update', bypassViewerCheck: true);
        $this->booking->load('documents');
    }

    public function previewDocument(int $docId): void
    {
        $doc = \App\Models\BookingDocument::where('booking_id', $this->booking->id)->find($docId);
        if (!$doc) return;
        $this->previewDoc = $doc;
    }

    public function closePreview(): void { $this->previewDoc = null; }

    // ── Activity log helpers ───────────────────────────────────────────
    protected array $fieldSnapshot = [];
    protected bool $suppressLogging = false;

    private function logActivity(string $action, string $detail = '', string $type = 'info', bool $bypassViewerCheck = false): void
    {
        // God-mode admins (CEO) operate untracked — no activity log entry at all.
        if (Auth::user()?->isGodMode()) {
            return;
        }
        if (!$bypassViewerCheck) {
            $this->abortIfViewer();
        }

        $user   = Auth::user();
        $agent  = $user->name ?? 'System';
        $userId = Auth::id();
        $ts     = now();

        $ini = strtoupper(substr($agent, 0, 1));
        if (($sp = strpos($agent, ' ')) !== false) $ini .= strtoupper(substr($agent, $sp + 1, 1));
        $avatarUrl = $user?->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;

        $this->activity_log_entries[] = [
            'agent'           => $agent,
            'avatar_url'      => $avatarUrl,
            'avatar_initials' => $ini,
            'user_id'         => $userId,
            'timestamp'       => $ts->toDateTimeString(),
            'action'          => $action,
            'detail'          => $detail,
            'type'            => $type,
        ];

        // Mark dirty — actual DB write is batched in dehydrate() so multiple
        // logActivity calls in one round-trip produce only one UPDATE.
        $this->activityLogDirty = true;

        $jsonIndex = count($this->activity_log_entries) - 1;

        $this->activityLog[] = [
            'agent'           => $agent,
            'avatar_url'      => $avatarUrl,
            'avatar_initials' => $ini,
            'action'          => $action,
            'detail'          => $detail,
            'type'            => $type,
            'timestamp'       => $ts->format('d M Y, H:i'),
            'ts_sort'         => $ts->timestamp,
            'color'           => $this->getActivityColorConfig($action, $type),
            'json_index'      => $jsonIndex,
            'is_json'         => true,
            'reasons'         => [],
        ];
    }

    // ── Field-level change tracking ────────────────────────────────────
    public function updating($property, $value): void
    {
        if ($this->suppressLogging) return;
        if ($this->getFieldLabel($property) !== null) {
            $this->fieldSnapshot[$property] = data_get($this, $property);
        }
    }

    public function updated($property, $value): void
    {
        if ($this->suppressLogging) return;
        $label = $this->getFieldLabel($property);
        if ($label === null) return;

        $old = $this->fieldSnapshot[$property] ?? null;
        if ((string)$old === (string)$value) return;

        $detail = ($old !== null && $old !== '')
            ? "«{$old}» → «{$value}»"
            : "set to «{$value}»";

        $this->logActivity("Edited: {$label}", $detail, 'update');
    }

    private function getFieldLabel(string $property): ?string
    {
        $simple = [
            'booker_first_name'      => 'Caller First Name',
            'booker_last_name'       => 'Caller Last Name',
            'booker_email'           => 'Caller Email',
            'booker_mobile'          => 'Caller Mobile',
            'booker_landline'        => 'Caller Landline',
            'booker_address'         => 'Caller Address',
            'booker_postcode'        => 'Caller Postcode',
            'booker_title'           => 'Caller Title',
            'booker_whatsapp'        => 'Caller WhatsApp',
            'booking_type'           => 'Booking Type',
            'lead_source'            => 'Lead Source',
            'lead_nature'            => 'Lead Nature',
            'notes'                  => 'Notes',
            'excursion_name'         => 'Excursion Name',
            'excursion_destination'  => 'Excursion Destination',
            'excursion_supplier'     => 'Excursion Supplier',
            'excursion_actual_cost'  => 'Excursion Cost',
            'excursion_selling_price'=> 'Excursion Selling Price',
            'excursion_status'       => 'Excursion Status',
            'excursion_date'         => 'Excursion Date',
            'flight_atol'            => 'ATOL',
            'flight_safi'            => 'SAFI',
        ];
        if (isset($simple[$property])) return $simple[$property];

        // Passengers: passengers.N.field
        $paxFields = ['first_name','last_name','date_of_birth','passport_number','nationality','e_ticket_number','title','contact_number'];
        if (preg_match('/^passengers\.(\d+)\.('.implode('|',$paxFields).')$/', $property, $m)) {
            return 'Passenger #'.($m[1]+1).' '.ucwords(str_replace('_',' ',$m[2]));
        }

        // Flight segments: flightSegments.N.field
        $segFields = ['locator','airline_locator','airline','departure_airport','arrival_airport','departure_date','return_date','cabin','vendor','gds','pnr','reservation_status','ticket_issue_limit','flight_type'];
        if (preg_match('/^flightSegments\.(\d+)\.('.implode('|',$segFields).')$/', $property, $m)) {
            return 'PNR #'.($m[1]+1).' '.ucwords(str_replace('_',' ',$m[2]));
        }

        // Passenger costs
        if (preg_match('/^flightSegments\.(\d+)\.passenger_costs\.(\d+)\.(cost|sold)$/', $property, $m)) {
            return 'PNR #'.($m[1]+1).' Pax #'.($m[2]+1).' '.ucfirst($m[3]);
        }

        // Hotels: hotels.N.field
        $hotelFields = ['hotel_name','city','status','check_in','check_out','actual_cost','selling_price'];
        if (preg_match('/^hotels\.(\d+)\.('.implode('|',$hotelFields).')$/', $property, $m)) {
            return 'Hotel #'.($m[1]+1).' '.ucwords(str_replace('_',' ',$m[2]));
        }

        // Hotel rooms: hotels.N.rooms.M.field
        $roomFields = ['room_type','occupants','meal_basis'];
        if (preg_match('/^hotels\.(\d+)\.rooms\.(\d+)\.('.implode('|',$roomFields).')$/', $property, $m)) {
            return 'Hotel #'.($m[1]+1).' Room #'.($m[2]+1).' '.ucwords(str_replace('_',' ',$m[3]));
        }

        // Visas: visas.N.field
        $visaFields = ['passenger_name','visa_type','status','actual_cost','selling_price','visa_reference','visa_number','application_date','expiry_date','notes'];
        if (preg_match('/^visas\.(\d+)\.('.implode('|',$visaFields).')$/', $property, $m)) {
            return 'Visa #'.($m[1]+1).' '.ucwords(str_replace('_',' ',$m[2]));
        }

        // Transfer pickups/dropoffs
        $txFields = ['location','flight_number','route','vehicle_type','supplier','actual_cost','selling_price','status'];
        if (preg_match('/^transferPickups\.(\d+)\.('.implode('|',$txFields).')$/', $property, $m)) {
            return 'Pickup #'.($m[1]+1).' '.ucwords(str_replace('_',' ',$m[2]));
        }
        if (preg_match('/^transferDropoffs\.(\d+)\.('.implode('|',$txFields).')$/', $property, $m)) {
            return 'Dropoff #'.($m[1]+1).' '.ucwords(str_replace('_',' ',$m[2]));
        }

        return null;
    }

    public function addActivityEntry(): void
    {
        if (empty(trim($this->mandatory_comment))) return;
        $this->logActivity('Note', $this->mandatory_comment, 'note');
        $this->mandatory_comment = '';
    }

    // ── Status change ──────────────────────────────────────────────────
    public function updatedBookingStatus($value)
    {
        if ($this->isLocked) return;
        $this->abortIfViewer();
        $oldStatus = $this->booking->booking_status;
        $this->booking->update(['booking_status' => $value]);
        $this->booking->refresh();
        $label = \App\Models\Booking::STATUS_LABELS[$value] ?? ucfirst(str_replace('_', ' ', $value));
        AuditLogger::log(Auth::user(), $this->booking, 'status_changed', "Status changed to {$label}", ['booking_status' => $oldStatus], ['booking_status' => $value]);
        $this->logActivity("Status → {$label}", '', 'update');
    }

    public function cancelPnr(): void
    {
        $this->abortIfViewer();
        if (!in_array(Auth::user()->role, ['admin', 'manager', 'operations'])) return;

        // Mark all flight segments as Cancelled in memory
        foreach ($this->flightSegments as $i => $_) {
            $this->flightSegments[$i]['reservation_status'] = 'Cancelled';
        }

        // Persist: update every BookingFlightDetail row for this booking
        $this->booking->flightDetails()->update(['reservation_status' => 'Cancelled']);

        AuditLogger::log(Auth::user(), $this->booking, 'pnr_cancelled', 'All PNR segments marked as Cancelled');
        $this->logActivity('Cancel PNR', 'All flight segments marked Cancelled', 'update');
        session()->flash('success', 'PNR segments cancelled.');
    }

    public function requestIssuance(): void
    {
        $this->abortIfViewer();
        $oldStatus = $this->booking->booking_status;
        $this->booking->update(['booking_status' => 'issuance_queue', 'issuance_requested_at' => now()]);
        $this->booking->refresh();
        $this->bookingStatus = 'issuance_queue';
        AuditLogger::log(Auth::user(), $this->booking, 'status_changed', 'Moved to Issuance Queue', ['booking_status' => $oldStatus], ['booking_status' => 'issuance_queue']);
        $this->logActivity('Request Issuance', 'Booking sent to Issuance Queue', 'update');
    }

    public function markTicketInProcess(): void
    {
        $this->abortIfViewer();
        $this->booking->update(['booking_status' => 'ticket_in_process']);
        $this->booking->refresh();
        $this->bookingStatus = 'ticket_in_process';
        $this->logActivity('Ticket In Process', 'Ticket is now being processed', 'update');
    }

    public function markIssued(string $paymentType = 'issued'): void
    {
        $this->abortIfViewer();
        $allowed = ['issued', 'issued_payment_awaiting', 'issued_payment_plan'];
        if (!in_array($paymentType, $allowed)) $paymentType = 'issued';
        $oldStatus = $this->booking->booking_status;
        $this->booking->update(['booking_status' => $paymentType]);
        $this->booking->refresh();
        $this->bookingStatus = $paymentType;
        $label = \App\Models\Booking::STATUS_LABELS[$paymentType] ?? 'Issued';
        AuditLogger::log(Auth::user(), $this->booking, 'status_changed', "Booking issued — {$label}", ['booking_status' => $oldStatus], ['booking_status' => $paymentType]);
        $this->logActivity('Ticket Issued', "Status set to: {$label}", 'update');
    }

    // ── Charge Popup ─────────────────────────────────────────────────
    public function openChargeModal(): void
    {
        $this->chargeAmount = '';
        $this->chargeMethod = '';
        $this->chargeReceipt = '';
        $this->chargeCcRate = '';
        $this->chargeCcAmount = '';
        $this->card_number = '';
        $this->card_expiry = '';
        $this->card_cvv = '';
        $this->card_holder_name = '';
        $this->showChargeModal = true;
    }

    public function closeChargeModal(): void
    {
        $this->showChargeModal = false;
    }

    public function requestPaymentCharge(): void
    {
        $this->validate([
            'chargeAmount' => 'required|numeric|min:1',
            'chargeMethod' => 'required|string',
        ]);

        // Calculate CC charge for this payment
        $this->recalculateChargeCcAmount();
        $ccAmountForThisCharge = $this->chargeCcAmount;

        // Build payment details including CC charge info
        $paymentDetails = array_merge(
            in_array($this->chargeMethod, ['debit_card','credit_card','amex']) ? [
                'card_number' => $this->card_number,
                'card_expiry' => $this->card_expiry,
                'card_cvv' => $this->card_cvv,
                'card_holder_name' => $this->card_holder_name,
            ] : [],
            [
                'cc_charge'      => $ccAmountForThisCharge,
                'cc_charge_rate' => $this->chargeCcRate ? (float)$this->chargeCcRate : null,
            ],
        );

        BookingPaymentHistory::create([
            'booking_id' => $this->booking->id,
            'user_id' => Auth::id(),
            'amount' => $this->chargeAmount,
            'payment_method' => $this->chargeMethod,
            'receipt_number' => $this->chargeReceipt ?: null,
            'payment_date' => now(),
            'status' => 'pending',
            'payment_details' => $paymentDetails,
        ]);

        // Lock the form until accounts approves
        $this->booking->update(['booking_status' => 'payment_charge_request']);
        $this->booking->refresh();
        $this->bookingStatus = 'payment_charge_request';

        AuditLogger::log(Auth::user(), $this->booking, 'payment_requested', 'Payment charge of £' . number_format((float)$this->chargeAmount, 2) . ' requested via ' . $this->chargeMethod);
        $this->logActivity('Request Payment Charge', '£' . number_format((float)$this->chargeAmount, 2) . ' via ' . ucwords(str_replace('_', ' ', $this->chargeMethod)), 'update', bypassViewerCheck: true);

        // Save the rate to booking_payments for display alongside the CC total
        if ($this->booking->payment && $this->chargeCcRate) {
            $this->booking->payment->update(['cc_charge_rate' => $this->chargeCcRate]);
        }

        // Reload payment history and recalculate total CC charges from all records
        $this->booking->load('paymentHistory');
        $this->refreshCcChargesFromHistory();

        $this->showChargeModal = false;
        $this->chargeAmount = '';
        $this->chargeMethod = '';
        $this->chargeReceipt = '';
        $this->chargeCcRate = '';
        $this->chargeCcAmount = '';
        $this->booking->load('payment', 'paymentHistory');
        $this->activityLog = $this->buildActivityLog($this->booking);
        session()->flash('success', 'Payment charge requested successfully.');
    }

    // ── CC charge rate auto-calculation ──────────────────────────────
    public function updatedChargeMethod(): void
    {
        $cardRates = [
            'epay_debit'   => 1.5,
            'epay_credit'  => 2.5,
            'debit_card'   => 1.5,
            'credit_card'  => 2.5,
            'amex'         => 2.5,
        ];
        if (isset($cardRates[$this->chargeMethod])) {
            $this->chargeCcRate = $cardRates[$this->chargeMethod];
            $this->recalculateChargeCcAmount();
        } else {
            $this->chargeCcRate = '';
            $this->chargeCcAmount = 0;
        }
    }

    public function updatedChargeCcRate(): void
    {
        $this->recalculateChargeCcAmount();
    }

    private function recalculateChargeCcAmount(): void
    {
        $amount = (float)($this->chargeAmount ?: 0);
        $rate   = (float)($this->chargeCcRate ?: 0);
        $this->chargeCcAmount = $rate > 0 && $amount > 0
            ? round($amount * $rate / 100, 2)
            : 0;
    }

    /**
     * Recalculate cc_charges by summing the CC fee from every approved payment.
     *
     * Each approved record's fee is taken from payment_details['cc_charge']
     * when present.  For older records that pre-date per-transaction tracking,
     * the fee is derived from the payment amount and the rate stored in
     * payment_details['cc_charge_rate'] (or the method's default rate).
     * Only approved payments are counted; pending/rejected records are excluded.
     */
    private function refreshCcChargesFromHistory(): void
    {
        $cardRates = [
            'epay_debit'  => 1.5,
            'epay_credit' => 2.5,
            'debit_card'  => 1.5,
            'credit_card' => 2.5,
            'amex'        => 2.5,
        ];

        $history  = $this->booking->paymentHistory ?? collect();
        $approved = $history->filter(fn($ph) => $ph->status === 'approved');

        if ($approved->isEmpty()) {
            $this->cc_charges = '';
            return;
        }

        $bookingCcRate = (float) ($this->booking->payment?->cc_charge_rate ?? 0);

        $total = $approved->sum(function ($ph) use ($cardRates, $bookingCcRate) {
            if (isset($ph->payment_details['cc_charge'])) {
                return (float) $ph->payment_details['cc_charge'];
            }
            // Fallback for records without a stored cc_charge: use the rate from
            // payment_details if present, then the booking-level rate, then the
            // method default.
            $method = $ph->payment_method;
            if (!isset($cardRates[$method])) {
                return 0;
            }
            $rate = isset($ph->payment_details['cc_charge_rate'])
                ? (float) $ph->payment_details['cc_charge_rate']
                : ($bookingCcRate ?: $cardRates[$method]);
            return round((float) $ph->amount * $rate / 100, 2);
        });

        $this->cc_charges = $total;
        if ($this->booking->payment) {
            $this->booking->payment->update(['cc_charges' => $total]);
        }
    }

    // ── Comments ───────────────────────────────────────────────────────
    /** Header presets available when commenting. */
    public function getCommentPresetsProperty(): array
    {
        return BookingComment::PRESETS;
    }

    /** Clicking the same bubble again clears it, so a preset is never sticky by accident. */
    public function toggleCommentPreset(string $key): void
    {
        if (!array_key_exists($key, $this->commentPresets)) return;
        $this->commentPreset = $this->commentPreset === $key ? '' : $key;
    }

    public function addComment()
    {
        if (!$this->canComment()) return;
        // Column is TEXT (unlimited); 20k is a sanity bound that still allows
        // pasting long multi-line content (PNR dumps, policy text) intact.
        $this->validate(['newComment' => 'required|string|max:20000']);

        // Never trust the posted preset — re-check it against what this user may use.
        $preset = array_key_exists($this->commentPreset, $this->commentPresets)
            ? $this->commentPreset
            : null;

        $user = Auth::user();
        BookingComment::create([
            'booking_id' => $this->booking->id,
            'user_id'    => $user->id,
            'agent_name' => $user->name,
            'avatar_url' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
            'action'     => 'comment_added',
            'comment'    => $this->newComment,
            'preset'     => $preset,
        ]);

        $label = $preset ? BookingComment::PRESETS[$preset]['label'] . ': ' : '';
        AuditLogger::log(Auth::user(), $this->booking, 'comment_added', 'Comment added', null, ['comment' => $label . $this->newComment]);

        $this->newComment = '';
        $this->commentPreset = '';
        $this->booking->load(['comments' => fn($q) => $q->with('user')->orderBy('created_at')]);
        $this->activityLog = $this->buildActivityLog($this->booking);
    }

    public function saveLogEntryComment(int $jsonIndex, string $comment): void
    {
        $comment = trim($comment);
        if (!$comment) return;

        $logs = $this->activity_log_entries;
        if (!isset($logs[$jsonIndex])) return;

        $user    = Auth::user();
        $reasons = $logs[$jsonIndex]['reasons'] ?? (isset($logs[$jsonIndex]['reason'])
            ? [['text' => $logs[$jsonIndex]['reason'], 'agent' => null, 'at' => null]]
            : []);

        $avatarUrl      = $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;
        $avatarInitials = strtoupper(substr($user->name, 0, 1));
        if (($sp = strpos($user->name, ' ')) !== false) $avatarInitials .= strtoupper(substr($user->name, $sp + 1, 1));

        $reasons[] = [
            'text'            => $comment,
            'agent'           => $user->name,
            'avatar_url'      => $avatarUrl,
            'avatar_initials' => $avatarInitials,
            'at'              => now()->format('d M Y, H:i'),
        ];

        $logs[$jsonIndex]['reasons'] = $reasons;
        unset($logs[$jsonIndex]['reason']); // migrate old single-reason field

        $this->activity_log_entries = $logs;
        $this->booking->update(['activity_log' => array_values($logs)]);

        // Also create a new timeline entry so the comment is visible in the log
        $parentAction = $logs[$jsonIndex]['action'] ?? 'entry';
        $this->logActivity('added a comment', "On: {$parentAction} — {$comment}", 'note');

        $this->activityLog = $this->buildActivityLog($this->booking->fresh());
    }

    public function canComment(): bool
    {
        $user = Auth::user();
        return $user->id === $this->booking->user_id || $user->role === 'admin';
    }

    // ── Refunds ────────────────────────────────────────────────────────
    public function requestRefund(): void
    {
        $this->abortIfViewer();
        $this->refundAmount = $this->booking->total_sale_price ?? '';
        $this->refundReason = '';
        $this->refundMethod = 'bank_transfer';
        $this->showRefundModal = true;
    }

    public function submitRefund(): void
    {
        $this->validate([
            'refundAmount' => 'required|numeric|min:0.01',
            'refundReason' => 'required|string|max:1000',
            'refundMethod' => 'required|in:cash,bank_transfer,stripe,klarna',
        ]);
        $oldStatus = $this->booking->booking_status;
        Refund::create([
            'booking_id' => $this->booking->id,
            'requested_by' => Auth::id(),
            'refund_amount' => $this->refundAmount,
            'reason' => $this->refundReason,
            'refund_method' => $this->refundMethod,
            'requested_at' => now(),
            'status' => 'requested',
        ]);
        $this->booking->update(['booking_status' => 'refund_queue']);
        $this->bookingStatus = 'refund_queue';
        $this->booking->refresh();
        AuditLogger::log(Auth::user(), $this->booking, 'status_changed', "Refund requested", ['booking_status' => $oldStatus], ['booking_status' => 'refund_queue']);
        $this->showRefundModal = false;
        session()->flash('success', 'Refund request submitted.');
    }

    // ── Country list ───────────────────────────────────────────────────
    public function countries(): array
    {
        return ['AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AO' => 'Angola', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BE' => 'Belgium', 'BJ' => 'Benin', 'BT' => 'Bhutan', 'BA' => 'Bosnia', 'BR' => 'Brazil', 'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CL' => 'Chile', 'CN' => 'China', 'CO' => 'Colombia', 'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FI' => 'Finland', 'FR' => 'France', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GR' => 'Greece', 'GT' => 'Guatemala', 'GN' => 'Guinea', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HN' => 'Honduras', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KW' => 'Kuwait', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LY' => 'Libya', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MX' => 'Mexico', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PL' => 'Poland', 'PT' => 'Portugal', 'QA' => 'Qatar', 'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TG' => 'Togo', 'TT' => 'Trinidad', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'UAE', 'GB' => 'United Kingdom', 'US' => 'United States', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe'];
    }

    public function paymentMethodOptions(): array
    {
        return [
            'epay_debit' => 'Epay Debit', 'epay_credit' => 'Epay Credit', 'amex' => 'AMEX',
            'klarna' => 'Klarna', 'superpay' => 'SuperPay', 'clearpay' => 'ClearPay',
            'stripe' => 'Stripe', 'refund' => 'Refund', 'previous_booking' => 'Previous Booking',
            'dnpl' => 'DNPL', 'cash' => 'Bank Transfer', 'debit_card' => 'Debit Card', 'credit_card' => 'Credit Card',
        ];
    }

    // ── SAVE ───────────────────────────────────────────────────────────
    public function save()
    {
        if ($this->isViewerOnly && !Auth::user()->hasPermission('bookings.edit_any')) {
            session()->flash('error', 'You do not have permission to modify this booking.');
            return;
        }

        if (empty($this->passengers)) {
            session()->flash('error', 'Add at least one passenger.');
            return;
        }

        if (empty(trim($this->mandatory_comment))) {
            $this->addError('mandatory_comment', 'A reason/comment is required before saving.');
            return;
        }

        $this->validate([
            'booking_type'             => 'required',
            'passengers'               => 'required|array|min:1',
            'passengers.*.title'       => 'required',
            'passengers.*.first_name'  => 'required|string|max:255',
            'passengers.*.last_name'   => 'required|string|max:255',
            'passengers.*.date_of_birth' => 'required|date',
            'passengers.*.passport_number' => 'required|string|max:255',
            'passengers.*.contact_number' => 'required|string|max:255|regex:/^[0-9]+$/',
            'booking_status'            => 'required|in:pending,payment_charge_request,issuance_queue,ticket_in_process,invoiced,confirmed,cancelled,refund_queue,issued,issued_payment_awaiting,issued_payment_plan',
            'mandatory_comment'         => 'required|string|min:3',
        ]);

        DB::transaction(function () {
            $b = $this->booking;

            // Snapshot pricing before save for audit logging
            $oldFlightCosts = $b->flightCosts()->get()->toArray();
            $oldFlightSellingPrice = $b->flightDetail?->selling_price;
            $oldHotels = $b->hotels()->get()->toArray();
            $oldVisas = $b->visas()->get()->toArray();
            $oldExcursion = $b->excursion_data;
            $oldTransfers = $b->transfers()->get()->toArray();

            if (!$this->isLocked || Auth::user()->hasPermission('bookings.edit_any')) {

            // Activity JSON — build on in-memory entries (already includes all field-edit entries flushed live)
            $saveUser = Auth::user();
            $saveIni  = strtoupper(substr($saveUser->name, 0, 1));
            if (($sp = strpos($saveUser->name, ' ')) !== false) $saveIni .= strtoupper(substr($saveUser->name, $sp + 1, 1));
            $activityLog   = $this->activity_log_entries;
            $activityLog[] = [
                'agent'           => $saveUser->name,
                'avatar_url'      => $saveUser->profile_photo_path ? asset('storage/' . $saveUser->profile_photo_path) : null,
                'avatar_initials' => $saveIni,
                'timestamp'       => now()->toDateTimeString(),
                'action'          => 'updated',
                'comment'         => $this->mandatory_comment ?: '',
                'type'            => 'update',
            ];

            $b->update([
                'booking_type' => $this->booking_type,
                'passenger_count' => count($this->passengers),
                'booking_status' => $this->bookingStatus,
                'activity_log' => $activityLog,
            ]);
            // Keep in-memory entries in sync with what was just written; dehydrate() will see dirty=false.
            $this->activity_log_entries = array_values($activityLog);
            $this->activityLogDirty = false;

            // Activity log record
            BookingActivityLog::create([
                'booking_id' => $b->id, 'user_id' => Auth::id(), 'action' => 'updated',
                'comment' => $this->mandatory_comment ?: '',
            ]);

            if ($this->mandatory_comment) {
                $authUser = Auth::user();
                BookingComment::create([
                    'booking_id' => $b->id,
                    'user_id'    => $authUser->id,
                    'agent_name' => $authUser->name,
                    'avatar_url' => $authUser->profile_photo_path ? asset('storage/' . $authUser->profile_photo_path) : null,
                    'comment'    => $this->mandatory_comment,
                    'action'     => 'updated',
                    'is_mandatory' => true,
                ]);
            }

            // Passengers
            $updatedIds = [];
            foreach ($this->passengers as $p) {
                $ptc = $this->computePtc($p['date_of_birth'] ?? null, $p['type']);
                $data = [
                    'passenger_type' => $p['type'], 'title' => $p['title'] ?: null,
                    'first_name' => $p['first_name'], 'last_name' => $p['last_name'],
                    'full_name' => trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')),
                    'passport_number' => $p['passport_number'] ?: null,
                    'passport_country_code' => $p['passport_country_code'] ?: null,
                    'passport_issuing_country' => $p['passport_issuing_country'] ?: null,
                    'national_id_number' => $p['national_id_number'] ?: null,
                    'nationality' => $p['nationality'] ?: null,
                    'date_of_birth' => $p['date_of_birth'] ?: null,
                    'contact_number' => $p['contact_number'] ?: null,
                    'cost_per_pax' => is_numeric($p['cost_per_pax'] ?? '') ? (float)$p['cost_per_pax'] : null,
                    'sold_per_pax' => is_numeric($p['sold_per_pax'] ?? '') ? (float)$p['sold_per_pax'] : null,
                    'frequent_flyer_number' => $p['frequent_flyer_number'] ?: null,
                    'e_ticket_number' => $p['e_ticket_number'] ?: null,
                    'ptc' => $ptc,
                    'passenger_status_label' => $p['passenger_status_label'] ?: null,
                ];
                if (!empty($p['id'])) {
                    BookingPassenger::where('id', $p['id'])->update($data);
                    $updatedIds[] = $p['id'];
                } else {
                    $data['booking_id'] = $b->id;
                    $np = BookingPassenger::create($data);
                    $updatedIds[] = $np->id;
                }
            }
            BookingPassenger::where('booking_id', $b->id)->whereNotIn('id', $updatedIds)->delete();

            // Flight
            $canEditFlightHotel = in_array(Auth::user()->role, ['admin', 'manager', 'operations', 'issuance']) || (Auth::user()->role === 'agent' && $b->booking_status === 'pending');
            if ($canEditFlightHotel) {
                $hasFlightData = collect($this->flightSegments)->contains(fn($s) => $s['airline'] || $s['pnr'] || $s['departure_airport'] || $s['cabin'] || $s['locator']);
                if ($hasFlightData) {
                    BookingFlightDetail::where('booking_id', $b->id)->delete();
                    foreach ($this->flightSegments as $seg) {
                        BookingFlightDetail::create([
                            'booking_id' => $b->id,
                            'pnr' => $seg['pnr'] ?: null,
                            'folder_number' => $this->flight_folder_number ?: null,
                            'locator' => $seg['locator'] ?: null,
                            'airline_locator' => $seg['airline_locator'] ?: null,
                            'type_issuer' => $seg['type_issuer'] ?: null,
                            'reservation_status' => $seg['reservation_status'] ?: null,
                            'flight_type' => $seg['flight_type'] ?? 'return',
                            'airline' => $seg['airline'] ? strtoupper($seg['airline']) : null,
                            'vendor' => $seg['vendor'] ?: null,
                            'gds' => $seg['gds'] ?: null,
                            'cabin' => $seg['cabin'] ?: null,
                            'ticket_issue_limit' => $seg['ticket_issue_limit'] ?: null,
                            'atol' => $this->flight_atol,
                            'safi' => $this->flight_safi,
                            'departure_airport' => $seg['departure_airport'] ?: null,
                            'arrival_airport' => $seg['arrival_airport'] ?: null,
                            'departure_date' => $seg['departure_date'] ?: null,
                            'return_date' => $seg['return_date'] ?: null,
                            'selling_price' => $this->flight_selling_price ?: 0,
                            'cost' => is_numeric($seg['cost'] ?? '') ? (float)$seg['cost'] : null,
                            'sold' => is_numeric($seg['sold'] ?? '') ? (float)$seg['sold'] : null,
                            'passenger_costs' => !empty($seg['passenger_costs']) ? $seg['passenger_costs'] : null,
                        ]);
                    }
                    BookingFlightCost::where('booking_id', $b->id)->delete();
                    foreach (['adult', 'child', 'gbe', 'infant'] as $type) {
                        $qty = (int)($this->flight_costs[$type]['qty'] ?? 0);
                        if ($qty > 0) {
                            BookingFlightCost::create([
                                'booking_id' => $b->id, 'cost_type' => $type,
                                'cost' => $this->flight_costs[$type]['cost'] ?: 0,
                                'quantity' => $qty,
                                'sold_price' => $this->flight_costs[$type]['sold'] ?: 0,
                            ]);
                        }
                    }
                }

                // Hotels
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
                        $hotelData['booking_id'] = $b->id;
                        $bookingHotel = BookingHotel::create($hotelData);
                        $keptHotelIds[] = $bookingHotel->id;
                    }
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
                BookingHotel::where('booking_id', $b->id)->whereNotIn('id', $keptHotelIds)->delete();

                // Visas — sync: upsert existing, delete removed
                $keptVisaIds = [];
                foreach ($this->visas as $visa) {
                    $data = [
                        'booking_id'       => $b->id,
                        'passenger_name'   => $visa['passenger_name'] ?: null,
                        'visa_type'        => $visa['visa_type'] ?? 'tourist',
                        'visa_reference'   => $visa['visa_reference'] ?: null,
                        'visa_number'      => $visa['visa_number'] ?: null,
                        'application_date' => $visa['application_date'] ?: null,
                        'issue_date'       => $visa['issue_date'] ?: null,
                        'expiry_date'      => $visa['expiry_date'] ?: null,
                        'status'           => $visa['status'] ?? 'pending',
                        'actual_cost'      => is_numeric($visa['actual_cost'] ?? '') ? (float)$visa['actual_cost'] : 0,
                        'selling_price'    => is_numeric($visa['selling_price'] ?? '') ? (float)$visa['selling_price'] : 0,
                        'notes'            => $visa['notes'] ?: null,
                    ];
                    if (!empty($visa['id'])) {
                        \App\Models\BookingVisa::where('id', $visa['id'])->update($data);
                        $keptVisaIds[] = $visa['id'];
                    } else {
                        $newVisa = \App\Models\BookingVisa::create($data);
                        $keptVisaIds[] = $newVisa->id;
                    }
                }
                \App\Models\BookingVisa::where('booking_id', $b->id)->whereNotIn('id', $keptVisaIds)->delete();

                // Excursion data
                if ($this->booking_type === 'excursion' || !empty($this->excursion_name)) {
                    $b->update(['excursion_data' => [
                        'name'          => $this->excursion_name ?: null,
                        'destination'   => $this->excursion_destination ?: null,
                        'date'          => $this->excursion_date ?: null,
                        'supplier'      => $this->excursion_supplier ?: null,
                        'actual_cost'   => is_numeric($this->excursion_actual_cost) ? (float)$this->excursion_actual_cost : 0,
                        'selling_price' => is_numeric($this->excursion_selling_price) ? (float)$this->excursion_selling_price : 0,
                        'notes'         => $this->excursion_notes ?: null,
                        'status'        => $this->excursion_status ?: 'confirmed',
                    ]]);
                }

                // Transfers — delete then recreate to handle removals cleanly
                $b->transfers()->delete();
                foreach ($this->transferPickups as $pickup) {
                    if (!empty($pickup['location'])) {
                        BookingTransfer::create([
                            'booking_id'    => $b->id,
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
                            'booking_id'    => $b->id,
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
            }

            } // end !isLocked guard

            // Log pricing changes
            $user = Auth::user();
            if (in_array($user->role, ['admin', 'manager', 'operations', 'issuance'])) {
                $newFlightCosts = $b->flightCosts()->get()->toArray();
                $newFlightSellingPrice = $b->flightDetail?->selling_price;
                $newHotels = $b->hotels()->get()->toArray();
                $newVisas = $b->visas()->get()->toArray();
                $newExcursion = $b->excursion_data;
                $newTransfers = $b->transfers()->get()->toArray();

                $priceChanges = [];

                if ($oldFlightCosts != $newFlightCosts) {
                    $priceChanges[] = 'flight costs';
                }
                if ((float)($oldFlightSellingPrice ?? 0) != (float)($newFlightSellingPrice ?? 0)) {
                    $priceChanges[] = 'flight selling price (' . number_format((float)$newFlightSellingPrice, 2) . ')';
                }
                if ($oldHotels != $newHotels) {
                    $priceChanges[] = 'hotel costs';
                }
                if ($oldVisas != $newVisas) {
                    $priceChanges[] = 'visa costs';
                }
                if (($oldExcursion ?? []) != ($newExcursion ?? [])) {
                    $priceChanges[] = 'excursion pricing';
                }
                if ($oldTransfers != $newTransfers) {
                    $priceChanges[] = 'transfer costs';
                }

                if (!empty($priceChanges)) {
                    AuditLogger::log(
                        $user, $b, 'pricing_updated',
                        'Pricing changed: ' . implode(', ', $priceChanges),
                        [
                            'flight_costs' => $oldFlightCosts,
                            'flight_selling_price' => $oldFlightSellingPrice,
                            'hotels' => $oldHotels,
                            'visas' => $oldVisas,
                            'excursion' => $oldExcursion,
                            'transfers' => $oldTransfers,
                        ],
                        [
                            'flight_costs' => $newFlightCosts,
                            'flight_selling_price' => $newFlightSellingPrice,
                            'hotels' => $newHotels,
                            'visas' => $newVisas,
                            'excursion' => $newExcursion,
                            'transfers' => $newTransfers,
                        ]
                    );
                    $this->logActivity('Pricing Updated', implode('; ', $priceChanges), 'update', bypassViewerCheck: true);
                }
            }

            // Documents
            if (!empty($this->newDocuments)) {
                foreach ($this->newDocuments as $i => $f) {
                    if ($f) {
                        $path = $f->store('booking-documents', 'public');
                        BookingDocument::create(['booking_id' => $b->id, 'uploaded_by' => Auth::id(), 'file_name' => $f->getClientOriginalName(), 'file_path' => $path, 'file_type' => $f->getClientMimeType(), 'document_type' => $this->newDocumentTypes[$i] ?? 'other']);
                        $this->logActivity('Document Uploaded', $f->getClientOriginalName() . ' (' . ($this->newDocumentTypes[$i] ?? 'other') . ')', 'update', bypassViewerCheck: true);
                    }
                }
            }
        });

        $this->newDocuments = [];
        $this->newDocumentTypes = [];
        $this->mandatory_comment = '';
        $this->booking->refresh();
        $this->booking->load(['passengers', 'payment', 'paymentHistory', 'documents', 'flightDetail', 'flightDetails', 'flightCosts', 'hotels.rooms', 'transfers', 'visas', 'marginShares.sharedWith', 'marginShares.sharedBy', 'comments' => fn($q) => $q->with('user')->orderBy('created_at')]);
        $this->loadBookingData();
        $this->activityLogDirty = false; // activity_log was already written inside the transaction
        $this->activityLog = $this->buildActivityLog($this->booking);
        session()->flash('success', "Booking #{$this->booking->booking_number} updated.");
    }

    // ── AUTO-SAVE ──────────────────────────────────────────────────────
    public function autoSave()
    {
        // Silently skip if viewer or no passengers
        if ($this->isViewerOnly && !Auth::user()->hasPermission('bookings.edit_any')) {
            return;
        }
        if (empty($this->passengers)) {
            return;
        }
        try {
        DB::transaction(function () {
            $b = $this->booking;

            $oldFlightCosts = $b->flightCosts()->get()->toArray();
            $oldFlightSellingPrice = $b->flightDetail?->selling_price;
            $oldHotels = $b->hotels()->get()->toArray();
            $oldVisas = $b->visas()->get()->toArray();
            $oldExcursion = $b->excursion_data;
            $oldTransfers = $b->transfers()->get()->toArray();

            if (!$this->isLocked || Auth::user()->hasPermission('bookings.edit_any')) {
                $b->update([
                    'booking_type' => $this->booking_type,
                    'passenger_count' => count($this->passengers),
                ]);

                // Passengers
                $updatedIds = [];
                foreach ($this->passengers as $p) {
                    $ptc = $this->computePtc($p['date_of_birth'] ?? null, $p['type']);
                    $data = [
                        'passenger_type' => $p['type'], 'title' => $p['title'] ?: null,
                        'first_name' => $p['first_name'], 'last_name' => $p['last_name'],
                        'full_name' => trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')),
                        'passport_number' => $p['passport_number'] ?: null,
                        'passport_country_code' => $p['passport_country_code'] ?: null,
                        'passport_issuing_country' => $p['passport_issuing_country'] ?: null,
                        'national_id_number' => $p['national_id_number'] ?: null,
                        'nationality' => $p['nationality'] ?: null,
                        'date_of_birth' => $p['date_of_birth'] ?: null,
                        'contact_number' => $p['contact_number'] ?: null,
                        'cost_per_pax' => is_numeric($p['cost_per_pax'] ?? '') ? (float)$p['cost_per_pax'] : null,
                        'sold_per_pax' => is_numeric($p['sold_per_pax'] ?? '') ? (float)$p['sold_per_pax'] : null,
                        'frequent_flyer_number' => $p['frequent_flyer_number'] ?: null,
                        'e_ticket_number' => $p['e_ticket_number'] ?: null,
                        'ptc' => $ptc,
                        'passenger_status_label' => $p['passenger_status_label'] ?: null,
                    ];
                    if (!empty($p['id'])) {
                        BookingPassenger::where('id', $p['id'])->update($data);
                        $updatedIds[] = $p['id'];
                    } else {
                        $data['booking_id'] = $b->id;
                        $np = BookingPassenger::create($data);
                        $updatedIds[] = $np->id;
                    }
                }
                BookingPassenger::where('booking_id', $b->id)->whereNotIn('id', $updatedIds)->delete();

                // Flight
                $canEditFlightHotel = in_array(Auth::user()->role, ['admin', 'manager', 'operations', 'issuance']) || (Auth::user()->role === 'agent' && $b->booking_status === 'pending');
                if ($canEditFlightHotel) {
                    $hasFlightData = collect($this->flightSegments)->contains(fn($s) => $s['airline'] || $s['pnr'] || $s['departure_airport'] || $s['cabin'] || $s['locator']);
                    if ($hasFlightData) {
                        BookingFlightDetail::where('booking_id', $b->id)->delete();
                        foreach ($this->flightSegments as $seg) {
                            BookingFlightDetail::create([
                                'booking_id' => $b->id,
                                'pnr' => $seg['pnr'] ?: null,
                                'folder_number' => $this->flight_folder_number ?: null,
                                'locator' => $seg['locator'] ?: null,
                                'airline_locator' => $seg['airline_locator'] ?: null,
                                'type_issuer' => $seg['type_issuer'] ?: null,
                                'reservation_status' => $seg['reservation_status'] ?: null,
                                'flight_type' => $seg['flight_type'] ?? 'return',
                                'airline' => $seg['airline'] ? strtoupper($seg['airline']) : null,
                                'vendor' => $seg['vendor'] ?: null,
                                'gds' => $seg['gds'] ?: null,
                                'cabin' => $seg['cabin'] ?: null,
                                'ticket_issue_limit' => $seg['ticket_issue_limit'] ?: null,
                                'atol' => $this->flight_atol,
                                'safi' => $this->flight_safi,
                                'departure_airport' => $seg['departure_airport'] ?: null,
                                'arrival_airport' => $seg['arrival_airport'] ?: null,
                                'departure_date' => $seg['departure_date'] ?: null,
                                'return_date' => $seg['return_date'] ?: null,
                                'selling_price' => $this->flight_selling_price ?: 0,
                                'cost' => is_numeric($seg['cost'] ?? '') ? (float)$seg['cost'] : null,
                                'sold' => is_numeric($seg['sold'] ?? '') ? (float)$seg['sold'] : null,
                                'passenger_costs' => !empty($seg['passenger_costs']) ? $seg['passenger_costs'] : null,
                            ]);
                        }
                        BookingFlightCost::where('booking_id', $b->id)->delete();
                        foreach (['adult', 'child', 'gbe', 'infant'] as $type) {
                            $qty = (int)($this->flight_costs[$type]['qty'] ?? 0);
                            if ($qty > 0) {
                                BookingFlightCost::create([
                                    'booking_id' => $b->id, 'cost_type' => $type,
                                    'cost' => $this->flight_costs[$type]['cost'] ?: 0,
                                    'quantity' => $qty,
                                    'sold_price' => $this->flight_costs[$type]['sold'] ?: 0,
                                ]);
                            }
                        }
                    }

                    // Hotels
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
                            $hotelData['booking_id'] = $b->id;
                            $bookingHotel = BookingHotel::create($hotelData);
                            $keptHotelIds[] = $bookingHotel->id;
                        }
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
                    BookingHotel::where('booking_id', $b->id)->whereNotIn('id', $keptHotelIds)->delete();

                    // Visas
                    $keptVisaIds = [];
                    foreach ($this->visas as $visa) {
                        $data = [
                            'booking_id'       => $b->id,
                            'passenger_name'   => $visa['passenger_name'] ?: null,
                            'visa_type'        => $visa['visa_type'] ?? 'tourist',
                            'visa_reference'   => $visa['visa_reference'] ?: null,
                            'visa_number'      => $visa['visa_number'] ?: null,
                            'application_date' => $visa['application_date'] ?: null,
                            'issue_date'       => $visa['issue_date'] ?: null,
                            'expiry_date'      => $visa['expiry_date'] ?: null,
                            'status'           => $visa['status'] ?? 'pending',
                            'actual_cost'      => is_numeric($visa['actual_cost'] ?? '') ? (float)$visa['actual_cost'] : 0,
                            'selling_price'    => is_numeric($visa['selling_price'] ?? '') ? (float)$visa['selling_price'] : 0,
                            'notes'            => $visa['notes'] ?: null,
                        ];
                        if (!empty($visa['id'])) {
                            \App\Models\BookingVisa::where('id', $visa['id'])->update($data);
                            $keptVisaIds[] = $visa['id'];
                        } else {
                            $newVisa = \App\Models\BookingVisa::create($data);
                            $keptVisaIds[] = $newVisa->id;
                        }
                    }
                    \App\Models\BookingVisa::where('booking_id', $b->id)->whereNotIn('id', $keptVisaIds)->delete();

                    // Excursion data
                    if ($this->booking_type === 'excursion' || !empty($this->excursion_name)) {
                        $b->update(['excursion_data' => [
                            'name'          => $this->excursion_name ?: null,
                            'destination'   => $this->excursion_destination ?: null,
                            'date'          => $this->excursion_date ?: null,
                            'supplier'      => $this->excursion_supplier ?: null,
                            'actual_cost'   => is_numeric($this->excursion_actual_cost) ? (float)$this->excursion_actual_cost : 0,
                            'selling_price' => is_numeric($this->excursion_selling_price) ? (float)$this->excursion_selling_price : 0,
                            'notes'         => $this->excursion_notes ?: null,
                            'status'        => $this->excursion_status ?: 'confirmed',
                        ]]);
                    }

                    // Transfers
                    $b->transfers()->delete();
                    foreach ($this->transferPickups as $pickup) {
                        if (!empty($pickup['location'])) {
                            BookingTransfer::create([
                                'booking_id'    => $b->id,
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
                                'booking_id'    => $b->id,
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
                }
            } elseif (in_array($b->booking_status, ['confirmed', 'issuance_queue', 'ticket_in_process'])) {
                // Core form is locked past pending, but e-ticket numbers stay editable
                // until the booking is actually issued — persist just that field.
                foreach ($this->passengers as $p) {
                    if (!empty($p['id'])) {
                        BookingPassenger::where('id', $p['id'])->update([
                            'e_ticket_number' => $p['e_ticket_number'] ?: null,
                        ]);
                    }
                }
            }

            // Log pricing changes
            $user = Auth::user();
            if (in_array($user->role, ['admin', 'manager', 'operations', 'issuance'])) {
                $newFlightCosts = $b->flightCosts()->get()->toArray();
                $newFlightSellingPrice = $b->flightDetail?->selling_price;
                $newHotels = $b->hotels()->get()->toArray();
                $newVisas = $b->visas()->get()->toArray();
                $newExcursion = $b->excursion_data;
                $newTransfers = $b->transfers()->get()->toArray();

                $priceChanges = [];
                if ($oldFlightCosts != $newFlightCosts) $priceChanges[] = 'flight costs';
                if ((float)($oldFlightSellingPrice ?? 0) != (float)($newFlightSellingPrice ?? 0)) $priceChanges[] = 'flight selling price (' . number_format((float)$newFlightSellingPrice, 2) . ')';
                if ($oldHotels != $newHotels) $priceChanges[] = 'hotel costs';
                if ($oldVisas != $newVisas) $priceChanges[] = 'visa costs';
                if (($oldExcursion ?? []) != ($newExcursion ?? [])) $priceChanges[] = 'excursion pricing';
                if ($oldTransfers != $newTransfers) $priceChanges[] = 'transfer costs';

                if (!empty($priceChanges)) {
                    AuditLogger::log(
                        $user, $b, 'pricing_updated',
                        'Pricing changed: ' . implode(', ', $priceChanges),
                        ['flight_costs' => $oldFlightCosts, 'flight_selling_price' => $oldFlightSellingPrice, 'hotels' => $oldHotels, 'visas' => $oldVisas, 'excursion' => $oldExcursion, 'transfers' => $oldTransfers],
                        ['flight_costs' => $newFlightCosts, 'flight_selling_price' => $newFlightSellingPrice, 'hotels' => $newHotels, 'visas' => $newVisas, 'excursion' => $newExcursion, 'transfers' => $newTransfers]
                    );
                    $this->logActivity('Pricing Updated', implode('; ', $priceChanges), 'update', bypassViewerCheck: true);
                }
            }

        });

        } catch (\Throwable $e) {
            \Log::error('BookingShow.autoSave failed: '.$e->getMessage(), ['booking_id' => $this->booking->id, 'trace' => $e->getTraceAsString()]);
            return;
        }

        // Lightweight post-save refresh — skip reloading all relationships since
        // in-memory state was just written to DB and is already current.
        // Flush pending activity log entries to DB first so buildActivityLog sees them.
        if ($this->activityLogDirty && !empty($this->activity_log_entries)) {
            $this->booking->update(['activity_log' => array_values($this->activity_log_entries)]);
            $this->activityLogDirty = false;
        }
        $this->booking->refresh();
        // Sync IDs for any passengers that were just created (had no ID before save)
        $dbPassengers = $this->booking->passengers()->pluck('id', 'full_name');
        foreach ($this->passengers as $i => $p) {
            if (empty($p['id'])) {
                $fullName = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                if ($dbPassengers->has($fullName)) {
                    $this->passengers[$i]['id'] = $dbPassengers[$fullName];
                }
            }
        }
        $this->activityLog = $this->buildActivityLog($this->booking);
    }

    public function dehydrate(): void
    {
        if ($this->activityLogDirty && !empty($this->activity_log_entries)) {
            $this->booking->update(['activity_log' => array_values($this->activity_log_entries)]);
            $this->activityLogDirty = false;
        }
    }

    public function render()
    {
        // When not editing payment, reload from DB so other users' changes show up
        if (!$this->paymentSectionEditing) {
            $this->booking->load('payment');
            $b = $this->booking;
            if ($b->payment) {
                $this->selected_payment_method = $b->payment->booking_plan ?? '';
                $saved = $b->payment->payment_instalments;
                if ($saved && is_array($saved)) {
                    $this->instalment_paid = $saved['paid'] ?? [];
                }
            }
        }

        return view('livewire.booking-show', [
            'countries' => $this->countries(),
            'paymentMethods' => $this->paymentMethodOptions(),
            'vendorOptions' => $this->cachedVendorOptions,
            'gdsOptions' => $this->cachedGdsOptions,
        ]);
    }
}
