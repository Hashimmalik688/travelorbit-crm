<div x-data="{
    saveTimer: null,
    saving: false,
    saved: false,
    triggerAutoSave() {
        if (this.saving) return;
        if (this.saveTimer) clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(() => {
            this.saving = true;
            this.saved = false;
            $wire.autoSave().then(() => {
                this.saving = false;
                this.saved = true;
                setTimeout(() => this.saved = false, 2500);
            }).catch(() => { this.saving = false; });
        }, 1800);
    },
    init() {
        this.$el.addEventListener('input', (e) => {
            if (e.target.type === 'file' || e.target.tagName === 'TEXTAREA') return;
            this.triggerAutoSave();
        });
        this.$el.addEventListener('change', (e) => {
            if (e.target.type === 'file' || e.target.tagName === 'TEXTAREA') return;
            this.triggerAutoSave();
        });
    }
}">
  <style>
    @keyframes as-spin {
      to {
        transform: rotate(360deg);
      }
    }

    .spinning {
      animation: as-spin .8s linear infinite;
    }

    .bv-section {
      background: #fff;
      border-radius: 16px;
      border: 1px solid rgba(51, 46, 158, .08);
      margin-bottom: 16px;
      overflow: visible;
    }

    .bv-section-hdr {
      padding: 14px 20px;
      border-bottom: 1px solid rgba(51, 46, 158, .06);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bv-section-hdr .bv-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .bv-section-hdr h2 {
      font-size: 1.02rem;
      font-weight: 700;
      color: #0F172A;
      margin: 0;
      letter-spacing: -.01em;
    }

    .bv-section-body {
      padding: 16px 20px;
    }

    .bv-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: #475569;
      margin-bottom: 2px;
    }

    .bv-value {
      font-size: 0.96rem;
      font-weight: 500;
      color: #1E293B;
    }

    .bv-divider {
      height: 1px;
      background: rgba(51, 46, 158, .05);
      margin: 14px 0;
    }

    .bv-pill {
      display: inline-flex;
      align-items: center;
      gap: 3px;
      padding: 2px 9px;
      border-radius: 20px;
      font-size: 0.792rem;
      font-weight: 700;
    }

    /* Cost & Margins rows — section headings sit clearly above their lighter column headers. */
    .bv-cm-hdr {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 11px 20px;
      font-size: 0.744rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .1em;
    }

    .bv-cm-cols {
      display: grid;
      padding: 7px 20px;
    }

    .bv-cm-cols span {
      font-size: 0.63rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: #94A3B8;
    }

    .bv-cm-row {
      display: grid;
      align-items: center;
      padding: 10px 20px;
    }

    .bv-cm-total {
      display: grid;
      align-items: center;
      padding: 9px 20px;
    }

    .bv-action {
      padding: 5px 14px;
      border-radius: 9px;
      font-size: 0.864rem;
      font-weight: 600;
      border: 1.5px solid;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      text-decoration: none;
      transition: all .15s;
    }

    .bv-action:hover {
      opacity: .85;
    }

    .bv-edit-pencil {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      border-radius: 5px;
      border: 1px solid rgba(51, 46, 158, .12);
      background: transparent;
      color: #475569;
      cursor: pointer;
      font-size: 0.792rem;
      flex-shrink: 0;
      margin-left: 3px;
      transition: all .12s;
    }

    .bv-edit-pencil:hover {
      background: rgba(51, 46, 158, .06);
      color: #332E9E;
      border-color: rgba(51, 46, 158, .25);
    }

    .bv-edit-pencil.locked {
      opacity: .35;
      cursor: not-allowed;
      pointer-events: none;
    }

    .bv-field-row {
      display: flex;
      align-items: flex-start;
      gap: 3px;
    }

    .bv-input-inline {
      border: 1.5px solid #332E9E;
      border-radius: 7px;
      padding: 3px 7px;
      font-size: 0.888rem;
      background: #fff;
      outline: none;
      width: 100%;
    }

    .bv-input-inline:focus {
      box-shadow: 0 0 0 3px rgba(51, 46, 158, .10);
    }

    .bv-select-inline {
      border: 1.5px solid #332E9E;
      border-radius: 7px;
      padding: 3px 7px;
      font-size: 0.888rem;
      background: #fff;
      outline: none;
      width: 100%;
    }

    .refund-overlay {
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .refund-overlay>div {
      scrollbar-width: none;
      -ms-overflow-style: none;
    }

    .refund-overlay>div::-webkit-scrollbar {
      display: none;
    }

    .rf-input {
      border: 1.5px solid #332E9E;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: 0.9rem;
      background: #fff;
      outline: none;
      width: 100%;
      min-height: 44px;
    }

    .rf-input:focus {
      box-shadow: 0 0 0 4px rgba(51, 46, 158, .12);
    }

    textarea.rf-input {
      min-height: 96px;
      padding-top: 12px;
      padding-bottom: 12px;
    }

    .refund-card {
      background: #F8FAFF;
      border: 1px solid rgba(51, 46, 158, .08);
      border-radius: 16px;
      padding: 18px;
      margin-bottom: 16px;
    }

    .refund-card-title {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 18px;
      font-size: 0.95rem;
      font-weight: 700;
      color: #0F172A;
    }

    .refund-card-subtitle {
      color: #475569;
      font-size: 0.82rem;
      font-weight: 600;
    }

    .refund-remove-btn {
      background: rgba(220, 38, 38, .08);
      border: none;
      color: #DC2626;
      border-radius: 10px;
      padding: 8px 12px;
      font-size: 0.82rem;
      cursor: pointer;
      transition: background .15s ease;
    }

    .refund-remove-btn:hover {
      background: rgba(220, 38, 38, .15);
    }

    .refund-footer {
      border-top: 1px solid rgba(51, 46, 158, .08);
      margin-top: 26px;
      padding-top: 20px;
    }

    .refund-actions button {
      min-width: 150px;
    }

    .bv-lock-badge {
      display: inline-flex;
      align-items: center;
      gap: 2px;
      font-size: 0.672rem;
      color: #475569;
      background: rgba(148, 163, 184, .08);
      padding: 1px 6px;
      border-radius: 10px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    .bv-pax-counter {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(51, 46, 158, .03);
      border: 1px solid rgba(51, 46, 158, .08);
      border-radius: 12px;
      padding: 6px 14px;
    }

    .bv-pax-counter .bv-pax-type {
      font-size: 0.78rem;
      font-weight: 800;
      letter-spacing: .06em;
      min-width: 24px;
    }

    .bv-pax-counter .bv-pax-btn {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.96rem;
      font-weight: 700;
      line-height: 1;
      border: none;
      cursor: pointer;
    }

    .bv-pax-counter .bv-pax-num {
      font-size: 1.14rem;
      font-weight: 800;
      min-width: 16px;
      text-align: center;
    }

    .bv-pax-total {
      background: linear-gradient(135deg, #332E9E, #4A45B5);
      color: #fff;
      border-radius: 12px;
      padding: 6px 16px;
      text-align: center;
      min-width: 56px;
    }

    .bv-pax-total .bv-pax-total-num {
      font-size: 1.2rem;
      font-weight: 800;
      line-height: 1;
    }

    .bv-pax-total .bv-pax-total-label {
      font-size: 0.672rem;
      opacity: .7;
      text-transform: uppercase;
    }

    .bv-seg-card {
      border-radius: 12px;
      border: 1px solid rgba(51, 46, 158, .12);
      overflow: visible;
      margin-bottom: 10px;
    }

    .bv-seg-hdr {
      padding: 8px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid rgba(51, 46, 158, .06);
    }

    .bv-seg-body {
      padding: 10px 14px;
    }

    .bv-pay-row {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 14px;
      border-radius: 10px;
      margin-bottom: 6px;
      font-size: 0.888rem;
    }

    .bv-pay-row.approved {
      background: rgba(22, 163, 74, .06);
      border: 1px solid rgba(22, 163, 74, .15);
    }

    .bv-pay-row.sent {
      background: rgba(245, 158, 11, .06);
      border: 1px solid rgba(245, 158, 11, .15);
    }

    .bv-pay-row.pending {
      background: rgba(220, 38, 38, .04);
      border: 1px solid rgba(220, 38, 38, .12);
    }

    .bv-pay-number {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.816rem;
      font-weight: 800;
      flex-shrink: 0;
    }

    .bv-pay-number.approved {
      background: #16A34A;
      color: #fff;
    }

    .bv-pay-number.sent {
      background: #F59E0B;
      color: #fff;
    }

    .bv-pay-number.pending {
      background: #DC2626;
      color: #fff;
    }

    .bv-pay-detail {
      flex: 1;
      min-width: 0;
      display: flex;
      flex-wrap: wrap;
      gap: 6px 14px;
      align-items: center;
    }

    .bv-pay-detail span {
      font-size: 0.84rem;
    }

    .bv-pay-status {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .06em;
      padding: 2px 8px;
      border-radius: 10px;
      white-space: nowrap;
    }

    .bv-pay-status.approved {
      background: rgba(22, 163, 74, .12);
      color: #15803D;
    }

    .bv-pay-status.sent {
      background: rgba(245, 158, 11, .12);
      color: #B45309;
    }

    .bv-pay-status.pending {
      background: rgba(220, 38, 38, .10);
      color: #DC2626;
    }

    .bv-pay-btn {
      font-size: 0.744rem;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 7px;
      border: none;
      cursor: pointer;
      white-space: nowrap;
    }

    .bv-pay-btn.send {
      background: #F59E0B;
      color: #fff;
    }

    .bv-pay-btn.approve {
      background: #16A34A;
      color: #fff;
    }

    [x-cloak] {
      display: none !important;
    }

    .bv-activity-row {
      display: flex;
      gap: 8px;
      padding: 4px 0;
      font-size: 0.84rem;
      border-bottom: 1px solid rgba(51, 46, 158, .03);
    }

    .bv-activity-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      margin-top: 6px;
      flex-shrink: 0;
    }

    [x-cloak] {
      display: none !important;
    }
  </style>

  @php
    $role = Auth::user()->role;
    $status = $booking->booking_status === 'invoiced' ? 'issued' : $booking->booking_status;
    $btype = $booking->booking_type ?? 'flight';
    $isPrivileged = Auth::user()->hasPermission('bookings.edit_any');
    $isAdmin = $role === 'admin';
    $isOwner = Auth::id() === $booking->user_id;

    // ── Documents: never locked by workflow stage. The owner (or anyone with
    // bookings.edit_any) can attach documents at ANY status — e-tickets,
    // invoices and vouchers are attached precisely after issuance. Matches
    // BookingShow::canManageDocuments() enforced server-side. ──
    $canUploadDocuments = $isPrivileged || $isOwner;

    // ── Core form lock (booking info, passengers, flight, hotel sections) ──
    // Editable only when pending/confirmed, or by privileged roles always
    $canEditCore = $isPrivileged || in_array($status, ['pending', 'confirmed']);
    $isLocked = !$canEditCore;

    // ── Booking type: manager/admin only, regardless of status ──
    $canEditBookingType = in_array($role, ['admin', 'manager']);

    // ── E-ticket number: editable up to and including ticket_in_process, locks once issued ──
    $canEditEticket =
        $isPrivileged || in_array($status, ['pending', 'confirmed', 'issuance_queue', 'ticket_in_process']);

    // ── Payment section (structure + request charge button) ──
    // Always editable for agents when awaiting payment charge approval
    $canEditPayment =
        $isPrivileged ||
        in_array($status, [
            'pending',
            'confirmed',
            'ticket_in_process',
            'issued_payment_awaiting',
            'issued_payment_plan',
            'payment_charge_request',
        ]);

    // ── Flight/hotel section fields (needs operations/issuance role + core unlock) ──
    $canEditFlightHotel =
        $canEditCore &&
        (in_array($role, ['admin', 'manager', 'operations', 'issuance']) ||
            ($role === 'agent' && $status === 'pending'));

    // ── Add buttons visibility: depends on booking type AND lock ──
    $canAddPNR = $canEditFlightHotel && in_array($btype, ['flight', 'holiday', 'umrah']);
    $canAddHotel = $canEditFlightHotel && in_array($btype, ['hotel', 'holiday', 'umrah']);
    $canAddVisa = $canEditFlightHotel && in_array($btype, ['umrah', 'holiday']);
    $canAddTransfer = $canEditFlightHotel && in_array($btype, ['transfers', 'umrah', 'holiday']);
    $canAddExcursion = $canEditFlightHotel && in_array($btype, ['excursion', 'holiday', 'umrah']);

    // ── Payment approvals ──
    $canApprovePayments = in_array($role, ['admin', 'manager', 'accounts']);

    // ── Viewer-only override: locks everything except Request Payment Charge ──
    $viewerOnly = Auth::id() !== $booking->user_id && !Auth::user()->hasPermission('bookings.edit_any');
    if ($viewerOnly) {
        $canEditCore = false;
        $isLocked = true;
        $canEditBookingType = false;
        $canEditEticket = false;
        $canEditPayment = false;
        $canEditFlightHotel = false;
        $canAddPNR = false;
        $canAddHotel = false;
        $canAddVisa = false;
        $canAddTransfer = false;
        $canAddExcursion = false;
        $canApprovePayments = false;
    }

    // ── Request Payment Charge button: always available for viewers, follows
    // canEditPayment otherwise — but never while one is already outstanding,
    // issued or not (the booking_status itself no longer reflects this for
    // issued bookings, so this checks the payment-history ledger directly). ──
    $hasPendingChargeRequest = $booking->paymentHistory->contains(fn($h) => $h->status === 'pending');
    $canRequestChargeButton = ($viewerOnly || $canEditPayment) && !$hasPendingChargeRequest;

    $canEditBooking = $canEditCore && Auth::user()->can('update', $booking);
  @endphp

  @php
    $stStatus = $booking->booking_status;
    // "Invoiced" is a label displayed next to the booking number, not a banner status
    $displayStatus = $stStatus === 'invoiced' ? 'issued' : $stStatus;
    $stColors = \App\Models\Booking::STATUS_COLORS[$displayStatus] ?? [
        'bg' => '#64748B',
        'text' => '#fff',
        'badge_bg' => 'rgba(148,163,184,.12)',
        'badge_color' => '#64748B',
    ];
    $stLabel = \App\Models\Booking::STATUS_LABELS[$displayStatus] ?? ucfirst(str_replace('_', ' ', $displayStatus));
    $stIcon = \App\Models\Booking::STATUS_ICONS[$displayStatus] ?? 'ph-circle';
    $typeIcons = [
        'flight' => 'ph-airplane',
        'hotel' => 'ph-buildings',
        'holiday' => 'ph-island',
        'umrah' => 'ph-mosque',
        'visa' => 'ph-identification-card',
        'transfers' => 'ph-van',
        'excursion' => 'ph-binoculars',
    ];
    $typeIcon = $typeIcons[$booking->booking_type ?? ''] ?? 'ph-ticket';
    $fd = $booking->flightDetail;
    $leadLabels = [
        'to_returning' => 'TO Returning',
        'to_referral' => 'TO Referral',
        'referral_client' => 'Referral Client',
        'returning_client' => 'Returning Client',
        'fb' => 'Facebook',
        'wa' => 'WhatsApp',
        'email' => 'Email',
        'diaspora_group' => 'Diaspora Group',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'website' => 'Website',
        'google' => 'Google',
        'personal' => 'Personal',
    ];
    $natureLabels = [
        'new_booking' => 'New Booking',
        'date_change' => 'Date Change',
        'refund_booking' => 'Refund Booking',
        'previous_booking' => 'Previous Booking',
    ];
    // Status → next action config
    $statusFlow = [
        'pending' => [
            'next' => 'issuance_queue',
            'action' => 'requestIssuance',
            'btn_label' => 'Request Issuance',
            'btn_icon' => 'ph-ticket',
            'btn_color' => '#7C3AED',
        ],
        'issuance_queue' => [
            'next' => 'ticket_in_process',
            'action' => 'markTicketInProcess',
            'btn_label' => 'Mark: Ticket In Process',
            'btn_icon' => 'ph-airplane-takeoff',
            'btn_color' => '#0369A1',
        ],
        'ticket_in_process' => [
            'next' => 'issued',
            'action' => null,
            'btn_label' => null,
            'btn_icon' => null,
            'btn_color' => null,
        ],
    ];
    $nextFlow = $statusFlow[$stStatus] ?? null;
  @endphp

  {{-- STATUS BANNER — status label only, centered --}}
  <div
    style="background:{{ $stColors['bg'] }};padding:12px 20px;border-radius:14px;margin-bottom:14px;display:flex;align-items:center;justify-content:center;gap:10px;">
    <i class="ph {{ $stIcon }}" style="font-size:1.32rem;color:{{ $stColors['text'] }};opacity:.85;"></i>
    <span
      style="font-size:1.2rem;font-weight:800;color:{{ $stColors['text'] }};letter-spacing:.01em;">{{ $stLabel }}</span>
    {{-- Issued bookings keep their status while a charge is pending — this badge
       is the only visible sign one is outstanding (see requestPaymentCharge()). --}}
    @if ($hasPendingChargeRequest && $stStatus !== 'payment_charge_request')
      <span
        style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#fff;background:rgba(0,0,0,.18);padding:4px 10px;border-radius:20px;display:flex;align-items:center;gap:4px;">
        <i class="ph ph-hourglass-medium"></i> Charge Requested
      </span>
    @endif
    {{-- A refund request is a Refund row, not a booking_status — the booking keeps
       its real status and this badge is the prominent sign one is in flight. --}}
    @if ($this->activeRefund)
      <span
        style="font-size:0.744rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:#fff;background:#DC2626;padding:5px 12px;border-radius:20px;display:flex;align-items:center;gap:5px;box-shadow:0 0 0 3px rgba(220,38,38,.25);">
        <i class="ph ph-arrows-counter-clockwise"></i> Refund Requested —
        &pound;{{ number_format($this->activeRefund->refund_amount, 2) }}
      </span>
    @endif
  </div>

  {{-- BOOKING CARD — three-column grid (not flex space-between) so the
     INVOICED seal sits truly centered on the card regardless of how long the
     left meta or right button group are, rather than wherever flex leftover
     space happens to land it. --}}
  <div
    style="background:#fff;border-radius:14px;border:1px solid rgba(51,46,158,.08);padding:18px 24px;margin-bottom:16px;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:16px;">
    <div class="d-flex align-items-center gap-20" style="gap:24px;">
      <div>
        <div class="bv-label">Booking</div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <span
            style="font-size:1.44rem;font-weight:800;color:#0F172A;letter-spacing:-.02em;">#{{ $booking->booking_number }}</span>
          <span class="d-flex align-items-center gap-1"
            style="font-size:0.768rem;font-weight:500;color:#475569;background:rgba(148,163,184,.08);padding:3px 8px;border-radius:10px;">
            <i class="ph {{ $typeIcon }}" style="color:#332E9E;font-size:0.84rem;"></i>
            {{ ucfirst($booking->booking_type ?? '-') }}
          </span>

          <span style="font-size:0.864rem;color:#475569;display:flex;align-items:center;gap:4px;">
            <i class="ph ph-calendar" style="font-size:0.96rem;color:#475569;"></i>
            {{ $booking->created_at->format('d M Y') }}
          </span>

          @if ($booking->last_payment_date)
            <span
              style="font-size:0.768rem;font-weight:500;color:#0369A1;background:rgba(3,105,161,.08);display:flex;align-items:center;gap:4px;padding:3px 8px;border-radius:10px;">
              <i class="ph ph-money" style="font-size:0.84rem;"></i>
              Last Payment: {{ $booking->last_payment_date->format('d M Y') }}
            </span>
          @endif

          @if ($booking->last_issue_date)
            <span
              style="font-size:0.768rem;font-weight:500;color:#047857;background:rgba(4,120,87,.08);display:flex;align-items:center;gap:4px;padding:3px 8px;border-radius:10px;">
              <i class="ph ph-check-fat" style="font-size:0.84rem;"></i>
              Last Issue: {{ $booking->last_issue_date->format('d M Y') }}
            </span>
          @endif
        </div>
      </div>
    </div>
    {{-- Always rendered, even with nothing inside — a CSS Grid column is only
       ever assigned to children that actually exist in the DOM. When the
       @if below produced no seal, this whole slot vanished and the button
       group got auto-placed into column 2 (the seal's own "auto" track)
       instead of column 3, landing mid-row instead of pushed to the right. --}}
    <div style="position:relative;display:flex;align-items:center;justify-content:center;margin:0 auto;">
      @if ($booking->invoiced_at)
        <div
          style="transform:rotate(-12deg);border:4px double #D4A017;border-radius:10px;padding:10px 28px;background:linear-gradient(135deg,rgba(212,160,23,.08),rgba(255,215,0,.05));">
          <div style="display:flex;align-items:center;gap:8px;">
            <i class="ph ph-seal-check" style="font-size:1.6rem;color:#D4A017;"></i>
            <div>
              <div
                style="font-size:0.54rem;font-weight:700;text-transform:uppercase;letter-spacing:.15em;color:#D4A017;">
                Booking</div>
              <div style="font-size:1.14rem;font-weight:900;color:#D4A017;letter-spacing:.03em;line-height:1;">INVOICED
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap" style="justify-content:flex-end;">

      {{-- ── Action buttons: REQUEST dispositions only. Approvals/marking happen on dedicated dashboards. ── --}}

      {{-- Request Issuance: all roles except accounts, when booking is editable --}}
      @if (in_array($status, ['pending', 'confirmed']) && !in_array($role, ['accounts']))
        <button type="button" wire:click="requestIssuance" class="bv-action"
          style="background:rgba(124,58,237,.08);border-color:rgba(124,58,237,.3);color:#7C3AED;">
          <i class="ph ph-ticket"></i> Request Issuance
        </button>
      @endif

      {{-- Request Refund: when ticket has been issued or booking is active.
         A refund already in flight is a Refund row, not a booking_status — so
         the booking keeps its real status (issued/invoiced/etc.) and the
         prominent "Refund Requested" badge in the status banner above carries
         that signal instead; this button just steps aside while one's active. --}}
      @if (in_array($status, ['pending', 'confirmed', 'issued', 'issued_payment_awaiting', 'issued_payment_plan']) &&
              !in_array($role, ['accounts', 'issuance']) &&
              !$this->activeRefund)
        <button type="button" x-data="{ open: @entangle('showRefundModal') }" @click="open = true" wire:click="requestRefund" class="bv-action"
          style="background:rgba(220,38,38,.06);border-color:rgba(220,38,38,.2);color:#DC2626;">
          <i class="ph ph-arrows-counter-clockwise"></i> Request Refund
        </button>
      @endif

      {{-- Cancel PNR: operations/manager/admin when form is editable --}}
      @if (in_array($role, ['admin', 'manager', 'operations']) && $canEditCore)
        <button type="button" wire:click="cancelPnr" wire:confirm="Cancel all PNR segments? This cannot be undone."
          class="bv-action" style="background:rgba(185,28,28,.05);border-color:rgba(185,28,28,.18);color:#B91C1C;">
          <i class="ph ph-x-circle"></i> Cancel PNR
        </button>
      @endif

      {{-- Revert mistaken Issued - Payment Plan: manager/admin only correction --}}
      @if ($status === 'issued_payment_plan' && in_array($role, ['admin', 'manager']))
        <button type="button" wire:click="revertIssuedPaymentPlan"
          wire:confirm="Revert this booking from Issued - Payment Plan back to the Issuance Queue? Use this only to correct a mistaken issuance."
          class="bv-action" style="background:rgba(14,116,144,.08);border-color:rgba(14,116,144,.3);color:#0E7490;">
          <i class="ph ph-arrow-counter-clockwise"></i> Revert to Issuance Queue
        </button>
      @endif

      {{-- separator --}}
      <div style="width:1px;height:28px;background:rgba(51,46,158,.1);margin:0 2px;"></div>

      <a href="{{ route('bookings.index') }}" class="bv-action"
        style="background:transparent;border-color:rgba(51,46,158,.2);color:#374151;"><i class="ph ph-arrow-left"></i>
        Back</a>
      @if ($canEditBooking || ($isLocked && Auth::user()->hasPermission('accounts.access')))
        <div
          style="display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:9px;font-size:0.816rem;font-weight:600;">
          <span x-show="saving" style="display:inline-flex;align-items:center;gap:4px;color:#475569;"><i
              class="ph ph-circle-notch spinning"></i> Saving...</span>
          <span x-show="saved && !saving" style="display:inline-flex;align-items:center;gap:4px;color:#16A34A;"><i
              class="ph ph-check-circle"></i> Saved</span>
        </div>
      @endif
    </div>
  </div>

  @if (session()->has('success'))
    <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2"
      style="background:rgba(22,163,74,.08);border-radius:10px;border:1px solid rgba(22,163,74,.15);color:#15803D;font-size:0.936rem;">
      <i class="ph ph-check-circle" style="font-size:1.2rem;"></i> {{ session('success') }}
    </div>
  @endif

  <div class="row g-3">
    <div class="col-lg-8" style="position:relative;z-index:2;">

      {{-- LEAD & CALLER --}}
      <div class="bv-section" x-data="{ sectionEditing: false }">
        <div class="bv-section-hdr">
          <div class="bv-icon" style="background:rgba(51,46,158,.08);"><i class="ph ph-user-circle"
              style="color:#332E9E;font-size:1.08rem;"></i></div>
          <h2>Lead &amp; Caller</h2>
          @if ($isPrivileged)
            <button type="button" @click="sectionEditing = !sectionEditing" class="bv-edit-pencil ms-auto"
              style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;"
              x-text="sectionEditing ? 'Done' : 'Edit'"></button>
          @endif
        </div>
        <div class="bv-section-body">
          <div class="row g-2">
            <div class="col-md-4">@include('livewire.partials.editable-field', [
                'label' => 'Lead Source',
                'model' => 'lead_source',
                'val' =>
                    $leadLabels[$booking->lead_source ?? ''] ??
                    ucfirst(str_replace('_', ' ', $booking->lead_source ?? '')),
                'type' => 'select',
                'options' => [
                    ['value' => 'to_returning', 'label' => 'TO Returning'],
                    ['value' => 'to_referral', 'label' => 'TO Referral'],
                    ['value' => 'referral_client', 'label' => 'Referral Client'],
                    ['value' => 'returning_client', 'label' => 'Returning Client'],
                    ['value' => 'fb', 'label' => 'Facebook'],
                    ['value' => 'wa', 'label' => 'WhatsApp'],
                    ['value' => 'email', 'label' => 'Email'],
                    ['value' => 'diaspora_group', 'label' => 'Diaspora Group'],
                    ['value' => 'instagram', 'label' => 'Instagram'],
                    ['value' => 'tiktok', 'label' => 'TikTok'],
                    ['value' => 'website', 'label' => 'Website'],
                    ['value' => 'google', 'label' => 'Google'],
                    ['value' => 'personal', 'label' => 'Personal'],
                ],
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
            <div class="col-md-4">@include('livewire.partials.editable-field', [
                'label' => 'Lead Nature',
                'model' => 'lead_nature',
                'val' =>
                    $natureLabels[$booking->lead_nature ?? ''] ??
                    ucfirst(str_replace('_', ' ', $booking->lead_nature ?? '')),
                'type' => 'select',
                'options' => [
                    ['value' => 'new_booking', 'label' => 'New Booking'],
                    ['value' => 'date_change', 'label' => 'Date Change'],
                    ['value' => 'refund_booking', 'label' => 'Refund Booking'],
                    ['value' => 'previous_booking', 'label' => 'Previous Booking'],
                ],
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
            <div class="col-md-4">@include('livewire.partials.editable-field', [
                'label' => 'Booking Type',
                'model' => 'booking_type',
                'val' => ucfirst($booking->booking_type ?? ''),
                'type' => 'select',
                'options' => [
                    ['value' => 'flight', 'label' => 'Flight'],
                    ['value' => 'hotel', 'label' => 'Hotel'],
                    ['value' => 'holiday', 'label' => 'Holidays'],
                    ['value' => 'umrah', 'label' => 'Umrah'],
                    ['value' => 'visa', 'label' => 'Visa'],
                    ['value' => 'transfers', 'label' => 'Transfers'],
                    ['value' => 'excursion', 'label' => 'Excursion'],
                ],
                'locked' => !$canEditBookingType,
                'editingVar' => 'sectionEditing',
            ])</div>

            <div class="col-12" style="margin:4px 0 8px;padding-top:10px;border-top:1px dashed rgba(51,46,158,.14);">
              <span
                style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94A3B8;">Caller
                Details</span>
            </div>

            <div class="col-md-2">@include('livewire.partials.editable-field', [
                'label' => 'Title',
                'model' => 'booker_title',
                'val' => \App\Models\Booking::TITLES[$booking->booker_title] ?? ($booking->booker_title ?? ''),
                'type' => 'select',
                'options' => [
                    ['value' => '1', 'label' => 'Mr.'],
                    ['value' => '2', 'label' => 'Ms.'],
                    ['value' => '3', 'label' => 'Mrs.'],
                    ['value' => '4', 'label' => 'Mstr'],
                    ['value' => '5', 'label' => 'Miss'],
                    ['value' => '6', 'label' => 'Dr.'],
                ],
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
            <div class="col-md-5">@include('livewire.partials.editable-field', [
                'label' => 'First Name',
                'model' => 'booker_first_name',
                'val' => $booking->booker_first_name ?? '',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
                'valueColor' => '#332E9E',
            ])</div>
            <div class="col-md-5">@include('livewire.partials.editable-field', [
                'label' => 'Last Name',
                'model' => 'booker_last_name',
                'val' => $booking->booker_last_name ?? '',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
                'valueColor' => '#332E9E',
            ])</div>

            <div class="col-12" style="margin:2px 0 6px;padding-top:10px;border-top:1px dashed rgba(51,46,158,.10);">
            </div>

            <div class="col-md-3">@include('livewire.partials.editable-field', [
                'label' => 'Mobile',
                'model' => 'booker_mobile',
                'val' => $booking->booker_mobile ?? '',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
            <div class="col-md-3">@include('livewire.partials.editable-field', [
                'label' => 'Landline',
                'model' => 'booker_landline',
                'val' => $booking->booker_landline ?? '',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
            <div class="col-md-6">@include('livewire.partials.editable-field', [
                'label' => 'Email',
                'model' => 'booker_email',
                'val' => $booking->booker_email ?? '',
                'type' => 'email',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>

            <div class="col-12" style="margin:2px 0 6px;padding-top:10px;border-top:1px dashed rgba(51,46,158,.10);">
            </div>

            <div class="col-md-8">@include('livewire.partials.editable-field', [
                'label' => 'Address',
                'model' => 'booker_address',
                'val' => $booking->booker_address ?? '',
                'type' => 'textarea',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
            <div class="col-md-4">@include('livewire.partials.editable-field', [
                'label' => 'Postcode',
                'model' => 'booker_postcode',
                'val' => $booking->booker_postcode ?? '',
                'locked' => !$isPrivileged,
                'editingVar' => 'sectionEditing',
            ])</div>
          </div>
        </div>
      </div>

      {{-- PASSENGERS --}}
      <div class="bv-section">
        <div class="bv-section-hdr">
          <div class="bv-icon" style="background:rgba(22,163,74,.08);"><i class="ph ph-users"
              style="color:#16A34A;font-size:1.08rem;"></i></div>
          <h2>Passengers</h2>
          <div class="d-flex align-items-center gap-1 ms-auto">
            @foreach (['adult' => ['ADT', '#332E9E'], 'gbe' => ['GBE', '#D83F87'], 'child' => ['CNN', '#D97706'], 'infant' => ['INF', '#16A34A']] as $type => [$code, $color])
              @php $count = ${$type.'Count'}; @endphp
              <div class="bv-pax-counter">
                <span class="bv-pax-type"
                  style="color:{{ $count > 0 ? $color : '#9CA3AF' }};">{{ $code }}</span>
                <button type="button" wire:click="dec('{{ $type }}')" class="bv-pax-btn"
                  style="background:transparent;border:1.5px solid {{ $count > 0 ? $color . '55' : '#D1D5DB' }};color:{{ $count > 0 ? $color : '#9CA3AF' }};{{ $isLocked ? 'opacity:.35;cursor:not-allowed;pointer-events:none;' : '' }}">-</button>
                <span class="bv-pax-num"
                  style="color:{{ $count > 0 ? $color : '#374151' }};">{{ $count }}</span>
                <button type="button" wire:click="inc('{{ $type }}')" class="bv-pax-btn"
                  style="background:{{ $color }};color:#fff;{{ $isLocked ? 'opacity:.35;cursor:not-allowed;pointer-events:none;' : '' }}">+</button>
              </div>
            @endforeach
            <div class="bv-pax-total">
              <div class="bv-pax-total-num">{{ $this->totalPassengers }}</div>
              <div class="bv-pax-total-label">pax</div>
            </div>
          </div>
        </div>
        @if ($this->totalPassengers > 0)
          <div class="bv-section-body" style="padding-top:8px;">
            <div class="table-responsive">
              <table class="table mb-0" style="font-size:0.912rem;">
                <thead>
                  <tr style="background:rgba(51,46,158,.03);">
                    @foreach (['#', 'Type', 'Name', 'DOB', 'Passport', 'E-Ticket'] as $h)
                      <th
                        style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.05em;color:#475569;font-weight:700;padding:8px 10px;border:none;white-space:nowrap;">
                        {{ $h }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach ($passengers as $i => $p)
                    @php
                      $ptc = !empty($p['date_of_birth']) ? $this->computePtc($p['date_of_birth'], $p['type']) : '';
                      $typeColor =
                          ['adult' => '#332E9E', 'gbe' => '#D83F87', 'child' => '#D97706', 'infant' => '#16A34A'][
                              $p['type']
                          ] ?? '#6B7280';
                      $name = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                    @endphp
                    <tr style="border-color:rgba(51,46,158,.04);{{ $loop->even ? 'background:#FAFBFF;' : '' }}"
                      x-data="{ open: false }">
                      <td style="padding:8px 10px;border-color:rgba(51,46,158,.04);color:#475569;font-weight:600;">
                        {{ $i + 1 }}</td>
                      <td style="padding:8px 10px;border-color:rgba(51,46,158,.04);"><span class="bv-pill"
                          style="background:{{ $typeColor }}15;color:{{ $typeColor }};font-size:0.72rem;">{{ $this->passengerTypeLabel($p['type']) }}</span>
                      </td>
                      <td style="padding:8px 10px;border-color:rgba(51,46,158,.04);">
                        <div class="d-flex align-items-center gap-2">
                          {{-- Name is plain text: only the pencil opens the edit panel. --}}
                          <span class="fw-semibold"
                            style="color:#1E293B;">{{ $name ?: $this->passengerTypeFullName($p['type']) . ' ' . $this->getPassengerNumber($i, $p['type']) }}</span>
                          @php
                            $ageInfo = $this->computeAgeInfo($p['date_of_birth'] ?? '');
                          @endphp
                          @if ($ptc)
                            <span
                              style="background:{{ ['ADT' => '#332E9E', 'GBE' => '#D83F87', 'CNN' => '#D97706', 'INF' => '#16A34A'][$ptc] ?? '#6B7280' }};color:#fff;border-radius:5px;font-size:0.696rem;font-weight:800;padding:1px 6px;">{{ $ptc }}</span>
                          @endif
                          @if ($ageInfo['years'] > 0 || $ageInfo['months'] > 0)
                            <span
                              style="background:rgba(51,46,158,0.05);color:#332E9E;border-radius:5px;font-size:0.696rem;font-weight:600;padding:1px 6px;white-space:nowrap;">
                              {{ $ageInfo['years'] }}y{{ $ageInfo['years'] < 2 ? ' ' . $ageInfo['months'] . 'm' : '' }}
                            </span>
                          @endif
                          @if (!empty($ageInfo['next_ptc']) && !empty($ageInfo['next_ptc_date']))
                            <span
                              style="background:rgba(51,46,158,0.08);color:#332E9E;border-radius:5px;font-size:0.696rem;font-weight:600;padding:1px 6px;white-space:nowrap;display:inline-flex;align-items:center;gap:2px;">
                              <i class="ph ph-arrow-up-right" style="font-size:0.624rem;"></i>
                              {{ str_replace(['Child (CNN)', 'Youth (GBE)', 'Adult (ADT)'], ['CNN', 'GBE', 'ADT'], $ageInfo['next_ptc']) }}
                              on {{ $ageInfo['next_ptc_date'] }}
                            </span>
                          @endif
                          @if (!$isLocked || $canEditEticket)
                            <button type="button" @click="open = !open" class="bv-edit-pencil"><i
                                class="ph ph-pencil-simple"></i></button>
                          @endif
                        </div>
                        <div x-show="open" x-cloak class="mt-2 p-3"
                          style="background:#F8FAFF;border-radius:10px;border:1px solid rgba(51,46,158,.08);">
                          <div class="row g-2">
                            @php $titleOpts = [['value'=>'','label'=>'-'],['value'=>'Mr.','label'=>'Mr.'],['value'=>'Ms.','label'=>'Ms.'],['value'=>'Mrs.','label'=>'Mrs.'],['value'=>'Mstr','label'=>'Mstr'],['value'=>'Miss','label'=>'Miss'],['value'=>'Dr.','label'=>'Dr.']]; @endphp
                            <div class="col-md-3"><label class="bv-label">Title</label><x-styled-select-sm
                                :modelName="'passengers.' . $i . '.title'" :options="$titleOpts" placeholder="-" :disabled="$isLocked" /></div>
                            <div class="col-md-5"><label class="bv-label">First Name</label><input type="text"
                                wire:model="passengers.{{ $i }}.first_name" class="bv-input-inline"
                                style="font-size:0.864rem;{{ $isLocked ? 'opacity:.45;pointer-events:none;' : '' }}">
                            </div>
                            <div class="col-md-4"><label class="bv-label">Last Name</label><input type="text"
                                wire:model="passengers.{{ $i }}.last_name" class="bv-input-inline"
                                style="font-size:0.864rem;{{ $isLocked ? 'opacity:.45;pointer-events:none;' : '' }}">
                            </div>
                            <div class="col-md-3"><label class="bv-label">DOB @if ($ageInfo['years'] > 0 || $ageInfo['months'] > 0)
                                  <span style="font-weight:400;color:#475569;">·
                                    {{ $ageInfo['years'] }}y{{ $ageInfo['years'] < 2 ? ' ' . $ageInfo['months'] . 'm' : '' }}</span>
                                @endif
                              </label>
                              <x-date-picker :modelName="'passengers.' . $i . '.date_of_birth'" :compact="true" />
                              @error("passengers.{$i}.date_of_birth")
                                <small style="color:#DC2626;font-size:0.744rem;">{{ $message }}</small>
                              @enderror
                            </div>
                            <div class="col-md-3"><label class="bv-label">Passport #</label><input type="text"
                                wire:model="passengers.{{ $i }}.passport_number" class="bv-input-inline"
                                style="font-size:0.864rem;{{ $isLocked ? 'opacity:.45;pointer-events:none;' : '' }}">
                            </div>
                            <div class="col-md-2"><label class="bv-label">Contact</label><input type="tel"
                                wire:model="passengers.{{ $i }}.contact_number" class="bv-input-inline"
                                style="font-size:0.864rem;{{ $isLocked ? 'opacity:.45;pointer-events:none;' : '' }}"
                                oninput="this.value=this.value.replace(/[^0-9+]/g,'')"></div>
                            <div class="col-md-4"><label class="bv-label">E-Ticket</label><input type="text"
                                wire:model="passengers.{{ $i }}.e_ticket_number" class="bv-input-inline"
                                style="font-size:0.864rem;{{ !$canEditEticket ? 'opacity:.45;pointer-events:none;' : '' }}"
                                placeholder="176-1234567890"></div>
                            @php $countryOpts = array_merge([['value'=>'','label'=>'-']], collect($countries)->map(fn($name,$code)=>['value'=>$code,'label'=>$name])->values()->toArray()); @endphp
                            <div class="col-md-3"><label class="bv-label">Issuing Country</label><x-styled-select-sm
                                :modelName="'passengers.' . $i . '.passport_issuing_country'" :options="$countryOpts" placeholder="-" :searchable="true"
                                :disabled="$isLocked" /></div>
                            <div class="col-md-3"><label class="bv-label">Nationality</label><x-styled-select-sm
                                :modelName="'passengers.' . $i . '.nationality'" :options="$countryOpts" placeholder="-" :searchable="true"
                                :disabled="$isLocked" /></div>
                          </div>
                          <button type="button" @click="open = false" class="mt-2"
                            style="border:none;background:rgba(51,46,158,.06);color:#332E9E;border-radius:6px;padding:3px 12px;cursor:pointer;font-size:0.84rem;font-weight:600;">Done</button>
                        </div>
                      </td>
                      <td
                        style="padding:8px 10px;border-color:rgba(51,46,158,.04);font-family:monospace;font-size:0.864rem;">
                        {{ $p['date_of_birth'] ? \Carbon\Carbon::parse($p['date_of_birth'])->format('d/m/Y') : '-' }}
                      </td>
                      <td
                        style="padding:8px 10px;border-color:rgba(51,46,158,.04);font-family:monospace;font-size:0.864rem;">
                        {{ $p['passport_number'] ?? '-' }}</td>
                      <td
                        style="padding:8px 10px;border-color:rgba(51,46,158,.04);font-family:monospace;font-size:0.84rem;color:#332E9E;">
                        {{ $p['e_ticket_number'] ?? '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @else
          <div class="bv-section-body text-center py-3">
            <p style="color:#475569;font-size:0.912rem;">No passengers added. Use counters above.</p>
          </div>
        @endif
      </div>

      {{-- FLIGHT / PNR --}}
      <div class="bv-section">
        <div class="bv-section-hdr">
          <div class="bv-icon" style="background:rgba(14,165,233,.08);"><i class="ph ph-airplane"
              style="color:#0EA5E9;font-size:1.08rem;"></i></div>
          <h2>Flight &amp; PNR</h2>
          @if ($fd)
            <span class="bv-pill ms-auto"
              style="background:rgba(14,165,233,.08);color:#0369A1;font-size:0.768rem;">{{ strtoupper($fd->departure_airport ?? '-') }}
              - {{ strtoupper($fd->arrival_airport ?? '-') }}</span>
          @endif
          @if ($canEditFlightHotel)
            <button type="button" wire:click="toggleFlightSectionEditing" class="bv-edit-pencil"
              style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;">{{ $flightSectionEditing ? 'Done' : 'Edit' }}</button>
          @endif
        </div>
        <div class="bv-section-body">
          @foreach ($flightSegments as $si => $seg)
            <div class="bv-seg-card" style="border-color:rgba(51,46,158,{{ $si === 0 ? '0.10' : '0.16' }});">
              <div class="bv-seg-hdr"
                style="background:{{ $si === 0 ? 'rgba(51,46,158,0.03)' : 'rgba(51,46,158,0.06)' }};">
                <span class="fw-bold" style="font-size:0.888rem;color:#332E9E;">{{ $this->getPnrLabel($si) }}
                  @if (!empty($seg['departure_airport']) && !empty($seg['arrival_airport']))
                    <span class="text-muted fw-normal" style="font-size:0.816rem;"> -
                      {{ strtoupper($seg['departure_airport']) }} - {{ strtoupper($seg['arrival_airport']) }}</span>
                  @endif
                </span>
                @if ($si > 0 && $canEditFlightHotel)
                  <button type="button" wire:click="removeFlightSegment({{ $si }})" class="btn btn-sm"
                    style="background:rgba(220,38,38,.08);color:#DC2626;border:none;border-radius:6px;font-size:0.792rem;padding:2px 8px;">Remove</button>
                @endif
              </div>
              <div class="bv-seg-body">
                <div class="row g-2 mb-2">
                  <div class="col-md-4">@include('livewire.partials.editable-field', [
                      'label' => 'Locator',
                      'model' => "flightSegments.{$si}.locator",
                      'val' => $seg['locator'] ?? '',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-4">@include('livewire.partials.editable-field', [
                      'label' => 'Airline Locator',
                      'model' => "flightSegments.{$si}.airline_locator",
                      'val' => $seg['airline_locator'] ?? '',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-4">@include('livewire.partials.editable-field', [
                      'label' => 'Vendor',
                      'model' => "flightSegments.{$si}.vendor",
                      'val' => $seg['vendor'] ?? '',
                      'type' => 'select',
                      'options' => $vendorOptions,
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-2">@include('livewire.partials.editable-field', [
                      'label' => 'Airline',
                      'model' => "flightSegments.{$si}.airline",
                      'val' => $seg['airline'] ? strtoupper($seg['airline']) : '',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-2">@include('livewire.partials.editable-field', [
                      'label' => 'GDS',
                      'model' => "flightSegments.{$si}.gds",
                      'val' => $seg['gds'] ?? '',
                      'type' => 'select',
                      'options' => $gdsOptions,
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-2">@include('livewire.partials.editable-field', [
                      'label' => 'Cabin',
                      'model' => "flightSegments.{$si}.cabin",
                      'val' => $seg['cabin'] ?? '',
                      'type' => 'select',
                      'options' => [
                          ['value' => 'Economy', 'label' => 'Economy'],
                          ['value' => 'Premium Economy', 'label' => 'Premium Economy'],
                          ['value' => 'Business', 'label' => 'Business'],
                          ['value' => 'First Class', 'label' => 'First Class'],
                      ],
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-3">@include('livewire.partials.editable-field', [
                      'label' => 'Reservation',
                      'model' => "flightSegments.{$si}.reservation_status",
                      'val' => $seg['reservation_status'] ?? '',
                      'type' => 'select',
                      'options' => [
                          ['value' => 'Confirmed', 'label' => 'Confirmed'],
                          ['value' => 'Ticketed', 'label' => 'Ticketed'],
                          ['value' => 'Pending', 'label' => 'Pending'],
                          ['value' => 'On Hold', 'label' => 'On Hold'],
                          ['value' => 'Cancelled', 'label' => 'Cancelled'],
                      ],
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-3">@include('livewire.partials.editable-field', [
                      'label' => 'Ticket Limit',
                      'model' => "flightSegments.{$si}.ticket_issue_limit",
                      'val' => $seg['ticket_issue_limit']
                          ? \Carbon\Carbon::parse($seg['ticket_issue_limit'])->format('d M Y, H:i')
                          : '',
                      'type' => 'datetime-local',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                </div>
                <div class="row g-2 mb-2">
                  <div class="col-md-2">@include('livewire.partials.editable-field', [
                      'label' => 'Dep. Airport',
                      'model' => "flightSegments.{$si}.departure_airport",
                      'val' => $seg['departure_airport'] ? strtoupper($seg['departure_airport']) : '',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-2">@include('livewire.partials.editable-field', [
                      'label' => 'Arr. Airport',
                      'model' => "flightSegments.{$si}.arrival_airport",
                      'val' => $seg['arrival_airport'] ? strtoupper($seg['arrival_airport']) : '',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-2">@include('livewire.partials.editable-field', [
                      'label' => 'Flight Type',
                      'model' => "flightSegments.{$si}.flight_type",
                      'val' => ($seg['flight_type'] ?? 'return') === 'one_way' ? 'One Way' : 'Return',
                      'type' => 'select',
                      'options' => [
                          ['value' => 'return', 'label' => 'Return'],
                          ['value' => 'one_way', 'label' => 'One Way'],
                      ],
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  <div class="col-md-3">@include('livewire.partials.editable-field', [
                      'label' => 'Dep. Date',
                      'model' => "flightSegments.{$si}.departure_date",
                      'val' => $seg['departure_date']
                          ? \Carbon\Carbon::parse($seg['departure_date'])->format('d M Y')
                          : '',
                      'type' => 'date',
                      'locked' => !$canEditFlightHotel,
                      'phpEditing' => $flightSectionEditing,
                  ])</div>
                  @if (($seg['flight_type'] ?? 'return') !== 'one_way')
                    <div class="col-md-3">@include('livewire.partials.editable-field', [
                        'label' => 'Return Date',
                        'model' => "flightSegments.{$si}.return_date",
                        'val' => $seg['return_date']
                            ? \Carbon\Carbon::parse($seg['return_date'])->format('d M Y')
                            : '',
                        'type' => 'date',
                        'locked' => !$canEditFlightHotel,
                        'phpEditing' => $flightSectionEditing,
                    ])</div>
                  @endif
                </div>
                {{-- Passenger Pricing grouped by type --}}
                @if (count($passengers) > 0)
                  @php
                    $pPtcOrder = ['adult', 'gbe', 'child', 'infant'];
                    $pPtcLabels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
                    $pPtcColors = [
                        'adult' => '#332E9E',
                        'gbe' => '#D83F87',
                        'child' => '#D97706',
                        'infant' => '#16A34A',
                    ];
                    $pPtcBg = [
                        'adult' => 'rgba(51,46,158,.07)',
                        'gbe' => 'rgba(216,63,135,.07)',
                        'child' => 'rgba(217,119,6,.07)',
                        'infant' => 'rgba(22,163,74,.07)',
                    ];
                    // First passenger index per type (editing wires to this; updatedFlightSegments syncs the rest)
                    $pTypeFirstIdx = [];
                    foreach ($passengers as $pi => $p) {
                        $t = $p['type'] ?? 'adult';
                        if (!isset($pTypeFirstIdx[$t])) {
                            $pTypeFirstIdx[$t] = $pi;
                        }
                    }
                  @endphp
                  <div class="mb-3 pb-3" style="border-bottom:2px solid rgba(51,46,158,.07);">
                    <div
                      style="font-size:0.696rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#475569;margin-bottom:8px;">
                      Cost per Passenger Type</div>
                    @foreach ($pPtcOrder as $pType)
                      @if (isset($pTypeFirstIdx[$pType]))
                        @php
                          $pi = $pTypeFirstIdx[$pType];
                          $pColor = $pPtcColors[$pType];
                          $pBg = $pPtcBg[$pType];
                          $pLabel = $pPtcLabels[$pType];
                        @endphp
                        <div
                          style="display:grid;grid-template-columns:1fr 120px 120px;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid rgba(51,46,158,.04);">
                          <span
                            style="padding:2px 9px;border-radius:20px;background:{{ $pBg }};color:{{ $pColor }};font-size:0.744rem;font-weight:700;display:inline-block;width:fit-content;">{{ $pLabel }}</span>
                          <div>
                            <div style="font-size:0.672rem;color:#475569;margin-bottom:2px;">Cost</div>
                            @if ($canEditFlightHotel)
                              @if (!$flightSectionEditing)
                                <div><span
                                    style="font-size:0.84rem;font-weight:600;color:#374151;">&pound;{{ number_format((float) ($seg['passenger_costs'][$pi]['cost'] ?? 0), 2) }}</span>
                                </div>
                              @else<div><input type="number"
                                    wire:model.blur="flightSegments.{{ $si }}.passenger_costs.{{ $pi }}.cost"
                                    step="0.01" min="0" class="bv-input-inline"
                                    style="font-size:0.84rem;padding:3px 6px;width:100%;" placeholder="0.00"></div>
                              @endif
                            @else
                              <span
                                style="font-size:0.84rem;font-weight:600;color:#374151;">&pound;{{ number_format((float) ($seg['passenger_costs'][$pi]['cost'] ?? 0), 2) }}</span>
                            @endif
                          </div>
                          <div>
                            <div style="font-size:0.672rem;color:#475569;margin-bottom:2px;">Sold</div>
                            @if ($canEditFlightHotel)
                              @if (!$flightSectionEditing)
                                <div><span
                                    style="font-size:0.84rem;font-weight:700;color:#111827;">&pound;{{ number_format((float) ($seg['passenger_costs'][$pi]['sold'] ?? 0), 2) }}</span>
                                </div>
                              @else<div><input type="number"
                                    wire:model.blur="flightSegments.{{ $si }}.passenger_costs.{{ $pi }}.sold"
                                    step="0.01" min="0" class="bv-input-inline"
                                    style="font-size:0.84rem;padding:3px 6px;width:100%;" placeholder="0.00"></div>
                              @endif
                            @else
                              <span
                                style="font-size:0.84rem;font-weight:700;color:#111827;">&pound;{{ number_format((float) ($seg['passenger_costs'][$pi]['sold'] ?? 0), 2) }}</span>
                            @endif
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                @endif
                <label class="bv-label">PNR Details</label>
                @if ($canEditFlightHotel && $flightSectionEditing)
                  <div class="d-flex gap-2 align-items-start">
                    <textarea wire:model.lazy="flightSegments.{{ $si }}.pnr" rows="12"
                      class="form-control form-control-sm flex-grow-1"
                      style="border-radius:10px;font-family:monospace;font-size:0.864rem;resize:vertical;min-height:180px;"
                      placeholder="Paste PNR here - RP/LONBA1234..."></textarea><button type="button" class="btn btn-sm fw-semibold flex-shrink-0"
                      style="background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:9px;font-size:0.84rem;white-space:nowrap;padding:5px 10px;">Fetch
                      PNR</button>
                  </div>
                @else
                  <div
                    style="font-family:monospace;font-size:0.888rem;color:#1E293B;background:#F8FAFF;border-radius:10px;padding:10px;min-height:100px;white-space:pre-wrap;border:1px solid rgba(51,46,158,.06);">
                    {{ $seg['pnr'] ?? 'No PNR recorded' }}</div>
                @endif
              </div>
            </div>
          @endforeach
          @if ($canAddPNR)
            <button type="button" wire:click="addFlightSegment"
              class="btn btn-sm d-flex align-items-center gap-1 w-100 justify-content-center"
              style="background:rgba(51,46,158,.05);color:#332E9E;border:1.5px dashed rgba(51,46,158,.2);border-radius:10px;padding:8px;font-size:0.888rem;font-weight:600;"><i
                class="ph ph-plus-circle"></i> Add Another PNR</button>
          @endif
          @if ($canAddHotel || $canAddVisa || $canAddTransfer || $canAddExcursion)
            <div class="d-flex gap-2 flex-wrap {{ $canAddPNR ? 'mt-2' : '' }} w-100">
              @if ($canAddHotel)
                <button type="button" wire:click="addHotel"
                  class="btn btn-sm d-flex align-items-center gap-1 flex-grow-1 justify-content-center"
                  style="background:rgba(124,58,237,.06);color:#7C3AED;border:1.5px solid rgba(124,58,237,.2);border-radius:9px;padding:6px 14px;font-size:0.864rem;font-weight:600;"><i
                    class="ph ph-buildings"></i> Add Hotel</button>
              @endif
              @if ($canAddVisa)
                <button type="button" wire:click="addVisa"
                  class="btn btn-sm d-flex align-items-center gap-1 flex-grow-1 justify-content-center"
                  style="background:rgba(22,163,74,.06);color:#16A34A;border:1.5px solid rgba(22,163,74,.2);border-radius:9px;padding:6px 14px;font-size:0.864rem;font-weight:600;"><i
                    class="ph ph-identification-card"></i> Add Visa</button>
              @endif
              @if ($canAddTransfer)
                <button type="button" wire:click="addPickup"
                  class="btn btn-sm d-flex align-items-center gap-1 flex-grow-1 justify-content-center"
                  style="background:rgba(51,46,158,.06);color:#332E9E;border:1.5px solid rgba(51,46,158,.2);border-radius:9px;padding:6px 14px;font-size:0.864rem;font-weight:600;"><i
                    class="ph ph-van"></i> Add Transfer</button>
              @endif
              @if ($canAddExcursion)
                <button type="button" wire:click="addExcursion"
                  class="btn btn-sm d-flex align-items-center gap-1 flex-grow-1 justify-content-center"
                  style="background:rgba(255,107,53,.06);color:#FF6B35;border:1.5px solid rgba(255,107,53,.2);border-radius:9px;padding:6px 14px;font-size:0.864rem;font-weight:600;"><i
                    class="ph ph-binoculars"></i> Add Excursion</button>
              @endif
            </div>
          @endif
          @if ($canEditFlightHotel)
            <div class="d-flex align-items-center gap-3 mt-2 pt-2" style="border-top:1px solid rgba(51,46,158,.06);">
              @if ($flightSectionEditing)
                <div>
                  <label class="d-flex align-items-center gap-1 mb-0" style="cursor:pointer;">
                    <div class="form-check form-switch mb-0 ps-0"><input type="checkbox"
                        wire:model.live="flight_atol" role="switch" class="form-check-input ms-0"
                        style="width:28px;height:15px;cursor:pointer;"></div><span
                      style="font-size:0.84rem;font-weight:700;color:{{ $flight_atol ? '#FF6B35' : '#374151' }};">ATOL</span>
                  </label>
                  <label class="d-flex align-items-center gap-1 mb-0" style="cursor:pointer;">
                    <div class="form-check form-switch mb-0 ps-0"><input type="checkbox"
                        wire:model.live="flight_safi" role="switch" class="form-check-input ms-0"
                        style="width:28px;height:15px;cursor:pointer;"></div><span
                      style="font-size:0.84rem;font-weight:700;color:{{ $flight_safi ? '#332E9E' : '#374151' }};">SAFI</span>
                  </label>
                </div>
              @else
                <div>
                  <span
                    style="font-size:0.84rem;font-weight:700;color:{{ $flight_atol ? '#FF6B35' : '#94A3B8' }};">{{ $flight_atol ? 'ATOL' : '— ATOL' }}</span>
                  <span
                    style="font-size:0.84rem;font-weight:700;color:{{ $flight_safi ? '#332E9E' : '#94A3B8' }};margin-left:12px;">{{ $flight_safi ? 'SAFI' : '— SAFI' }}</span>
                </div>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- VISA section --}}
      @if (count($visas) > 0)
        <div class="bv-section" x-data="{ sectionEditing: false }">
          <div class="bv-section-hdr">
            <div class="bv-icon" style="background:rgba(22,163,74,.08);"><i class="ph ph-identification-card"
                style="color:#16A34A;font-size:1.08rem;"></i></div>
            <h2>Visa</h2>
            <span class="bv-pill ms-auto"
              style="background:rgba(22,163,74,.08);color:#16A34A;font-size:0.768rem;">{{ count($visas) }}
              visa{{ count($visas) !== 1 ? 's' : '' }}</span>
            @if ($canEditFlightHotel)
              <button type="button" @click="sectionEditing = !sectionEditing" class="bv-edit-pencil"
                style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;"
                x-text="sectionEditing ? 'Done' : 'Edit'"></button>
            @endif
          </div>
          @foreach ($visas as $vi => $visa)
            <div wire:key="visa-{{ $vi }}" class="bv-section-body"
              style="{{ !$loop->last ? 'border-bottom:1px solid rgba(22,163,74,.06);' : '' }}">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:0.816rem;font-weight:700;color:#16A34A;">Visa {{ $vi + 1 }}</span>
                @if ($canEditFlightHotel)
                  <button type="button" wire:click="removeVisa({{ $vi }})"
                    style="background:rgba(220,38,38,.08);color:#DC2626;border:none;border-radius:6px;font-size:0.792rem;padding:2px 8px;cursor:pointer;">Remove</button>
                @endif
              </div>
              <div class="row g-2 mb-2">
                <div class="col-md-4">@include('livewire.partials.editable-field', [
                    'label' => 'Passenger Name',
                    'model' => "visas.{$vi}.passenger_name",
                    'val' => $visa['passenger_name'] ?? '',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-4">@include('livewire.partials.editable-field', [
                    'label' => 'Visa Type',
                    'model' => "visas.{$vi}.visa_type",
                    'val' => ucfirst($visa['visa_type'] ?? 'tourist'),
                    'type' => 'select',
                    'options' => [
                        ['value' => 'umrah', 'label' => 'Umrah'],
                        ['value' => 'tourist', 'label' => 'Tourist'],
                        ['value' => 'business', 'label' => 'Business'],
                        ['value' => 'transit', 'label' => 'Transit'],
                        ['value' => 'student', 'label' => 'Student'],
                    ],
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-4">@include('livewire.partials.editable-field', [
                    'label' => 'Status',
                    'model' => "visas.{$vi}.status",
                    'val' => ucfirst($visa['status'] ?? 'pending'),
                    'type' => 'select',
                    'options' => [
                        ['value' => 'pending', 'label' => 'Pending'],
                        ['value' => 'applied', 'label' => 'Applied'],
                        ['value' => 'approved', 'label' => 'Approved'],
                        ['value' => 'rejected', 'label' => 'Rejected'],
                        ['value' => 'collected', 'label' => 'Collected'],
                    ],
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
              </div>
              <div class="row g-2 mb-2">
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Visa Reference',
                    'model' => "visas.{$vi}.visa_reference",
                    'val' => $visa['visa_reference'] ?? '',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Visa Number',
                    'model' => "visas.{$vi}.visa_number",
                    'val' => $visa['visa_number'] ?? '',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Application Date',
                    'model' => "visas.{$vi}.application_date",
                    'val' => $visa['application_date']
                        ? \Carbon\Carbon::parse($visa['application_date'])->format('d M Y')
                        : '',
                    'type' => 'date',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Expiry Date',
                    'model' => "visas.{$vi}.expiry_date",
                    'val' => $visa['expiry_date']
                        ? \Carbon\Carbon::parse($visa['expiry_date'])->format('d M Y')
                        : '',
                    'type' => 'date',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
              </div>
              <div class="row g-2">
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Cost (£)',
                    'model' => "visas.{$vi}.actual_cost",
                    'val' => $visa['actual_cost'] ? '£' . number_format((float) $visa['actual_cost'], 2) : '',
                    'type' => 'cost',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Selling (£)',
                    'model' => "visas.{$vi}.selling_price",
                    'val' => $visa['selling_price'] ? '£' . number_format((float) $visa['selling_price'], 2) : '',
                    'type' => 'cost',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-6">@include('livewire.partials.editable-field', [
                    'label' => 'Notes',
                    'model' => "visas.{$vi}.notes",
                    'val' => $visa['notes'] ?? '',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
              </div>
            </div>
          @endforeach
          @if ($canEditFlightHotel)
            <div class="px-4 pb-3">
              <button type="button" wire:click="addVisa"
                class="btn btn-sm d-flex align-items-center gap-1 w-100 justify-content-center"
                style="background:rgba(22,163,74,.05);color:#16A34A;border:1.5px dashed rgba(22,163,74,.2);border-radius:10px;padding:7px;font-size:0.864rem;font-weight:600;"><i
                  class="ph ph-plus-circle"></i> Add Another Visa</button>
            </div>
          @endif
        </div>
      @endif

      {{-- EXCURSION section --}}
      @if (($booking_type === 'excursion' || $booking_type === 'holiday') && $showExcursion)
        <div class="bv-section" x-data="{ sectionEditing: false }">
          <div class="bv-section-hdr">
            <div class="bv-icon" style="background:rgba(255,107,53,.08);"><i class="ph ph-binoculars"
                style="color:#FF6B35;font-size:1.08rem;"></i></div>
            <h2>Excursion</h2>
            @if (!empty($excursion_status))
              <span class="bv-pill ms-auto"
                style="background:rgba(255,107,53,.08);color:#FF6B35;font-size:0.768rem;">{{ ucfirst($excursion_status) }}</span>
            @endif
            @if ($canEditFlightHotel)
              <button type="button" @click="sectionEditing = !sectionEditing" class="bv-edit-pencil"
                style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;"
                x-text="sectionEditing ? 'Done' : 'Edit'"></button>
            @endif
          </div>
          <div class="bv-section-body">
            <div class="row g-2 mb-2">
              <div class="col-md-6">@include('livewire.partials.editable-field', [
                  'label' => 'Excursion Name',
                  'model' => 'excursion_name',
                  'val' => $excursion_name,
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
              <div class="col-md-3">@include('livewire.partials.editable-field', [
                  'label' => 'Destination',
                  'model' => 'excursion_destination',
                  'val' => $excursion_destination,
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
              <div class="col-md-3">@include('livewire.partials.editable-field', [
                  'label' => 'Supplier',
                  'model' => 'excursion_supplier',
                  'val' => $excursion_supplier,
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
            </div>
            <div class="row g-2">
              <div class="col-md-3">@include('livewire.partials.editable-field', [
                  'label' => 'Date',
                  'model' => 'excursion_date',
                  'val' => $excursion_date ? \Carbon\Carbon::parse($excursion_date)->format('d M Y') : '',
                  'type' => 'date',
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
              <div class="col-md-3">@include('livewire.partials.editable-field', [
                  'label' => 'Cost (£)',
                  'model' => 'excursion_actual_cost',
                  'val' => $excursion_actual_cost ? '£' . number_format((float) $excursion_actual_cost, 2) : '',
                  'type' => 'cost',
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
              <div class="col-md-3">@include('livewire.partials.editable-field', [
                  'label' => 'Selling (£)',
                  'model' => 'excursion_selling_price',
                  'val' => $excursion_selling_price
                      ? '£' . number_format((float) $excursion_selling_price, 2)
                      : '',
                  'type' => 'cost',
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
              <div class="col-md-3">@include('livewire.partials.editable-field', [
                  'label' => 'Status',
                  'model' => 'excursion_status',
                  'val' => ucfirst($excursion_status ?? 'confirmed'),
                  'type' => 'select',
                  'options' => [
                      ['value' => 'confirmed', 'label' => 'Confirmed'],
                      ['value' => 'pending', 'label' => 'Pending'],
                      ['value' => 'cancelled', 'label' => 'Cancelled'],
                  ],
                  'locked' => !$canEditFlightHotel,
                  'editingVar' => 'sectionEditing',
              ])</div>
            </div>
          </div>
        </div>
      @endif

      {{-- HOTEL --}}
      @if (!empty($hotels))
        <div class="bv-section" x-data="{ sectionEditing: false }">
          <div class="bv-section-hdr">
            <div class="bv-icon" style="background:rgba(124,58,237,.08);"><i class="ph ph-buildings"
                style="color:#7C3AED;font-size:1.08rem;"></i></div>
            <h2>Hotel</h2>
            <span class="bv-pill ms-auto"
              style="background:rgba(124,58,237,.08);color:#7C3AED;font-size:0.768rem;">{{ count($hotels) }}
              hotel{{ count($hotels) !== 1 ? 's' : '' }}</span>
            @if ($canEditFlightHotel)
              <button type="button" @click="sectionEditing = !sectionEditing" class="bv-edit-pencil"
                style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;"
                x-text="sectionEditing ? 'Done' : 'Edit'"></button>
            @endif
          </div>
          @foreach ($hotels as $hi => $hotel)
            <div wire:key="show-hotel-{{ $hi }}" class="bv-section-body"
              style="{{ !$loop->last ? 'padding-bottom:8px;' : '' }}">
              <div class="row g-2 mb-2">
                <div class="col-md-4">@include('livewire.partials.editable-field', [
                    'label' => 'Hotel Name',
                    'model' => "hotels.{$hi}.hotel_name",
                    'val' => $hotel['hotel_name'] ?? '',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-2">@include('livewire.partials.editable-field', [
                    'label' => 'City',
                    'model' => "hotels.{$hi}.city",
                    'val' => $hotel['city'] ?? '',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-2">@include('livewire.partials.editable-field', [
                    'label' => 'Status',
                    'model' => "hotels.{$hi}.status",
                    'val' => ucfirst($hotel['status'] ?? ''),
                    'type' => 'select',
                    'options' => [
                        ['value' => 'confirmed', 'label' => 'Confirmed'],
                        ['value' => 'on_holding', 'label' => 'On Holding'],
                        ['value' => 'cancelled', 'label' => 'Cancelled'],
                    ],
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-2">@include('livewire.partials.editable-field', [
                    'label' => 'Check In',
                    'model' => "hotels.{$hi}.check_in",
                    'val' => $hotel['check_in'] ? \Carbon\Carbon::parse($hotel['check_in'])->format('d M Y') : '',
                    'type' => 'date',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-2">@include('livewire.partials.editable-field', [
                    'label' => 'Check Out',
                    'model' => "hotels.{$hi}.check_out",
                    'val' => $hotel['check_out']
                        ? \Carbon\Carbon::parse($hotel['check_out'])->format('d M Y')
                        : '',
                    'type' => 'date',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
              </div>
              <div class="row g-2 mb-2">
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Cost (£)',
                    'model' => "hotels.{$hi}.actual_cost",
                    'val' => $hotel['actual_cost'] ? '£' . number_format((float) $hotel['actual_cost'], 2) : '',
                    'type' => 'cost',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-3">@include('livewire.partials.editable-field', [
                    'label' => 'Selling (£)',
                    'model' => "hotels.{$hi}.selling_price",
                    'val' => $hotel['selling_price']
                        ? '£' . number_format((float) $hotel['selling_price'], 2)
                        : '',
                    'type' => 'cost',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                ])</div>
                <div class="col-md-2">@include('livewire.partials.editable-field', [
                    'label' => 'Rooms',
                    'model' => "hotels.{$hi}.number_of_rooms",
                    'val' => $hotel['number_of_rooms'] ?? '1',
                    'type' => 'rooms',
                    'locked' => !$canEditFlightHotel,
                    'editingVar' => 'sectionEditing',
                    'rawModel' => "hotels.{$hi}.number_of_rooms",
                ])</div>
              </div>
              @foreach ($hotel['rooms'] as $ri => $room)
                <div wire:key="show-hotel-{{ $hi }}-room-{{ $ri }}" class="p-2 mb-1"
                  style="background:rgba(124,58,237,.02);border-radius:8px;border:1px solid rgba(124,58,237,.05);">
                  <div class="row g-2">
                    <div class="col-md-4">@include('livewire.partials.editable-field', [
                        'label' => 'Room ' . ($ri + 1) . ' Type',
                        'model' => "hotels.{$hi}.rooms.{$ri}.room_type",
                        'val' => $room['room_type'] ?? '',
                        'locked' => !$canEditFlightHotel,
                        'editingVar' => 'sectionEditing',
                    ])</div>
                    <div class="col-md-3">@include('livewire.partials.editable-field', [
                        'label' => 'Occupants',
                        'model' => "hotels.{$hi}.rooms.{$ri}.occupants",
                        'val' => $room['occupants'] ?? '',
                        'locked' => !$canEditFlightHotel,
                        'editingVar' => 'sectionEditing',
                    ])</div>
                    <div class="col-md-5">@include('livewire.partials.editable-field', [
                        'label' => 'Meal Basis',
                        'model' => "hotels.{$hi}.rooms.{$ri}.meal_basis",
                        'val' => ucwords(str_replace('_', ' ', $room['meal_basis'] ?? '')),
                        'type' => 'select',
                        'options' => [
                            ['value' => 'room_only', 'label' => 'Room Only'],
                            ['value' => 'breakfast', 'label' => 'Breakfast'],
                            ['value' => 'half_board', 'label' => 'Half Board'],
                            ['value' => 'full_board', 'label' => 'Full Board'],
                            ['value' => 'all_inclusive', 'label' => 'All Inclusive'],
                        ],
                        'locked' => !$canEditFlightHotel,
                        'editingVar' => 'sectionEditing',
                    ])</div>
                  </div>
                </div>
              @endforeach
              @if ($canEditFlightHotel)
                <div class="d-flex justify-content-end mt-1"><button type="button"
                    wire:click="removeHotel({{ $hi }})" class="btn btn-sm"
                    style="background:rgba(220,38,38,.06);color:#DC2626;border:none;border-radius:6px;font-size:0.792rem;padding:2px 10px;">Remove
                    Hotel</button></div>
              @endif
              @if (!$loop->last)
                <div class="bv-divider"></div>
              @endif
            </div>
          @endforeach
        </div>
      @endif

      {{-- TRANSFERS --}}
      @if (!empty($transferPickups) || !empty($transferDropoffs))
        <div class="bv-section" x-data="{ sectionEditing: false }">
          <div class="bv-section-hdr">
            <div class="bv-icon" style="background:rgba(51,46,158,.08);"><i class="ph ph-van"
                style="color:#332E9E;font-size:1.08rem;"></i></div>
            <h2>Transfers</h2>
            <span class="bv-pill ms-auto"
              style="background:rgba(51,46,158,.08);color:#332E9E;font-size:0.768rem;">{{ count($transferPickups) }}
              pickup / {{ count($transferDropoffs) }} dropoff</span>
            @if ($canEditFlightHotel)
              <button type="button" @click="sectionEditing = !sectionEditing" class="bv-edit-pencil"
                style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;"
                x-text="sectionEditing ? 'Done' : 'Edit'"></button>
            @endif
          </div>
          <div class="bv-section-body">
            @php
              $statusColors = ['confirmed' => '#16A34A', 'pending' => '#D97706', 'cancelled' => '#DC2626'];
              $vehicleIcons = [
                  'Minicab' => 'ph-car',
                  'Executive Car' => 'ph-car',
                  'Minibus' => 'ph-van',
                  'Coach' => 'ph-bus',
                  'Limo' => 'ph-car-profile',
                  'Other' => 'ph-van',
              ];
            @endphp

            {{-- Pickups --}}
            @if (!empty($transferPickups))
              <div
                style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#332E9E;margin-bottom:6px;display:flex;align-items:center;gap:5px;">
                <i class="ph ph-arrow-up-right"></i> Pickups
              </div>
              @foreach ($transferPickups as $ti => $t)
                <div wire:key="bv-pickup-{{ $ti }}" class="mb-2"
                  style="background:rgba(51,46,158,.02);border-radius:10px;border:1px solid rgba(51,46,158,.08);overflow:hidden;">
                  <div
                    style="padding:7px 12px;background:rgba(51,46,158,.04);border-bottom:1px solid rgba(51,46,158,.06);display:flex;align-items:center;justify-content:space-between;">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ph ph-map-pin-line" style="color:#332E9E;font-size:0.96rem;"></i>
                      <span
                        style="font-size:0.864rem;font-weight:700;color:#332E9E;">{{ $t['location'] ?: 'Pickup ' . ($ti + 1) }}</span>
                      @if (!empty($t['status']))
                        <span
                          style="font-size:0.696rem;font-weight:700;color:{{ $statusColors[$t['status']] ?? '#64748B' }};background:{{ $statusColors[$t['status']] . '15' ?? 'rgba(148,163,184,.08)' }};padding:1px 7px;border-radius:10px;text-transform:capitalize;">{{ $t['status'] }}</span>
                      @endif
                    </div>
                    @if ($canEditFlightHotel)
                      <button type="button" wire:click="removePickup({{ $ti }})"
                        style="background:rgba(220,38,38,.07);color:#DC2626;border:none;border-radius:5px;font-size:0.756rem;padding:2px 7px;cursor:pointer;">Remove</button>
                    @endif
                  </div>
                  <div class="p-2">
                    <div class="row g-2 mb-1">
                      <div class="col-md-6">@include('livewire.partials.editable-field', [
                          'label' => 'Location',
                          'model' => "transferPickups.{$ti}.location",
                          'val' => $t['location'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Date & Time',
                          'model' => "transferPickups.{$ti}.date_time",
                          'val' => $t['date_time']
                              ? \Carbon\Carbon::parse($t['date_time'])->format('d M Y H:i')
                              : '',
                          'type' => 'datetime-local',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Flight #',
                          'model' => "transferPickups.{$ti}.flight_number",
                          'val' => $t['flight_number'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                    </div>
                    <div class="row g-2 mb-1">
                      <div class="col-md-6">@include('livewire.partials.editable-field', [
                          'label' => 'Route',
                          'model' => "transferPickups.{$ti}.route",
                          'val' => $t['route'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Vehicle',
                          'model' => "transferPickups.{$ti}.vehicle_type",
                          'val' => $t['vehicle_type'] ?? '',
                          'type' => 'select',
                          'options' => [
                              ['value' => 'Minicab', 'label' => 'Minicab'],
                              ['value' => 'Executive Car', 'label' => 'Executive Car'],
                              ['value' => 'Minibus', 'label' => 'Minibus'],
                              ['value' => 'Coach', 'label' => 'Coach'],
                              ['value' => 'Limo', 'label' => 'Limousine'],
                              ['value' => 'Other', 'label' => 'Other'],
                          ],
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Supplier',
                          'model' => "transferPickups.{$ti}.supplier",
                          'val' => $t['supplier'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                    </div>
                    <div class="row g-2">
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Cost (£)',
                          'model' => "transferPickups.{$ti}.actual_cost",
                          'val' =>
                              $t['actual_cost'] !== '' && $t['actual_cost'] !== null
                                  ? '£' . number_format((float) $t['actual_cost'], 2)
                                  : '',
                          'type' => 'cost',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Selling (£)',
                          'model' => "transferPickups.{$ti}.selling_price",
                          'val' =>
                              $t['selling_price'] !== '' && $t['selling_price'] !== null
                                  ? '£' . number_format((float) $t['selling_price'], 2)
                                  : '',
                          'type' => 'cost',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Status',
                          'model' => "transferPickups.{$ti}.status",
                          'val' => ucfirst($t['status'] ?? 'confirmed'),
                          'type' => 'select',
                          'options' => [
                              ['value' => 'confirmed', 'label' => 'Confirmed'],
                              ['value' => 'pending', 'label' => 'Pending'],
                              ['value' => 'cancelled', 'label' => 'Cancelled'],
                          ],
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Notes',
                          'model' => "transferPickups.{$ti}.notes",
                          'val' => $t['notes'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                    </div>
                  </div>
                </div>
              @endforeach
              @if ($canEditFlightHotel)
                <button type="button" wire:click="addPickup" class="btn btn-sm d-flex align-items-center gap-1 mb-3"
                  style="background:rgba(51,46,158,.05);color:#332E9E;border:1.5px dashed rgba(51,46,158,.18);border-radius:8px;padding:5px 14px;font-size:0.84rem;font-weight:600;"><i
                    class="ph ph-plus-circle"></i> Add Pickup</button>
              @endif
            @endif

            {{-- Dropoffs --}}
            @if (!empty($transferDropoffs))
              <div
                style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#D83F87;margin-bottom:6px;display:flex;align-items:center;gap:5px;">
                <i class="ph ph-arrow-down-right"></i> Dropoffs
              </div>
              @foreach ($transferDropoffs as $ti => $t)
                <div wire:key="bv-dropoff-{{ $ti }}" class="mb-2"
                  style="background:rgba(216,63,135,.02);border-radius:10px;border:1px solid rgba(216,63,135,.10);overflow:hidden;">
                  <div
                    style="padding:7px 12px;background:rgba(216,63,135,.04);border-bottom:1px solid rgba(216,63,135,.06);display:flex;align-items:center;justify-content:space-between;">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ph ph-map-pin" style="color:#D83F87;font-size:0.96rem;"></i>
                      <span
                        style="font-size:0.864rem;font-weight:700;color:#D83F87;">{{ $t['location'] ?: 'Dropoff ' . ($ti + 1) }}</span>
                      @if (!empty($t['status']))
                        <span
                          style="font-size:0.696rem;font-weight:700;color:{{ $statusColors[$t['status']] ?? '#64748B' }};background:{{ $statusColors[$t['status']] . '15' ?? 'rgba(148,163,184,.08)' }};padding:1px 7px;border-radius:10px;text-transform:capitalize;">{{ $t['status'] }}</span>
                      @endif
                    </div>
                    @if ($canEditFlightHotel)
                      <button type="button" wire:click="removeDropoff({{ $ti }})"
                        style="background:rgba(220,38,38,.07);color:#DC2626;border:none;border-radius:5px;font-size:0.756rem;padding:2px 7px;cursor:pointer;">Remove</button>
                    @endif
                  </div>
                  <div class="p-2">
                    <div class="row g-2 mb-1">
                      <div class="col-md-6">@include('livewire.partials.editable-field', [
                          'label' => 'Location',
                          'model' => "transferDropoffs.{$ti}.location",
                          'val' => $t['location'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Date & Time',
                          'model' => "transferDropoffs.{$ti}.date_time",
                          'val' => $t['date_time']
                              ? \Carbon\Carbon::parse($t['date_time'])->format('d M Y H:i')
                              : '',
                          'type' => 'datetime-local',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Flight #',
                          'model' => "transferDropoffs.{$ti}.flight_number",
                          'val' => $t['flight_number'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                    </div>
                    <div class="row g-2 mb-1">
                      <div class="col-md-6">@include('livewire.partials.editable-field', [
                          'label' => 'Route',
                          'model' => "transferDropoffs.{$ti}.route",
                          'val' => $t['route'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Vehicle',
                          'model' => "transferDropoffs.{$ti}.vehicle_type",
                          'val' => $t['vehicle_type'] ?? '',
                          'type' => 'select',
                          'options' => [
                              ['value' => 'Minicab', 'label' => 'Minicab'],
                              ['value' => 'Executive Car', 'label' => 'Executive Car'],
                              ['value' => 'Minibus', 'label' => 'Minibus'],
                              ['value' => 'Coach', 'label' => 'Coach'],
                              ['value' => 'Limo', 'label' => 'Limousine'],
                              ['value' => 'Other', 'label' => 'Other'],
                          ],
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Supplier',
                          'model' => "transferDropoffs.{$ti}.supplier",
                          'val' => $t['supplier'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                    </div>
                    <div class="row g-2">
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Cost (£)',
                          'model' => "transferDropoffs.{$ti}.actual_cost",
                          'val' =>
                              $t['actual_cost'] !== '' && $t['actual_cost'] !== null
                                  ? '£' . number_format((float) $t['actual_cost'], 2)
                                  : '',
                          'type' => 'cost',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Selling (£)',
                          'model' => "transferDropoffs.{$ti}.selling_price",
                          'val' =>
                              $t['selling_price'] !== '' && $t['selling_price'] !== null
                                  ? '£' . number_format((float) $t['selling_price'], 2)
                                  : '',
                          'type' => 'cost',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Status',
                          'model' => "transferDropoffs.{$ti}.status",
                          'val' => ucfirst($t['status'] ?? 'confirmed'),
                          'type' => 'select',
                          'options' => [
                              ['value' => 'confirmed', 'label' => 'Confirmed'],
                              ['value' => 'pending', 'label' => 'Pending'],
                              ['value' => 'cancelled', 'label' => 'Cancelled'],
                          ],
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                      <div class="col-md-3">@include('livewire.partials.editable-field', [
                          'label' => 'Notes',
                          'model' => "transferDropoffs.{$ti}.notes",
                          'val' => $t['notes'] ?? '',
                          'locked' => !$canEditFlightHotel,
                          'editingVar' => 'sectionEditing',
                      ])</div>
                    </div>
                  </div>
                </div>
              @endforeach
              @if ($canEditFlightHotel)
                <button type="button" wire:click="addDropoff" class="btn btn-sm d-flex align-items-center gap-1"
                  style="background:rgba(216,63,135,.05);color:#D83F87;border:1.5px dashed rgba(216,63,135,.2);border-radius:8px;padding:5px 14px;font-size:0.84rem;font-weight:600;"><i
                    class="ph ph-plus-circle"></i> Add Dropoff</button>
              @endif
            @endif

            @if ($canEditFlightHotel && empty($transferPickups) && empty($transferDropoffs))
              <div class="d-flex gap-2">
                <button type="button" wire:click="addPickup" class="btn btn-sm flex-grow-1"
                  style="background:rgba(51,46,158,.05);color:#332E9E;border:1.5px dashed rgba(51,46,158,.2);border-radius:8px;padding:7px;font-size:0.864rem;font-weight:600;"><i
                    class="ph ph-arrow-up-right me-1"></i> Add Pickup</button>
                <button type="button" wire:click="addDropoff" class="btn btn-sm flex-grow-1"
                  style="background:rgba(216,63,135,.05);color:#D83F87;border:1.5px dashed rgba(216,63,135,.2);border-radius:8px;padding:7px;font-size:0.864rem;font-weight:600;"><i
                    class="ph ph-arrow-down-right me-1"></i> Add Dropoff</button>
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- ACTIVITY LOG (left column, full-width) --}}
      <div class="bv-section">
        <div class="bv-section-hdr"
          style="background:linear-gradient(135deg,#332E9E,#4A45B5);border-radius:16px 16px 0 0;">
          <div class="bv-icon" style="background:rgba(255,255,255,.15);"><i class="ph ph-clock-countdown"
              style="color:#fff;font-size:1.08rem;"></i></div>
          <h2 style="color:#fff;">Comments</h2>
          <span class="ms-auto" style="font-size:0.768rem;color:rgba(255,255,255,.45);">{{ count($activityLog) }}
            event{{ count($activityLog) !== 1 ? 's' : '' }}</span>
        </div>
        {{-- The feed runs oldest→newest, so the latest sits at the bottom. Open
           pinned to the bottom (chat-style) and stay pinned as new entries land,
           unless the reader has scrolled up into the history. --}}
        <div class="bv-section-body" style="max-height:460px;overflow-y:auto;padding:14px 16px 8px;"
          x-data="{ stick: true }" x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight });
          new MutationObserver(() => { if (stick) $el.scrollTop = $el.scrollHeight })
              .observe($el, { childList: true, subtree: true });"
          @scroll="stick = ($el.scrollHeight - $el.scrollTop - $el.clientHeight) < 40">
          @if (empty($activityLog))
            <p style="color:#475569;font-size:0.864rem;padding:4px 0;">No activity yet.</p>
          @else
            @foreach ($activityLog as $entry)
              @php
                $c = $entry['color'] ?? $this->getActivityColorConfig($entry['action'] ?? '', $entry['type'] ?? 'info');
                $isFullRow = !empty($c['full_row']);
                // Comments/events with a real lifecycle status (full_row events like
                // Issued, Processing, Cancelled...) get the same background tint as their
                // badge colour. Routine events (View, Note, Edit, Docs...) stay plain —
                // a preset note (General/Manager Info/...) only tints its own detail
                // box below, not the whole row.
                $statusColor = $isFullRow ? $c['border'] : null;
              @endphp
              @php $grouped = ($entry['group_count'] ?? 1) > 1; @endphp
              <div x-data="{ grp: false }"
                style="display:flex;gap:0;box-sizing:border-box;width:100%;{{ $statusColor ? 'background:' . $statusColor . '1F;border-left:3px solid ' . $statusColor . ';border-radius:8px;padding-left:2px;padding-top:8px;padding-bottom:8px;' : '' }}{{ $loop->last ? '' : 'border-bottom:1.5px solid #CBD5E1;margin-bottom:4px;' }}">
                {{-- Rail column: avatar circle + connecting line --}}
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:{{ $statusColor ? 'center' : 'flex-start' }};width:60px;flex-shrink:0;">
                  @if (!empty($entry['avatar_url']))
                    <img src="{{ $entry['avatar_url'] }}" alt="{{ $entry['avatar_initials'] ?? '?' }}"
                      style="width:{{ $isFullRow ? '52px' : '46px' }};height:{{ $isFullRow ? '52px' : '46px' }};border-radius:50%;object-fit:cover;flex-shrink:0;border:2px solid {{ $c['border'] }};box-shadow:0 1px 5px {{ $c['border'] }}33;position:relative;z-index:1;">
                  @else
                    <div
                      style="width:{{ $isFullRow ? '52px' : '46px' }};height:{{ $isFullRow ? '52px' : '46px' }};border-radius:50%;background:{{ $c['border'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:{{ $isFullRow ? '1.02rem' : '.92rem' }};font-weight:800;color:#fff;box-shadow:0 1px 5px {{ $c['border'] }}33;position:relative;z-index:1;">
                      {{ $entry['avatar_initials'] ?? '?' }}</div>
                  @endif
                  @if (!$loop->last)
                    <div
                      style="width:2px;flex:1;min-height:8px;background:linear-gradient(to bottom,{{ $c['border'] }}28,rgba(51,46,158,.06));margin-top:3px;">
                    </div>
                  @endif
                </div>

                {{-- Content --}}
                <div
                  style="flex:1;min-width:0;padding-left:10px;padding-bottom:{{ $loop->last ? '4px' : '10px' }};padding-top:{{ $isFullRow ? '1px' : '4px' }};">
                  {{-- Action line --}}
                  <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;line-height:1.3;">
                    @if ($isFullRow)
                      <span
                        style="font-size:0.876rem;font-weight:800;color:{{ $c['border'] }};">{{ $entry['action'] }}</span>
                    @else
                      <span style="font-size:0.828rem;font-weight:700;color:#1E293B;">{{ $entry['agent'] }}</span>
                      <span style="font-size:0.816rem;font-weight:400;color:#475569;">{{ $entry['action'] }}</span>
                    @endif
                    @if (!empty($c['label']) && $c['label'] !== 'Edit' && $c['label'] !== '')
                      <span
                        style="font-size:0.648rem;font-weight:700;color:{{ $c['border'] }};background:{{ $c['border'] }}18;border:1px solid {{ $c['border'] }}44;padding:1px 7px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0;">{{ $c['label'] }}</span>
                    @endif
                    {{-- Repeat count for a folded burst of identical events --}}
                    @if ($grouped)
                      <span
                        style="font-size:0.66rem;font-weight:800;color:#475569;background:rgba(100,116,139,.12);border:1px solid rgba(100,116,139,.28);padding:1px 7px;border-radius:20px;flex-shrink:0;">&times;{{ $entry['group_count'] }}</span>
                    @endif
                  </div>
                  {{-- Agent + timestamp. A folded run shows its span, first → last. --}}
                  @if ($isFullRow)
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;margin-top:6px;">
                      <span style="font-size:0.78rem;font-weight:600;color:#475569;">{{ $entry['agent'] }}</span>
                      <span style="font-size:0.744rem;color:#475569;text-align:right;">{{ $entry['timestamp'] }}@if ($grouped)
                          → {{ $entry['group_last_ts'] }}
                        @endif
                      </span>
                    </div>
                  @else
                    <div style="display:flex;justify-content:flex-end;font-size:0.72rem;color:#475569;margin-top:5px;">{{ $entry['timestamp'] }}
                      @if ($grouped)
                        → {{ $entry['group_last_ts'] }}
                      @endif
                    </div>
                  @endif
                  {{-- Every folded event is still here — one click lists them all. --}}
                  @if ($grouped)
                    <div style="margin-top:3px;">
                      <button type="button" @click="grp = !grp"
                        style="font-size:0.696rem;color:#475569;background:none;border:none;padding:0;cursor:pointer;display:flex;align-items:center;gap:3px;line-height:1;">
                        <i class="ph ph-caret-down" style="font-size:0.78rem;transition:transform .12s;"
                          :style="grp ? 'transform:rotate(180deg)' : ''"></i>
                        <span x-text="grp ? 'Hide' : 'Show all {{ $entry['group_count'] }}'"></span>
                      </button>
                      <div x-show="grp" x-cloak style="margin-top:4px;display:flex;flex-wrap:wrap;gap:3px 5px;">
                        @foreach ($entry['group_children'] as $ch)
                          <span
                            style="font-size:0.678rem;color:#475569;background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.1);border-radius:6px;padding:1px 6px;white-space:nowrap;">{{ $ch['timestamp'] }}</span>
                        @endforeach
                      </div>
                    </div>
                  @endif
                  {{-- Detail --}}
                  @if (!empty($entry['detail']))
                    @if (!empty($entry['preset']))
                      @php $pc = $entry['preset']['color']; @endphp
                      <div
                        style="margin-top:5px;border-left:3px solid {{ $pc }};background:{{ $pc }}0D;border-radius:0 8px 8px 0;padding:6px 10px;">
                        <div
                          style="font-size:0.66rem;font-weight:800;color:{{ $pc }};text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">
                          {{ $entry['preset']['label'] }}</div>
                        {{-- pre-wrap keeps pasted line breaks + indentation intact; content
                             sits right after the tag (no leading newline) so pre-wrap doesn't
                             render that whitespace as a visible blank space before the text. --}}
                        <div
                          style="font-size:0.78rem;color:#334155;line-height:1.45;white-space:pre-wrap;overflow-wrap:anywhere;">{{ $entry['detail'] }}</div>
                      </div>
                    @else
                      <div
                        style="font-size:0.756rem;color:#475569;margin-top:3px;font-style:italic;line-height:1.4;white-space:pre-wrap;overflow-wrap:anywhere;">{{ $entry['detail'] }}</div>
                    @endif
                  @endif
                  {{-- Comments --}}
                  @if (!empty($entry['is_json']))
                    @foreach ($entry['reasons'] ?? [] as $r)
                      <div style="margin-top:7px;display:flex;gap:7px;align-items:flex-start;">
                        @if (!empty($r['avatar_url']))
                          <img src="{{ $r['avatar_url'] }}" alt="{{ $r['avatar_initials'] ?? '?' }}"
                            style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1.5px solid rgba(51,46,158,.22);margin-top:1px;">
                        @else
                          <div
                            style="width:30px;height:30px;border-radius:50%;background:#332E9E;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.72rem;font-weight:800;color:#fff;margin-top:1px;">
                            {{ $r['avatar_initials'] ?? '?' }}</div>
                        @endif
                        <div
                          style="flex:1;min-width:0;background:rgba(51,46,158,.04);border:1px solid rgba(51,46,158,.1);border-radius:8px;padding:5px 9px;">
                          <div style="display:flex;align-items:center;justify-content:space-between;gap:5px;flex-wrap:wrap;margin-bottom:3px;">
                            @if ($r['agent'])
                              <span
                                style="font-size:0.744rem;font-weight:700;color:#332E9E;">{{ $r['agent'] }}</span>
                            @endif
                            @if ($r['at'])
                              <span style="font-size:0.696rem;color:#475569;margin-left:auto;">{{ $r['at'] }}</span>
                            @endif
                          </div>
                          <div
                            style="font-size:0.78rem;color:#374151;line-height:1.5;white-space:pre-wrap;overflow-wrap:anywhere;">{{ $r['text'] }}</div>
                        </div>
                      </div>
                    @endforeach
                  @endif
                </div>
              </div>
            @endforeach
          @endif
        </div>
        @if ($this->canComment())
          {{-- Preset picking is pure client-side state (Alpine) — toggling a bubble
           used to round-trip to the server just to flip a style, which felt laggy.
           The chosen key rides along in the addComment() call and is re-validated
           server-side against $this->commentPresets there, same as before. --}}
          <div x-data="{
            preset: '',
            presets: {{ Js::from($this->commentPresets) }},
          }">
            {{-- Header preset bubbles: pick one to prepend a coloured heading to the comment --}}
            <div class="d-flex gap-1 align-items-center flex-wrap px-4 pt-2"
              style="border-top:1px solid rgba(51,46,158,.06);background:#FAFBFF;">
              @foreach ($this->commentPresets as $key => $p)
                <button type="button" @click="preset = (preset === '{{ $key }}' ? '' : '{{ $key }}')"
                  title="{{ $p['label'] }}"
                  :style="`font-size:0.69rem;font-weight:700;padding:3px 10px;border-radius:20px;cursor:pointer;transition:all .12s;border:1.5px solid ${preset === '{{ $key }}' ? '{{ $p['color'] }}' : '{{ $p['color'] }}55'};background:${preset === '{{ $key }}' ? '{{ $p['color'] }}' : '{{ $p['color'] }}12'};color:${preset === '{{ $key }}' ? '#fff' : '{{ $p['color'] }}'}`">
                  {{ $p['short'] }}
                </button>
              @endforeach
              <button type="button" x-show="preset" x-cloak @click="preset = ''"
                style="font-size:0.66rem;font-weight:600;color:#64748B;background:none;border:none;cursor:pointer;padding:3px 4px;">✕
                clear</button>
            </div>
            {{-- Textarea (not <input>): an input silently strips newlines, so pasted
             multi-line text would collapse onto one line. Enter inserts a newline;
             Ctrl/Cmd+Enter submits. --}}
            <div class="d-flex gap-2 align-items-end px-4 py-2" style="background:#FAFBFF;">
              <textarea wire:model="newComment" rows="1"
                :placeholder="preset ? presets[preset].label + ' — add details...' : 'Add a comment...'"
                class="form-control form-control-sm" x-init="$el.style.height = 'auto';
                $el.style.height = Math.min($el.scrollHeight, 220) + 'px'"
                @input="$el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,220)+'px'"
                @keydown.ctrl.enter.prevent="$wire.addComment(preset)"
                @keydown.meta.enter.prevent="$wire.addComment(preset)"
                :style="`border-radius:14px;font-size:0.84rem;line-height:1.5;resize:vertical;overflow-y:auto;min-height:34px;max-height:220px;white-space:pre-wrap;border-color:${preset ? presets[preset].color + '66' : 'rgba(51,46,158,.12)'}`"></textarea>
              <button type="button" @click="$wire.addComment(preset); preset = ''" class="btn btn-sm flex-shrink-0"
                :style="`background:${preset ? presets[preset].color : 'linear-gradient(135deg,#332E9E,#4A45B5)'};color:#fff;border:none;border-radius:20px;padding:4px 14px;font-size:0.816rem;font-weight:600;`">Add
                Comment</button>
            </div>
          </div>
        @endif
      </div>

    </div>{{-- end left col --}}

    {{-- RIGHT COLUMN --}}
    <div class="col-lg-4">

      {{-- PAYMENT STRUCTURE --}}
      @php
        $planLabel =
            [
                'full' => 'Full Payment',
                'awaiting' => 'Payment Awaiting',
                'payment_plan' => 'Payment Plan',
                'dnpl' => 'DNPL',
            ][$selected_payment_method ?? ''] ?? '';
      @endphp
      <div class="bv-section mb-3" style="{{ !$canEditPayment ? 'opacity:.5;' : '' }}">
        <div class="bv-section-hdr">
          <div class="bv-icon" style="background:rgba(14,165,233,.08);"><i class="ph ph-credit-card"
              style="color:#0EA5E9;font-size:1.08rem;"></i></div>
          <h2>Payment Structure</h2>
          @if ($planLabel)
            <span
              style="font-size:0.768rem;font-weight:700;color:#fff;background:#332E9E;border-radius:8px;padding:3px 10px;margin-left:auto;">{{ $planLabel }}</span>
          @endif
          @if ($canEditPayment)
            <button type="button" wire:click="togglePaymentSectionEditing" class="bv-edit-pencil"
              style="width:auto;padding:4px 10px;border-radius:6px;gap:4px;">{{ $paymentSectionEditing ? 'Done' : 'Edit' }}</button>
          @endif
        </div>
        <div class="bv-section-body">

          {{-- Payment method selector --}}
          @if ($paymentSectionEditing)
            <x-styled-select modelName="selected_payment_method" placeholder="Choose plan..." :options="[
                ['value' => 'full', 'label' => 'Full Payment'],
                ['value' => 'awaiting', 'label' => 'Payment Awaiting'],
                ['value' => 'payment_plan', 'label' => 'Payment Plan'],
                ['value' => 'dnpl', 'label' => 'DNPL'],
            ]" />
          @elseif(!$selected_payment_method)
            <div style="padding:10px 12px;border-radius:10px;background:rgba(148,163,184,.06);">
              <span style="font-size:0.864rem;color:#475569;">No payment plan set — click Edit to configure.</span>
            </div>
          @endif

          {{-- Instalments --}}
          @if ($selected_payment_method && $selected_payment_method !== 'full')
            <div
              style="margin-top:{{ $paymentSectionEditing ? '14px' : '0' }};padding:14px 16px;background:linear-gradient(135deg,#F8FAFF,#EEF2FF);border-radius:12px;border:1px solid rgba(51,46,158,.10);">
              <div
                style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#332E9E;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                <i class="ph ph-calendar-blank"></i> Instalments
              </div>
              @foreach ($payment_instalments as $i => $inst)
                <div wire:key="pay-inst-{{ $i }}" class="d-flex align-items-center mb-2"
                  style="background:#fff;border-radius:10px;padding:8px 12px;border:1px solid rgba(51,46,158,.08);gap:10px;">
                  @php $canTickInstalment = $role === 'accounts'; @endphp
                  <input type="checkbox" wire:model="instalment_paid.{{ $i }}"
                    wire:change="savePaymentStructure" @if (!$canTickInstalment) disabled @endif
                    style="width:17px;height:17px;accent-color:#16A34A;cursor:{{ $canTickInstalment ? 'pointer' : 'default' }};flex-shrink:0;margin:0;opacity:{{ $canTickInstalment ? '1' : '.5' }}">
                  <span
                    style="width:22px;height:22px;border-radius:50%;background:rgba(51,46,158,.08);display:inline-flex;align-items:center;justify-content:center;font-size:0.744rem;font-weight:800;color:#332E9E;flex-shrink:0;">{{ $i + 1 }}</span>
                  @if ($paymentSectionEditing)
                    @php $paid = !empty($instalment_paid[$i]); @endphp
                    <div
                      style="display:flex;align-items:center;gap:0;border:1px solid rgba(51,46,158,.18);border-radius:7px;overflow:hidden;flex-shrink:0;{{ $paid ? 'opacity:.55;' : '' }}">
                      <span
                        style="padding:4px 7px;background:{{ $paid ? '#F1F5F9' : '#F8FAFF' }};font-size:0.864rem;color:{{ $paid ? '#94A3B8' : '#64748B' }};border-right:1px solid rgba(51,46,158,.12);">&pound;</span>
                      <input type="number" wire:model="payment_instalments.{{ $i }}.amount"
                        step="0.01" min="0" placeholder="0.00" {{ $paid ? 'disabled' : '' }}
                        style="width:70px;padding:4px 6px;font-size:0.9rem;border:none;outline:none;background:{{ $paid ? '#F1F5F9' : '#fff' }};{{ $paid ? 'color:#475569;cursor:not-allowed;' : '' }}">
                    </div>
                    <div style="flex-shrink:0;min-width:130px;{{ $paid ? 'opacity:.55;pointer-events:none;' : '' }}">
                      <x-date-picker :modelName="'payment_instalments.' . $i . '.date'" :compact="true" />
                    </div>
                  @else
                    <span class="fw-bold"
                      style="color:{{ !empty($instalment_paid[$i]) ? '#16A34A' : '#1E293B' }};font-size:0.936rem;text-decoration:{{ !empty($instalment_paid[$i]) ? 'line-through' : 'none' }};">&pound;{{ $inst['amount'] ? number_format((float) $inst['amount'], 2) : '0.00' }}</span>
                    <span
                      style="color:#475569;font-size:0.792rem;">{{ $inst['date'] ? \Carbon\Carbon::parse($inst['date'])->format('d M Y') : 'No date' }}</span>
                    @if (!empty($instalment_paid[$i]))
                      <span
                        style="font-size:0.696rem;font-weight:700;color:#16A34A;background:rgba(22,163,74,.08);border-radius:5px;padding:2px 6px;margin-left:auto;">PAID</span>
                    @endif
                  @endif
                </div>
              @endforeach
              @if ($paymentSectionEditing)
                <div class="d-flex gap-2 mt-3">
                  <button type="button" wire:click="addPaymentInstalment"
                    style="border:1.5px dashed rgba(51,46,158,.25);background:transparent;color:#332E9E;border-radius:10px;padding:6px 16px;font-size:0.816rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;"><i
                      class="ph ph-plus-circle"></i> Add Instalment</button>
                  @if (count($payment_instalments) > 1)
                    <button type="button" wire:click="removePaymentInstalment"
                      style="border:1.5px solid rgba(220,38,38,.15);background:transparent;color:#DC2626;border-radius:10px;padding:6px 12px;font-size:0.816rem;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;"><i
                        class="ph ph-minus-circle"></i> Remove</button>
                  @endif
                </div>
              @endif
            </div>
          @endif


        </div>
      </div>

      {{-- COST & MARGINS --}}
      <div class="bv-section">
        <div style="border-radius:16px 16px 0 0;background:#fff;border:1px solid rgba(51,46,158,.08);">
          <div class="bv-section-hdr">
            <div class="bv-icon" style="background:rgba(255,107,53,.08);"><i class="ph ph-currency-circle-dollar"
                style="color:#FF6B35;font-size:1.02rem;"></i></div>
            <h2>Cost &amp; Margins</h2>
          </div>
          @php
            $bvTypeOrder = ['adult', 'gbe', 'child', 'infant'];
            $bvTypeLabels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
            $bvTypeColors = ['adult' => '#332E9E', 'gbe' => '#D83F87', 'child' => '#D97706', 'infant' => '#16A34A'];
            $bvTypeBg = [
                'adult' => 'rgba(51,46,158,.09)',
                'gbe' => 'rgba(216,63,135,.09)',
                'child' => 'rgba(217,119,6,.09)',
                'infant' => 'rgba(22,163,74,.09)',
            ];
            $bvTypeGroups = [];
            foreach ($this->passengers as $pi => $pax) {
                $t = $pax['type'] ?? 'adult';
                if (!isset($bvTypeGroups[$t])) {
                    $bvTypeGroups[$t] = ['count' => 0, 'cost' => 0, 'sold' => 0];
                }
                $bvTypeGroups[$t]['count']++;
                foreach ($flightSegments as $seg) {
                    $pc = $seg['passenger_costs'][$pi] ?? [];
                    $bvTypeGroups[$t]['cost'] += (float) ($pc['cost'] ?? 0);
                    $bvTypeGroups[$t]['sold'] += (float) ($pc['sold'] ?? 0);
                }
            }
            $flightCost = array_sum(array_column($bvTypeGroups, 'cost'));
            $flightSold = array_sum(array_column($bvTypeGroups, 'sold'));
            $hotelCost = collect($hotels)->sum(fn($h) => (float) ($h['actual_cost'] ?? 0));
            $hotelSold = collect($hotels)->sum(fn($h) => (float) ($h['selling_price'] ?? 0));
            $visaCost = collect($visas)->sum(fn($v) => (float) ($v['actual_cost'] ?? 0));
            $visaSold = collect($visas)->sum(fn($v) => (float) ($v['selling_price'] ?? 0));
            // Transfers are charged per leg — pickups and dropoffs each carry their own cost/sold.
            $bvTransfers = collect($transferPickups)
                ->map(fn($t) => $t + ['leg' => 'Pickup'])
                ->merge(collect($transferDropoffs)->map(fn($t) => $t + ['leg' => 'Dropoff']));
            $transferCost = $bvTransfers->sum(fn($t) => (float) ($t['actual_cost'] ?? 0));
            $transferSold = $bvTransfers->sum(fn($t) => (float) ($t['selling_price'] ?? 0));
            $excCost = (float) ($excursion_actual_cost ?: 0);
            $excSold = (float) ($excursion_selling_price ?: 0);
            $ccAmt = (float) ($cc_charges ?: 0);
            // Derive rate(s) from per-transaction payment_details so multiple
            // charges with the same/different rates are reflected accurately.
            $ccRates =
                $booking->paymentHistory
                    ?->filter(fn($ph) => $ph->status === 'approved')
                    ?->pluck('payment_details.cc_charge_rate')
                    ?->filter(fn($r) => $r !== null && $r > 0)
                    ?->unique()
                    ?->values()
                    ?->toArray() ?? [];
            $ccRate =
                count($ccRates) === 1
                    ? $ccRates[0]
                    : (count($ccRates) > 1
                        ? 'mixed'
                        : $booking->payment?->cc_charge_rate ?? null);
          @endphp
          <div style="border-top:1px solid rgba(51,46,158,.06);">

            {{-- ── FLIGHT section (aggregated by type) ── --}}
            <div class="bv-cm-hdr"
              style="background:rgba(51,46,158,.04);border-bottom:1px solid rgba(51,46,158,.08);">
              <i class="ph ph-airplane-tilt" style="font-size:0.9rem;color:#332E9E;"></i>
              <span style="color:#332E9E;">Flight</span>
            </div>
            <div class="bv-cm-cols"
              style="grid-template-columns:1fr 36px 76px 76px;background:rgba(248,250,255,.9);border-bottom:1px solid rgba(51,46,158,.05);">
              <span>Type</span>
              <span style="text-align:center;">Pax</span>
              <span style="text-align:right;">Cost</span>
              <span style="text-align:right;">Sold</span>
            </div>
            @foreach ($bvTypeOrder as $t)
              @php
                $tData = $bvTypeGroups[$t] ?? ['count' => 0, 'cost' => 0, 'sold' => 0];
                $tColor = $bvTypeColors[$t];
                $tMgn = $tData['sold'] - $tData['cost'];
              @endphp
              @if ($tData['count'] > 0)
                <div class="bv-cm-row"
                  style="grid-template-columns:1fr 36px 76px 76px;border-bottom:1px solid rgba(51,46,158,.05);border-left:3px solid {{ $tColor }};">
                  <div>
                    <span
                      style="font-size:0.768rem;font-weight:700;color:{{ $tColor }};">{{ $bvTypeLabels[$t] }}</span>
                    @if ($tMgn != 0)
                      <span
                        style="font-size:0.648rem;font-weight:700;color:{{ $tMgn >= 0 ? '#16A34A' : '#DC2626' }};display:block;">{{ $tMgn >= 0 ? '+' : '' }}&pound;{{ number_format($tMgn, 2) }}</span>
                    @endif
                  </div>
                  <span
                    style="font-size:0.768rem;font-weight:700;color:{{ $tColor }};text-align:center;">{{ $tData['count'] }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">&pound;{{ number_format($tData['cost'], 2) }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">&pound;{{ number_format($tData['sold'], 2) }}</span>
                </div>
              @endif
            @endforeach
            @if ($this->safiTax > 0)
              <div class="bv-cm-row"
                style="grid-template-columns:1fr 36px 76px 76px;background:rgba(51,46,158,.03);border-bottom:1px solid rgba(51,46,158,.06);border-left:3px solid #332E9E;">
                <span style="font-size:0.744rem;font-weight:700;color:#332E9E;">SAFI</span>
                <span
                  style="font-size:0.744rem;font-weight:700;color:#332E9E;text-align:center;">{{ $this->nonInfantPassengerCount }}</span>
                <span
                  style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">&pound;{{ number_format($this->safiTax, 2) }}</span>
                <span style="font-size:0.816rem;font-weight:600;color:#475569;text-align:right;">—</span>
              </div>
            @endif
            @if ($this->atolTax > 0)
              <div class="bv-cm-row"
                style="grid-template-columns:1fr 36px 76px 76px;background:rgba(255,107,53,.04);border-bottom:1px solid rgba(255,107,53,.10);border-left:3px solid #FF6B35;">
                <span style="font-size:0.744rem;font-weight:700;color:#FF6B35;">ATOL</span>
                <span
                  style="font-size:0.744rem;font-weight:700;color:#FF6B35;text-align:center;">{{ $this->nonInfantPassengerCount }}</span>
                <span
                  style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">&pound;{{ number_format($this->atolTax, 2) }}</span>
                <span style="font-size:0.816rem;font-weight:600;color:#475569;text-align:right;">—</span>
              </div>
            @endif
            <div class="bv-cm-total"
              style="grid-template-columns:1fr 36px 76px 76px;background:rgba(51,46,158,.04);border-bottom:1px solid rgba(51,46,158,.08);">
              <span style="font-size:0.72rem;font-weight:800;color:#1E293B;">Total</span>
              <span></span>
              <span
                style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">&pound;{{ number_format($flightCost + $this->atolSafiTax, 2) }}</span>
              <span
                style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">&pound;{{ number_format($flightSold, 2) }}</span>
            </div>

            {{-- ── HOTEL section ── --}}
            @if (count($hotels) > 0)
              <div class="bv-cm-hdr"
                style="background:rgba(124,58,237,.05);border-bottom:1px solid rgba(124,58,237,.10);border-top:1px solid rgba(124,58,237,.10);">
                <i class="ph ph-buildings" style="font-size:0.9rem;color:#7C3AED;"></i>
                <span style="color:#7C3AED;">Hotel</span>
              </div>
              <div class="bv-cm-cols"
                style="grid-template-columns:1fr 36px 76px 76px;background:rgba(124,58,237,.06);border-bottom:1px solid rgba(124,58,237,.08);">
                <span>Hotel</span>
                <span style="text-align:center;">Rms</span>
                <span style="text-align:right;">Cost</span>
                <span style="text-align:right;">Sold</span>
              </div>
              @foreach ($hotels as $hi => $h)
                @php
                  $hCost = (float) ($h['actual_cost'] ?? 0);
                  $hSold = (float) ($h['selling_price'] ?? 0);
                  $hMgn = $hSold - $hCost;
                  $hRooms = (int) ($h['number_of_rooms'] ?? 1);
                @endphp
                <div class="bv-cm-row"
                  style="grid-template-columns:1fr 36px 76px 76px;border-bottom:1px solid rgba(124,58,237,.05);border-left:3px solid #7C3AED;">
                  <div>
                    <span
                      style="font-size:0.792rem;font-weight:600;color:#1E293B;display:block;">{{ $h['hotel_name'] ?: 'Hotel ' . ($hi + 1) }}</span>
                    @if ($hMgn != 0)
                      <span
                        style="font-size:0.648rem;font-weight:700;color:{{ $hMgn >= 0 ? '#16A34A' : '#DC2626' }};">{{ $hMgn >= 0 ? '+' : '' }}£{{ number_format($hMgn, 2) }}</span>
                    @endif
                  </div>
                  <span
                    style="font-size:0.768rem;font-weight:700;color:#7C3AED;text-align:center;">{{ $hRooms }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($hCost, 2) }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($hSold, 2) }}</span>
                </div>
              @endforeach
              <div class="bv-cm-total"
                style="grid-template-columns:1fr 36px 76px 76px;background:rgba(124,58,237,.05);border-bottom:1px solid rgba(124,58,237,.10);">
                <span style="font-size:0.72rem;font-weight:800;color:#1E293B;">Total</span>
                <span></span>
                <span
                  style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($hotelCost, 2) }}</span>
                <span
                  style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($hotelSold, 2) }}</span>
              </div>
            @endif

            {{-- ── VISA section ── --}}
            @if (count($visas) > 0)
              <div class="bv-cm-hdr"
                style="background:rgba(22,163,74,.05);border-bottom:1px solid rgba(22,163,74,.10);border-top:1px solid rgba(22,163,74,.10);">
                <i class="ph ph-identification-card" style="font-size:0.9rem;color:#16A34A;"></i>
                <span style="color:#16A34A;">Visa</span>
              </div>
              <div class="bv-cm-cols"
                style="grid-template-columns:1fr 76px 76px;background:rgba(22,163,74,.06);border-bottom:1px solid rgba(22,163,74,.08);">
                <span>Passenger</span>
                <span style="text-align:right;">Cost</span>
                <span style="text-align:right;">Sold</span>
              </div>
              @foreach ($visas as $vi => $v)
                @php
                  $vCost = (float) ($v['actual_cost'] ?? 0);
                  $vSold = (float) ($v['selling_price'] ?? 0);
                @endphp
                <div class="bv-cm-row"
                  style="grid-template-columns:1fr 76px 76px;border-bottom:1px solid rgba(22,163,74,.05);border-left:3px solid #16A34A;">
                  <span
                    style="font-size:0.78rem;font-weight:600;color:#1E293B;">{{ $v['passenger_name'] ?: 'Visa ' . ($vi + 1) }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($vCost, 2) }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($vSold, 2) }}</span>
                </div>
              @endforeach
              @if (count($visas) > 1)
                <div class="bv-cm-total"
                  style="grid-template-columns:1fr 76px 76px;background:rgba(22,163,74,.05);border-bottom:1px solid rgba(22,163,74,.10);">
                  <span style="font-size:0.72rem;font-weight:800;color:#1E293B;">Total</span>
                  <span
                    style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($visaCost, 2) }}</span>
                  <span
                    style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($visaSold, 2) }}</span>
                </div>
              @endif
            @endif

            {{-- ── TRANSFERS section ── --}}
            @if ($bvTransfers->isNotEmpty())
              <div class="bv-cm-hdr"
                style="background:rgba(14,165,233,.05);border-bottom:1px solid rgba(14,165,233,.10);border-top:1px solid rgba(14,165,233,.10);">
                <i class="ph ph-van" style="font-size:0.9rem;color:#0EA5E9;"></i>
                <span style="color:#0EA5E9;">Transfers</span>
              </div>
              <div class="bv-cm-cols"
                style="grid-template-columns:1fr 60px 76px 76px;background:rgba(14,165,233,.06);border-bottom:1px solid rgba(14,165,233,.10);">
                <span>Transfer</span>
                <span style="text-align:center;">Leg</span>
                <span style="text-align:right;">Cost</span>
                <span style="text-align:right;">Sold</span>
              </div>
              @foreach ($bvTransfers as $tri => $tr)
                @php
                  $trCost = (float) ($tr['actual_cost'] ?? 0);
                  $trSold = (float) ($tr['selling_price'] ?? 0);
                  $trMgn = $trSold - $trCost;
                @endphp
                <div class="bv-cm-row"
                  style="grid-template-columns:1fr 60px 76px 76px;border-bottom:1px solid rgba(14,165,233,.06);border-left:3px solid #0EA5E9;">
                  <div>
                    <span
                      style="font-size:0.792rem;font-weight:600;color:#1E293B;display:block;">{{ $tr['location'] ?: ($tr['route'] ?: 'Transfer ' . ($tri + 1)) }}</span>
                    @if ($trMgn != 0)
                      <span
                        style="font-size:0.648rem;font-weight:700;color:{{ $trMgn >= 0 ? '#16A34A' : '#DC2626' }};">{{ $trMgn >= 0 ? '+' : '' }}£{{ number_format($trMgn, 2) }}</span>
                    @endif
                  </div>
                  <span
                    style="font-size:0.696rem;font-weight:700;color:#0EA5E9;text-align:center;">{{ $tr['leg'] }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:600;color:#1E293B;text-align:right;">£{{ number_format($trCost, 2) }}</span>
                  <span
                    style="font-size:0.816rem;font-weight:700;color:#1E293B;text-align:right;">£{{ number_format($trSold, 2) }}</span>
                </div>
              @endforeach
              @if ($bvTransfers->count() > 1)
                <div class="bv-cm-total"
                  style="grid-template-columns:1fr 60px 76px 76px;background:rgba(14,165,233,.05);border-bottom:1px solid rgba(14,165,233,.10);">
                  <span style="font-size:0.72rem;font-weight:800;color:#1E293B;">Total</span>
                  <span></span>
                  <span
                    style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($transferCost, 2) }}</span>
                  <span
                    style="font-size:0.84rem;font-weight:800;color:#1E293B;text-align:right;">£{{ number_format($transferSold, 2) }}</span>
                </div>
              @endif
            @endif

            {{-- ── EXCURSION section ── --}}
            @if ($excCost > 0 || $excSold > 0)
              <div class="bv-cm-hdr"
                style="background:rgba(255,107,53,.05);border-bottom:1px solid rgba(255,107,53,.10);border-top:1px solid rgba(255,107,53,.10);">
                <i class="ph ph-binoculars" style="font-size:0.9rem;color:#FF6B35;"></i>
                <span style="color:#FF6B35;">Excursion</span>
              </div>
              <div class="bv-cm-row"
                style="grid-template-columns:1fr 76px 76px;border-bottom:1px solid rgba(255,107,53,.06);border-left:3px solid #FF6B35;">
                <span
                  style="font-size:0.792rem;font-weight:600;color:#1E293B;">{{ $excursion_name ?: 'Excursion' }}</span>
                <span
                  style="font-size:0.816rem;font-weight:600;color:#374151;text-align:right;">£{{ number_format($excCost, 2) }}</span>
                <span
                  style="font-size:0.816rem;font-weight:700;color:#111827;text-align:right;">£{{ number_format($excSold, 2) }}</span>
              </div>
            @endif

            {{-- ── Grand Totals + CC + Dual Margin ── --}}
            @php
              $totCost = $flightCost + $this->atolSafiTax + $hotelCost + $visaCost + $transferCost + $excCost;
              $totSold = $flightSold + $hotelSold + $visaSold + $transferSold + $excSold;
              $grossMgn = $totSold - $totCost;
              $netMgn = $grossMgn - $ccAmt;
              $netPct = $totSold > 0 ? round(($netMgn / $totSold) * 100, 1) : 0;
            @endphp
            <div style="padding:16px 20px;border-top:2px solid rgba(51,46,158,.08);">

              {{-- Payment Mode display --}}
              @if ($payment_mode || $payment_mode_2)
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">
                  @if ($payment_mode)
                    <span
                      style="font-size:0.72rem;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(51,46,158,.07);color:#332E9E;border:1px solid rgba(51,46,158,.14);">{{ ucfirst(str_replace('_', ' ', $payment_mode)) }}</span>
                  @endif
                  @if ($payment_mode_2)
                    <span
                      style="font-size:0.72rem;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(51,46,158,.07);color:#332E9E;border:1px solid rgba(51,46,158,.14);">{{ ucfirst(str_replace('_', ' ', $payment_mode_2)) }}</span>
                  @endif
                </div>
              @endif

              <div
                style="background:rgba(51,46,158,.03);border-radius:10px;padding:10px 14px;margin-bottom:10px;border:1px solid rgba(51,46,158,.07);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                  <span style="font-size:0.744rem;font-weight:700;color:#475569;">Grand Total Cost</span>
                  <span
                    style="font-size:0.912rem;font-weight:800;color:#1E293B;">£{{ number_format($totCost, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                  <span style="font-size:0.744rem;font-weight:700;color:#475569;">Grand Total Sold</span>
                  <span
                    style="font-size:0.912rem;font-weight:800;color:#1E293B;">£{{ number_format($totSold, 2) }}</span>
                </div>
              </div>

              {{-- CC Charges --}}
              @if ($ccAmt > 0)
                <div
                  style="display:flex;justify-content:space-between;align-items:center;padding:7px 10px;border-radius:8px;background:rgba(220,38,38,.06);border:1px solid rgba(220,38,38,.15);margin-bottom:10px;">
                  <div>
                    <span style="font-size:0.744rem;font-weight:700;color:#DC2626;">CC Charges</span>
                    @if ($ccRate && $ccRate !== 'mixed')
                      <span style="font-size:0.672rem;color:#475569;margin-left:4px;">({{ $ccRate }}%)</span>
                    @elseif($ccRate === 'mixed')
                      <span style="font-size:0.672rem;color:#475569;margin-left:4px;"
                        title="Multiple charges at different rates">(mixed rates)</span>
                    @endif
                  </div>
                  <span
                    style="font-size:0.864rem;font-weight:800;color:#DC2626;">–&pound;{{ number_format($ccAmt, 2) }}</span>
                </div>
              @endif

              {{-- Gross margin (before CC) --}}
              @if ($ccAmt > 0)
                <div
                  style="display:flex;justify-content:space-between;align-items:center;padding:6px 10px;border-radius:8px;background:rgba(51,46,158,.04);border:1px solid rgba(51,46,158,.08);margin-bottom:8px;">
                  <span style="font-size:0.72rem;font-weight:700;color:#475569;">Margin (excl. CC)</span>
                  <span
                    style="font-size:0.84rem;font-weight:700;color:{{ $grossMgn >= 0 ? '#16A34A' : '#DC2626' }};">&pound;{{ number_format($grossMgn, 2) }}</span>
                </div>
              @endif

              {{-- Net Margin box --}}
              <div
                style="padding:14px;border-radius:12px;{{ $netMgn >= 0 ? 'background:linear-gradient(135deg,rgba(22,163,74,.12),rgba(22,163,74,.05));border:2px solid rgba(22,163,74,.22);' : 'background:linear-gradient(135deg,rgba(220,38,38,.12),rgba(220,38,38,.05));border:2px solid rgba(220,38,38,.22);' }}">
                <div
                  style="font-size:0.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:{{ $netMgn >= 0 ? '#15803D' : '#DC2626' }};margin-bottom:2px;">
                  {{ $ccAmt > 0 ? 'Net Margin (incl. CC)' : 'Total Margin' }}</div>
                <div
                  style="font-size:1.6rem;font-weight:800;color:{{ $netMgn >= 0 ? '#16A34A' : '#DC2626' }};line-height:1;letter-spacing:-.02em;">
                  &pound;{{ number_format($netMgn, 2) }}</div>
                <div
                  style="font-size:0.768rem;font-weight:700;color:{{ $netMgn >= 0 ? '#16A34A' : '#DC2626' }};margin-top:3px;opacity:.8;">
                  {{ $netPct }}% margin</div>
              </div>

              {{-- Balance Due / Fully Settled / Overpaid --}}
              @php
                // Three distinct states. Previously ANY negative balance was clamped
                // to £0.00 and labelled "Fully Settled", which silently hid real
                // overpayments (e.g. a sold price reduced after payment was taken).
                // Tolerance of half a penny absorbs float noise so a genuinely
                // settled booking still reads as settled.
                $bal = $this->runningBalance;
                $state = abs($bal) < 0.005 ? 'settled' : ($bal > 0 ? 'due' : 'over');
                $bc = [
                    'settled' => ['#15803D', '#16A34A', '22,163,74'],
                    'due' => ['#DC2626', '#DC2626', '220,38,38'],
                    'over' => ['#B45309', '#D97706', '217,119,6'],
                ][$state];
                $blabel = ['settled' => 'Fully Settled', 'due' => 'Balance Due', 'over' => 'Overpaid'][$state];
              @endphp
              <div
                style="margin-top:10px;padding:12px;border-radius:12px;background:linear-gradient(135deg,rgba({{ $bc[2] }},.07),rgba({{ $bc[2] }},.02));border:1.5px solid rgba({{ $bc[2] }},.14);">
                <div
                  style="font-size:0.672rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:{{ $bc[0] }};margin-bottom:2px;">
                  {{ $blabel }}</div>
                <div
                  style="font-size:1.32rem;font-weight:800;color:{{ $bc[1] }};line-height:1;letter-spacing:-.01em;">
                  &pound;{{ number_format(abs($bal), 2) }}</div>
                @if ($state === 'over')
                  <div style="font-size:0.672rem;font-weight:600;color:#B45309;margin-top:3px;">Paid
                    £{{ number_format($this->totalPaid, 2) }} against a
                    £{{ number_format($this->totalSoldPrice, 2) }} sale</div>
                @endif
              </div>

              {{-- Margin sharing --}}
              @if (Auth::user()->hasPermission('bookings.share_margin'))
                <div style="margin-top:10px;">
                  @if ($booking->marginShares->isNotEmpty())
                    @foreach ($booking->marginShares as $share)
                      <div class="d-flex align-items-center gap-2 mb-2 px-2 py-1"
                        style="background:#FAFBFF;border-radius:8px;border:1px solid rgba(51,46,158,.05);">
                        <i class="ph ph-share-network" style="color:#332E9E;font-size:0.96rem;flex-shrink:0;"></i>
                        <div class="flex-grow-1">
                          <span class="fw-semibold"
                            style="font-size:0.84rem;color:#1E293B;">&pound;{{ number_format($share->amount, 2) }}
                            shared with {{ $share->sharedWith?->name }}</span>
                          @if ($share->note)
                            <span class="d-block"
                              style="font-size:0.72rem;color:#475569;">{{ $share->note }}</span>
                          @endif
                        </div>
                        <button type="button" wire:click="removeMarginShare({{ $share->id }})"
                          onclick="return confirm('Remove this margin share?')"
                          style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:5px;padding:1px 7px;font-size:0.72rem;cursor:pointer;flex-shrink:0;">✕</button>
                      </div>
                    @endforeach
                  @endif
                  <button type="button" wire:click="openShareMargin" class="w-100"
                    style="background:rgba(51,46,158,.06);color:#332E9E;border:1px solid rgba(51,46,158,.15);border-radius:10px;padding:8px;font-size:0.816rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i class="ph ph-share-network"></i> Share Margin
                  </button>
                </div>
              @endif

              {{-- Transfer booking ownership --}}
              @if (Auth::user()->hasPermission('bookings.transfer'))
                <div style="margin-top:10px;">
                  <button type="button" wire:click="openTransferBooking" class="w-100"
                    style="background:rgba(217,119,6,.08);color:#B45309;border:1px solid rgba(217,119,6,.2);border-radius:10px;padding:8px;font-size:0.816rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                    <i class="ph ph-arrows-left-right"></i> Transfer Booking
                  </button>
                </div>
              @endif
            </div>
          </div>

          {{-- CHARGE REFUND PAYMENT: shown above Payment History while a refund is queued --}}
          @if ($this->activeRefund)
            <div class="px-4 pt-3">
              <button type="button" x-data="{ open: @entangle('showRefundChargeModal') }" @click="open = true"
                wire:click="openRefundChargeModal" class="w-100"
                style="background:linear-gradient(135deg,#DC2626,#EF4444);color:#fff;border:none;border-radius:10px;padding:8px;font-size:0.816rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                <i class="ph ph-arrows-counter-clockwise"></i> Charge Refund Payment
              </button>
            </div>
          @endif

          {{-- PAYMENT HISTORY --}}
          <div class="px-4 py-3" style="border-top:1px solid rgba(51,46,158,.06);">
            @php
              // Declined charge requests already appear in the activity feed
              // (see PaymentChargeRequest::appendBookingActivity) — listing them
              // here too was redundant, so they're excluded from this panel.
$visiblePaymentHistory = $booking->paymentHistory?->reject(fn($ph) => $ph->status === 'rejected');
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span
                style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;">Payment
                History</span>
              @if ($visiblePaymentHistory?->isNotEmpty())
                <span style="font-size:0.816rem;font-weight:800;color:#16A34A;">Received
                  &pound;{{ number_format($this->totalPaid, 2) }}</span>
              @endif
            </div>
            @if ($visiblePaymentHistory?->isNotEmpty())
              @php $paymentNum = 0; @endphp
              @foreach ($visiblePaymentHistory as $ph)
                @php
                  $paymentNum++;
                  $status = $ph->status ?? 'pending';
                  $statusLabel =
                      ['pending' => 'Pending Approval', 'approved' => 'Approved', 'voided' => 'Voided'][$status] ??
                      'Pending Approval';
                  $statusStyle = match ($status) {
                      'approved' => 'color:#16A34A;background:rgba(22,163,74,.08);',
                      'voided' => 'color:#DC2626;background:rgba(220,38,38,.08);',
                      default => 'color:#F59E0B;background:rgba(245,158,11,.08);',
                  };
                  $voidReason = $ph->payment_details['void_reason'] ?? null;
                  $isRefundPayout = (bool) ($ph->payment_details['refund_payout'] ?? false);
                  $refundComment = $ph->payment_details['comment'] ?? null;
                @endphp
                <div class="d-flex align-items-center gap-2 mb-2 px-2 py-1"
                  style="background:#FAFBFF;border-radius:8px;border:1px solid rgba(51,46,158,.05);{{ $status === 'voided' ? 'opacity:.7;' : '' }}">
                  <span
                    style="width:20px;height:20px;border-radius:50%;background:rgba(51,46,158,.06);display:inline-flex;align-items:center;justify-content:center;font-size:0.696rem;font-weight:800;color:#332E9E;flex-shrink:0;">{{ $paymentNum }}</span>
                  <div class="flex-grow-1">
                    <span class="fw-semibold"
                      style="font-size:0.84rem;{{ $isRefundPayout ? 'color:#DC2626;' : 'color:#1E293B;' }}{{ $status === 'voided' ? 'text-decoration:line-through;' : '' }}">{{ $isRefundPayout ? '−' : '' }}&pound;{{ number_format(abs($ph->amount), 2) }}</span>
                    <span class="d-block"
                      style="font-size:0.72rem;color:#475569;">{{ $isRefundPayout ? 'Refund' : ucfirst(str_replace('_', ' ', $ph->payment_method ?? 'N/A')) }}
                      · {{ \Carbon\Carbon::parse($ph->payment_date)->format('d M Y') }}</span>
                    @if ($isRefundPayout && $refundComment)
                      <span class="d-block" style="font-size:0.708rem;color:#DC2626;">{{ $refundComment }}</span>
                    @endif
                    @if ($status === 'voided' && $voidReason)
                      <span class="d-block" style="font-size:0.708rem;color:#DC2626;">Voided by
                        {{ $ph->voidedBy?->name ?? 'accounts' }} — {{ $voidReason }}</span>
                    @endif
                  </div>
                  <span
                    style="font-size:0.696rem;font-weight:700;padding:2px 8px;border-radius:10px;{{ $statusStyle }}">{{ $statusLabel }}</span>
                  @if ($status === 'approved' && Auth::user()->hasPermission('payments.charge'))
                    <button type="button" wire:click="confirmVoidPayment({{ $ph->id }})"
                      title="Void this payment"
                      style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:5px;padding:1px 8px;font-size:0.696rem;font-weight:700;cursor:pointer;flex-shrink:0;">Void</button>
                  @endif
                  @if (Auth::user()->role === 'admin')
                    <button type="button" wire:click="deletePaymentHistory({{ $ph->id }})"
                      onclick="return confirm('Delete this payment record?')"
                      style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:5px;padding:1px 7px;font-size:0.72rem;cursor:pointer;flex-shrink:0;">✕</button>
                  @endif
                </div>
              @endforeach
            @else
              <p style="color:#475569;font-size:0.84rem;">No payments recorded yet.</p>
            @endif

            @if ($canRequestChargeButton)
              <button type="button" x-data="{ open: @entangle('showChargeModal') }" @click="open = true" wire:click="openChargeModal"
                class="w-100 mt-2"
                style="background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;padding:8px;font-size:0.816rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;">
                <i class="ph ph-plus-circle"></i> Request Payment Charge
              </button>
            @endif
          </div>

          {{-- DOCUMENTS — each one just opens in a new tab (native <a target="_blank">).
             Used to be an Alpine-driven inline preview modal (image/PDF/audio
             viewers, load-state tracking, per-type branching); a new tab does
             the same job with none of that client-side state to maintain. --}}
          <div class="px-4 py-3" style="border-top:1px solid rgba(51,46,158,.06);">
            <div
              style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;margin-bottom:6px;">
              Documents</div>

            {{-- Existing documents --}}
            @if (count($booking->documents))
              @php
                // Each document type gets its own colour so the kind of file is
                // readable at a glance rather than buried in grey text.
                $bvDocColors = [
                    'e_ticket' => '#332E9E',
                    'hotel_voucher' => '#7C3AED',
                    'passport' => '#16A34A',
                    'visa' => '#D97706',
                    'itinerary' => '#64748B',
                    'invoice' => '#D83F87',
                    'other' => '#94A3B8',
                ];
              @endphp
              <div style="margin-bottom:8px;">
                @php $audioExts = ['mp3','wav','m4a','ogg','aac','flac','webm','opus']; @endphp
                @foreach ($booking->documents as $doc)
                  @php
                    $docExt = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                    $bvDocC = $bvDocColors[$doc->document_type] ?? '#94A3B8';
                  @endphp
                  <div class="d-flex align-items-center justify-content-between mb-1 px-2 py-1"
                    style="background:#F8FAFF;border-radius:7px;">
                    <a href="{{ Storage::disk('public')->url($doc->file_path) }}" target="_blank" rel="noopener"
                      style="background:none;border:none;font-size:0.864rem;color:#332E9E;cursor:pointer;padding:0;text-align:left;text-decoration:none;">
                      <i class="ph {{ in_array($docExt, $audioExts) ? 'ph-waveform' : 'ph-file' }} me-1"
                        style="color:#7C3AED;"></i>
                      <span
                        style="font-size:0.66rem;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ $bvDocC }}1A;color:{{ $bvDocC }};border:1px solid {{ $bvDocC }}33;white-space:nowrap;">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</span>
                    </a>
                    @if (Auth::user()->role === 'admin')
                      <button type="button" wire:click="deleteDocument({{ $doc->id }})"
                        style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:5px;padding:1px 7px;font-size:0.72rem;cursor:pointer;">✕</button>
                    @endif
                  </div>
                @endforeach
              </div>
            @endif

            {{-- Upload new documents --}}
            @if ($canUploadDocuments)
              @foreach ($newDocuments as $di => $nd)
                <div class="d-flex gap-2 align-items-center mb-2">
                  <select wire:model="newDocumentTypes.{{ $di }}" class="form-control form-control-sm"
                    style="font-size:0.816rem;border-radius:7px;width:120px;flex-shrink:0;">
                    <option value="">Type</option>
                    <option value="e_ticket">E-Ticket</option>
                    <option value="hotel_voucher">Hotel Voucher</option>
                    <option value="passport">Passport</option>
                    <option value="visa">Visa</option>
                    <option value="itinerary">Itinerary</option>
                    <option value="invoice">Invoice</option>
                    <option value="other">Other</option>
                  </select>
                  @if ($nd && method_exists($nd, 'getClientOriginalName'))
                    <span style="font-size:0.864rem;flex:1;color:#1E293B;">
                      <i class="ph ph-file me-1" style="color:#7C3AED;"></i>{{ $nd->getClientOriginalName() }}
                    </span>
                    <button type="button" wire:click="saveDocument({{ $di }})"
                      style="background:rgba(22,163,74,.10);color:#16A34A;border:none;border-radius:6px;font-size:0.84rem;padding:3px 8px;cursor:pointer;font-weight:700;">✓</button>
                    <button type="button" wire:click="removeDocument({{ $di }})"
                      style="background:rgba(220,38,38,.08);color:#DC2626;border:none;border-radius:6px;font-size:0.84rem;padding:3px 8px;cursor:pointer;">✕</button>
                  @else
                    <input type="file" wire:model="newDocuments.{{ $di }}"
                      class="form-control form-control-sm" style="font-size:0.816rem;border-radius:7px;flex:1;">
                  @endif
                </div>
              @endforeach
              <button type="button" wire:click="addDocument"
                style="font-size:0.768rem;font-weight:600;padding:4px 12px;border-radius:7px;background:rgba(124,58,237,.07);color:#7C3AED;border:1.5px dashed rgba(124,58,237,.25);cursor:pointer;width:100%;display:flex;align-items:center;justify-content:center;gap:4px;margin-top:4px;">
                <i class="ph ph-upload-simple"></i> Upload Document
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- Modals below live INSIDE the Livewire root on purpose: Livewire only
     morphs the root element's subtree, so anything rendered outside it is
     silently dropped and the modal never appears. --}}

  {{-- REQUEST PAYMENT CHARGE MODAL --}}
  <div x-data="{ open: @entangle('showChargeModal'), method: @entangle('chargeMethod'), get isCard() { return ['debit_card', 'credit_card', 'amex'].includes(this.method) }, get isCc() { return ['epay_credit', 'credit_card', 'debit_card', 'amex', 'klarna', 'clearpay'].includes(this.method) } }" x-show="open" x-cloak class="charge-overlay"
    style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);"
    @click="open = false" @keydown.escape.window="open = false">
    <div
      style="background:#fff;border-radius:20px;width:100%;max-width:600px;box-shadow:0 32px 96px rgba(0,0,0,.35),0 8px 32px rgba(51,46,158,.15);overflow:hidden;max-height:92vh;display:flex;flex-direction:column;"
      @click.stop>
      <div
        style="padding:22px 28px;background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <h5 class="fw-bold mb-0" style="font-size:1.14rem;display:flex;align-items:center;gap:10px;"><i
            class="ph ph-credit-card" style="font-size:1.32rem;"></i> Request Payment Charge</h5>
        <button type="button" @click="open = false"
          style="background:rgba(255,255,255,.18);color:#fff;border:none;border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.08rem;transition:all .15s;">✕</button>
      </div>
      <div style="overflow-y:auto;padding:24px 28px;">

        {{-- Payment Method radio cards --}}
        <div
          style="font-size:0.816rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#475569;margin-bottom:14px;">
          Payment Method</div>
        @php
          $modalMethods = [
              'epay_debit' => ['label' => 'Epay Debit', 'icon' => 'ph-credit-card', 'color' => '#332E9E'],
              'epay_credit' => ['label' => 'Epay Credit', 'icon' => 'ph-credit-card', 'color' => '#7C3AED'],
              'bank_transfer' => ['label' => 'Bank Transfer', 'icon' => 'ph-bank', 'color' => '#16A34A'],
              'debit_card' => ['label' => 'Debit Card', 'icon' => 'ph-credit-card', 'color' => '#0EA5E9'],
              'credit_card' => ['label' => 'Credit Card', 'icon' => 'ph-credit-card', 'color' => '#D97706'],
              'amex' => ['label' => 'AMEX', 'icon' => 'ph-credit-card', 'color' => '#1E40AF'],
              'klarna' => ['label' => 'Klarna', 'icon' => 'ph-storefront', 'color' => '#FF69B4'],
              'superpay' => ['label' => 'SuperPay', 'icon' => 'ph-lightning', 'color' => '#DC2626'],
              'clearpay' => ['label' => 'ClearPay', 'icon' => 'ph-arrows-clockwise', 'color' => '#047857'],
              'stripe' => ['label' => 'Stripe', 'icon' => 'ph-lightning', 'color' => '#6366F1'],
              'cash' => ['label' => 'Cash', 'icon' => 'ph-money', 'color' => '#15803D'],
          ];
        @endphp
        {{-- Instant client-side highlight via the native radio's :checked state,
             so selection no longer waits for the Livewire round-trip. --}}
        <style>
          .charge-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
          }

          .pm-radio:checked+div {
            border-color: var(--pm) !important;
            background: var(--pm-tint) !important;
          }

          .pm-radio:checked+div i,
          .pm-radio:checked+div span {
            color: var(--pm) !important;
          }

          .pm-radio:checked+div span {
            font-weight: 700 !important;
          }
        </style>
        <div class="row g-2 mb-4">
          @foreach ($modalMethods as $val => $m)
            <div class="col-4 col-md-3">
              <label style="cursor:pointer;display:block;">
                <input type="radio" name="chargeMethod" wire:model.live="chargeMethod"
                  value="{{ $val }}" style="display:none;" class="pm-radio">
                <div class="px-3 py-3 d-flex flex-column align-items-center gap-2 text-center"
                  style="--pm:{{ $m['color'] }};--pm-tint:{{ $m['color'] }}12;border-radius:12px;border:2px solid {{ $chargeMethod === $val ? $m['color'] : 'rgba(51,46,158,.08)' }};background:{{ $chargeMethod === $val ? $m['color'] . '12' : '#fff' }};transition:all .2s ease;cursor:pointer;"
                  onmouseover="if(!this.querySelector('input:checked')){this.style.borderColor='{{ $m['color'] }}';this.style.background='{{ $m['color'] }}08';}"
                  onmouseout="if(!this.querySelector('input:checked')){this.style.borderColor='rgba(51,46,158,.08)';this.style.background='#fff';}">
                  <i class="ph {{ $m['icon'] }}"
                    style="font-size:1.32rem;color:{{ $chargeMethod === $val ? $m['color'] : '#94A3B8' }};flex-shrink:0;transition:all .2s ease;"></i>
                  <span
                    style="font-size:0.84rem;font-weight:{{ $chargeMethod === $val ? '700' : '600' }};color:{{ $chargeMethod === $val ? $m['color'] : '#374151' }};line-height:1.2;">{{ $m['label'] }}</span>
                </div>
              </label>
            </div>
          @endforeach
        </div>
        @error('chargeMethod')
          <small class="text-danger d-block mb-3" style="margin-top:-8px;">{{ $message }}</small>
        @enderror

        {{-- Card details when debit/credit/amex selected — toggled client-side for instant show/hide --}}
        <div x-show="isCard" x-cloak
          style="padding:16px;border-radius:14px;background:linear-gradient(135deg,#F8FAFF,#EEF2FF);border:1px solid rgba(51,46,158,.10);margin-bottom:16px;">
          <div
            style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#332E9E;margin-bottom:12px;">
            Card Details</div>
          <div class="mb-3">
            <label class="bv-label">Card Number</label>
            <input type="text" wire:model="card_number" class="bv-input-inline"
              style="width:100%;padding:8px 12px;font-size:0.984rem;" placeholder="1234 5678 9012 3456"
              maxlength="19">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6"><label class="bv-label">Expiry</label><input type="text" wire:model="card_expiry"
                class="bv-input-inline" style="padding:8px 12px;font-size:0.984rem;" placeholder="MM/YY"
                maxlength="5"></div>
            <div class="col-6"><label class="bv-label">CVV</label><input type="text" wire:model="card_cvv"
                class="bv-input-inline" style="padding:8px 12px;font-size:0.984rem;" placeholder="123"
                maxlength="4"></div>
          </div>
          <div>
            <label class="bv-label">Cardholder Name</label>
            <input type="text" wire:model="card_holder_name" class="bv-input-inline"
              style="width:100%;padding:8px 12px;font-size:0.984rem;" placeholder="Name on card">
          </div>
        </div>

        {{-- Amount & Receipt — CC columns toggle client-side so the layout reflows instantly --}}
        <div class="row g-3 mb-2">
          <div :class="isCc ? 'col-3' : 'col-6'">
            <label class="bv-label">Amount (&pound;)</label>
            <input type="number" wire:model.live.debounce.500ms="chargeAmount" step="0.01" min="1"
              class="bv-input-inline" style="width:100%;padding:8px 12px;font-size:1.02rem;font-weight:700;"
              placeholder="0.00">
            @error('chargeAmount')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>
          <div class="col-3" x-show="isCc" x-cloak>
            <label class="bv-label">CC Charge Rate (%)</label>
            <input type="number" wire:model.live.debounce.500ms="chargeCcRate" step="0.1" min="0"
              max="100" class="bv-input-inline" style="width:100%;padding:8px 12px;font-size:0.984rem;"
              placeholder="2.5">
          </div>
          <div class="col-3" x-show="isCc" x-cloak>
            <label class="bv-label">CC Charge (&pound;)</label>
            <div
              style="padding:8px 12px;font-size:1.02rem;font-weight:700;color:#DC2626;background:#FFF1F0;border-radius:10px;border:1px solid rgba(220,38,38,.12);">
              &pound;{{ number_format((float) $chargeCcAmount, 2) }}</div>
          </div>
          <div :class="isCc ? 'col-3' : 'col-6'">
            <label class="bv-label">Receipt # (optional)</label>
            <input type="text" wire:model="chargeReceipt" class="bv-input-inline"
              style="width:100%;padding:8px 12px;font-size:0.984rem;" placeholder="Receipt number">
          </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4 pt-3" style="border-top:1px solid rgba(51,46,158,.08);">
          <button type="button" @click="open = false"
            style="background:transparent;border:1.5px solid rgba(51,46,158,.18);color:#475569;border-radius:10px;padding:9px 24px;font-size:0.936rem;font-weight:600;cursor:pointer;transition:all .15s;">Cancel</button>
          <button type="button" wire:click="requestPaymentCharge"
            style="background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;padding:9px 28px;font-size:0.936rem;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(255,107,53,.3);transition:all .15s;">Request
            Charge</button>
        </div>
      </div>
    </div>
  </div>

  {{-- REQUEST REFUND MODAL --}}
  <div x-data="{ open: @entangle('showRefundModal') }" x-show="open" x-cloak class="refund-overlay"
    style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;padding:16px;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);"
    @click="open = false" @keydown.escape.window="open = false">
    <div
      style="background:#fff;border-radius:18px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);"
      @click.stop>
      <div
        style="background:linear-gradient(135deg,#DC2626,#EF4444);padding:16px 22px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:1;">
        <h5 class="fw-bold mb-0" style="font-size:1.02rem;color:#fff;display:flex;align-items:center;gap:8px;"><i
            class="ph ph-arrows-counter-clockwise" style="font-size:1.14rem;"></i> Request Refund</h5>
        <button type="button" @click="open = false"
          style="background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.9rem;">✕</button>
      </div>
      <div class="p-3">
        <div class="mb-3 px-3 py-2"
          style="background:#FAFBFF;border-radius:10px;border:1px solid rgba(51,46,158,.08);font-size:0.8rem;color:#475569;">
          <strong style="color:#1E293B;">{{ $booking->booker_name ?: 'N/A' }}</strong>
          <span class="text-muted"> · Booking #{{ $booking->booking_number ?? $booking->id }}</span>
        </div>

        <div class="mb-2">
          <span
            style="font-size:0.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94A3B8;">Total
            Expected Refund</span>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <label class="bv-label">Amount (£) <span style="color:#DC2626;">*</span></label>
            <input type="number" wire:model="refundAmount" class="rf-input" placeholder="0.00" min="0.01"
              step="0.01">
            @error('refundAmount')
              <div style="font-size:0.75rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-2">
          <span
            style="font-size:0.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94A3B8;">Passenger(s)
            On This Refund</span>
          <button type="button" wire:click="addRefundLine"
            style="background:rgba(51,46,158,.06);border:1.5px dashed rgba(51,46,158,.25);color:#332E9E;border-radius:7px;padding:2px 9px;font-size:0.72rem;font-weight:600;cursor:pointer;">+
            Add Person</button>
        </div>

        @foreach ($refundLines as $li => $line)
          <div class="refund-card">
            <div class="refund-card-title">
              <div>
                <div>Passenger {{ $li + 1 }}</div>
                <div class="refund-card-subtitle">Select the passenger and refund details below.</div>
              </div>
              @if (count($refundLines) > 1)
                <button type="button" wire:click="removeRefundLine({{ $li }})"
                  class="refund-remove-btn">
                  Remove
                </button>
              @endif
            </div>

            <div class="row g-3 align-items-end">
              <div class="col-md-5">
                <label class="bv-label">Passenger <span style="color:#DC2626;">*</span></label>
                <select wire:model="refundLines.{{ $li }}.passenger_id" class="rf-input">
                  @foreach ($passengers as $pax)
                    <option value="{{ $pax['id'] }}">
                      {{ trim(($pax['first_name'] ?? '') . ' ' . ($pax['last_name'] ?? '')) ?: 'Passenger ' . $loop->iteration }}
                      ({{ ucfirst($pax['type'] ?? 'adult') }})
                    </option>
                  @endforeach
                </select>
                @error("refundLines.{$li}.passenger_id")
                  <div style="font-size:0.75rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-7">
                <label class="bv-label">E-Ticket Number</label>
                <input type="text" wire:model="refundLines.{{ $li }}.e_ticket_number"
                  class="rf-input" placeholder="e.g. 176-2345678901">
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-md-4">
                <label class="bv-label">GDS Locator</label>
                <select wire:model="refundLines.{{ $li }}.gds_locator" class="rf-input">
                  <option value="">Select GDS locator</option>
                  @foreach ($refundGdsLocatorOptions as $locator)
                    <option value="{{ $locator }}">{{ $locator }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="bv-label">Airline Locator</label>
                <select wire:model="refundLines.{{ $li }}.airline_locator" class="rf-input">
                  <option value="">Select airline locator</option>
                  @foreach ($refundAirlineLocatorOptions as $locator)
                    <option value="{{ $locator }}">{{ $locator }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="bv-label">Airline Waiver Code</label>
                <input type="text" wire:model="refundLines.{{ $li }}.airline_waiver_code"
                  class="rf-input" placeholder="e.g. N/A">
              </div>
            </div>
          </div>
        @endforeach

        <div class="mb-3 mt-3">
          <label class="bv-label">Penalties <span style="color:#DC2626;">*</span></label>
          <textarea wire:model="refundReason" rows="3" class="rf-input"
            style="resize:vertical;transition:border-color .15s;" onfocus="this.style.borderColor='#4A45B5'"
            onblur="this.style.borderColor='#332E9E'"
            placeholder="Describe any cancellation penalties applied and the reason for this refund…"></textarea>
          @error('refundReason')
            <div style="font-size:0.75rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>
          @enderror
        </div>

        <div class="d-flex gap-2 justify-content-end mt-3 pt-3" style="border-top:1px solid rgba(51,46,158,.06);">
          <button type="button" @click="open = false"
            style="background:transparent;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:10px;padding:7px 20px;font-size:0.84rem;font-weight:600;cursor:pointer;">Cancel</button>
          <button type="button" wire:click="submitRefund"
            style="background:linear-gradient(135deg,#DC2626,#EF4444);color:#fff;border:none;border-radius:10px;padding:7px 20px;font-size:0.84rem;font-weight:700;cursor:pointer;box-shadow:0 3px 12px rgba(220,38,38,.25);">Submit
            Refund Request</button>
        </div>
      </div>
    </div>
  </div>

  {{-- CHARGE REFUND PAYMENT MODAL --}}
  <div x-data="{ open: @entangle('showRefundChargeModal') }" x-show="open" x-cloak class="refund-overlay"
    style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;padding:16px;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);"
    @click="open = false" @keydown.escape.window="open = false">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.2);"
      @click.stop>
      <div
        style="background:linear-gradient(135deg,#DC2626,#EF4444);padding:16px 22px;display:flex;align-items:center;justify-content:space-between;">
        <h5 class="fw-bold mb-0" style="font-size:1.02rem;color:#fff;display:flex;align-items:center;gap:8px;"><i
            class="ph ph-arrows-counter-clockwise" style="font-size:1.14rem;"></i> Charge Refund Payment</h5>
        <button type="button" @click="open = false"
          style="background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;width:26px;height:26px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.9rem;">✕</button>
      </div>
      <div class="p-3">
        <p style="font-size:0.8rem;color:#475569;margin-bottom:14px;">Records the refund as paid out to the customer —
          it appears in Payment History as a deduction and closes this booking's refund.</p>
        <div class="mb-3">
          <label class="bv-label">Amount (£) <span style="color:#DC2626;">*</span></label>
          <input type="number" wire:model="refundChargeAmount" class="rf-input" placeholder="0.00"
            min="0.01" step="0.01">
          @error('refundChargeAmount')
            <div style="font-size:0.75rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label class="bv-label">Comment <span style="color:#DC2626;">*</span></label>
          <textarea wire:model="refundChargeComment" rows="3" class="rf-input"
            placeholder="e.g. paid via bank transfer, ref ABC123"></textarea>
          @error('refundChargeComment')
            <div style="font-size:0.75rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>
          @enderror
        </div>
        <div class="d-flex gap-2 justify-content-end mt-3 pt-3" style="border-top:1px solid rgba(51,46,158,.06);">
          <button type="button" @click="open = false"
            style="background:transparent;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:10px;padding:7px 20px;font-size:0.84rem;font-weight:600;cursor:pointer;">Cancel</button>
          <button type="button" wire:click="submitRefundChargePayment"
            style="background:linear-gradient(135deg,#DC2626,#EF4444);color:#fff;border:none;border-radius:10px;padding:7px 20px;font-size:0.84rem;font-weight:700;cursor:pointer;box-shadow:0 3px 12px rgba(220,38,38,.25);">Confirm
            Refund Paid</button>
        </div>
      </div>
    </div>
  </div>

  {{-- VOID PAYMENT MODAL --}}
  @if ($showVoidModal)
    <div
      style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
      <div
        style="background:#fff;border-radius:18px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;">
        <div
          style="background:linear-gradient(135deg,#DC2626,#EF4444);padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
          <h5 class="fw-bold mb-0" style="font-size:1.08rem;color:#fff;display:flex;align-items:center;gap:8px;"><i
              class="ph ph-prohibit" style="font-size:1.2rem;"></i> Void Payment</h5>
          <button type="button" wire:click="closeVoidModal"
            style="background:rgba(255,255,255,.15);color:#fff;border:none;border-radius:6px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.96rem;">✕</button>
        </div>
        <div class="p-4">
          <p style="font-size:0.876rem;color:#475569;margin-bottom:16px;">This marks the payment as voided — it drops
            out of the received/balance totals but stays visible in Payment History with your reason attached. This
            can't be undone.</p>
          <div class="mb-3">
            <label class="bv-label">Reason for Voiding <span style="color:#DC2626;">*</span></label>
            <textarea wire:model="voidReason" rows="3" class="bv-input-inline" style="width:100%;font-size:0.936rem;"
              placeholder="e.g. Approved by mistake, wrong booking..."></textarea>
            @error('voidReason')
              <div style="font-size:0.816rem;color:#DC2626;margin-top:3px;">{{ $message }}</div>
            @enderror
          </div>
          <div class="d-flex gap-2 justify-content-end mt-4 pt-3" style="border-top:1px solid rgba(51,46,158,.06);">
            <button type="button" wire:click="closeVoidModal"
              style="background:transparent;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:10px;padding:8px 22px;font-size:0.876rem;font-weight:600;cursor:pointer;">Cancel</button>
            <button type="button" wire:click="executeVoidPayment"
              style="background:linear-gradient(135deg,#DC2626,#EF4444);color:#fff;border:none;border-radius:10px;padding:8px 22px;font-size:0.876rem;font-weight:700;cursor:pointer;box-shadow:0 3px 12px rgba(220,38,38,.25);">Void
              Payment</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- SHARE MARGIN MODAL --}}
  @if ($shareMarginOpen)
    <div
      style="position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);animation:bvFadeIn .15s ease-out;">
      <div
        style="background:#fff;border-radius:20px;width:100%;max-width:460px;box-shadow:0 24px 70px rgba(15,23,42,.28);overflow:hidden;animation:bvPopIn .18s cubic-bezier(.34,1.56,.64,1);">
        <div
          style="background:linear-gradient(135deg,#332E9E 0%,#4A45B5 60%,#5B54D6 100%);padding:22px 26px;position:relative;overflow:hidden;">
          <div
            style="position:absolute;top:-30px;right:-20px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.08);">
          </div>
          <div
            style="position:absolute;bottom:-40px;right:40px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.06);">
          </div>
          <div class="d-flex align-items-start justify-content-between" style="position:relative;">
            <div class="d-flex align-items-center gap-3">
              <div
                style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.16);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ph ph-share-network" style="font-size:1.32rem;color:#fff;"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0" style="font-size:1.104rem;color:#fff;">Share Margin</h5>
                <div style="font-size:0.792rem;color:rgba(255,255,255,.75);">Booking #{{ $booking->booking_number }}
                  · Net Margin £{{ number_format($this->totalMargin, 2) }}</div>
              </div>
            </div>
            <button type="button" wire:click="$set('shareMarginOpen',false)"
              style="background:rgba(255,255,255,.14);color:#fff;border:none;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.96rem;flex-shrink:0;">✕</button>
          </div>
        </div>
        <div class="p-4" style="padding:24px 26px 26px !important;">
          @if ($this->shareCandidateUsers->isEmpty())
            <div class="text-center" style="padding:18px 8px;">
              <i class="ph ph-users" style="font-size:1.8rem;color:#94A3B8;"></i>
              <p class="mb-0 mt-2" style="font-size:0.876rem;color:#64748B;">No other agents available to share this
                margin with.</p>
            </div>
          @else
            <div class="mb-3">
              <label class="bv-label">Share With <span style="color:#DC2626;">*</span></label>
              <div style="position:relative;">
                <i class="ph ph-user"
                  style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#332E9E;font-size:1rem;pointer-events:none;"></i>
                <select wire:model="shareUserId" class="bv-select-inline"
                  style="width:100%;font-size:0.936rem;padding-left:32px;">
                  <option value="">Select an agent…</option>
                  @foreach ($this->shareCandidateUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>
              @error('shareUserId')
                <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i
                    class="ph ph-warning-circle"></i>{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <label class="bv-label">Amount <span style="color:#DC2626;">*</span></label>
              <div style="position:relative;">
                <span
                  style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#332E9E;font-size:0.936rem;font-weight:700;">£</span>
                <input type="number" wire:model="shareAmount" class="bv-input-inline"
                  style="width:100%;font-size:0.984rem;font-weight:700;padding-left:26px;" placeholder="0.00"
                  min="0.01" step="0.01">
              </div>
              @error('shareAmount')
                <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i
                    class="ph ph-warning-circle"></i>{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-1">
              <label class="bv-label">Note (optional)</label>
              <textarea wire:model="shareNote" rows="2" class="bv-input-inline"
                style="width:100%;font-size:0.912rem;resize:none;" placeholder="Why is this margin being shared?"></textarea>
              @error('shareNote')
                <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i
                    class="ph ph-warning-circle"></i>{{ $message }}</div>
              @enderror
            </div>
          @endif
          <div class="d-flex gap-2 justify-content-end mt-4 pt-3" style="border-top:1px solid rgba(51,46,158,.08);">
            <button type="button" wire:click="$set('shareMarginOpen',false)"
              style="background:transparent;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:10px;padding:9px 22px;font-size:0.876rem;font-weight:600;cursor:pointer;">Cancel</button>
            @if ($this->shareCandidateUsers->isNotEmpty())
              <button type="button" wire:click="saveMarginShare"
                style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:10px;padding:9px 24px;font-size:0.876rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(51,46,158,.3);display:flex;align-items:center;gap:6px;">
                <i class="ph ph-check-circle"></i> Save Share
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
    <style>
      @keyframes bvFadeIn {
        from {
          opacity: 0;
        }

        to {
          opacity: 1;
        }
      }

      @keyframes bvPopIn {
        from {
          opacity: 0;
          transform: scale(.94) translateY(6px);
        }

        to {
          opacity: 1;
          transform: scale(1) translateY(0);
        }
      }
    </style>
  @endif

  {{-- TRANSFER BOOKING MODAL --}}
  @if ($transferBookingOpen)
    <div
      style="position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);animation:bvFadeIn .15s ease-out;">
      <div
        style="background:#fff;border-radius:20px;width:100%;max-width:460px;box-shadow:0 24px 70px rgba(15,23,42,.28);overflow:hidden;animation:bvPopIn .18s cubic-bezier(.34,1.56,.64,1);">
        <div
          style="background:linear-gradient(135deg,#B45309 0%,#D97706 60%,#F59E0B 100%);padding:22px 26px;position:relative;overflow:hidden;">
          <div
            style="position:absolute;top:-30px;right:-20px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.08);">
          </div>
          <div
            style="position:absolute;bottom:-40px;right:40px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.06);">
          </div>
          <div class="d-flex align-items-start justify-content-between" style="position:relative;">
            <div class="d-flex align-items-center gap-3">
              <div
                style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.16);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="ph ph-arrows-left-right" style="font-size:1.32rem;color:#fff;"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-0" style="font-size:1.104rem;color:#fff;">Transfer Booking</h5>
                <div style="font-size:0.792rem;color:rgba(255,255,255,.75);">Booking #{{ $booking->booking_number }}
                  · Currently owned by {{ $booking->user?->name }}</div>
              </div>
            </div>
            <button type="button" wire:click="$set('transferBookingOpen',false)"
              style="background:rgba(255,255,255,.14);color:#fff;border:none;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.96rem;flex-shrink:0;">✕</button>
          </div>
        </div>
        <div class="p-4" style="padding:24px 26px 26px !important;">
          <div class="mb-3" style="background:#FFF7ED;border:1px solid rgba(217,119,6,.15);border-radius:10px;padding:10px 12px;">
            <span style="font-size:0.78rem;color:#92400E;line-height:1.4;">
              The booking moves in full — its sale, margin and refunds will count toward the new owner from now on.
              Nothing already logged is changed; this transfer is recorded as a new entry.
            </span>
          </div>
          @if ($this->transferCandidateUsers->isEmpty())
            <div class="text-center" style="padding:18px 8px;">
              <i class="ph ph-users" style="font-size:1.8rem;color:#94A3B8;"></i>
              <p class="mb-0 mt-2" style="font-size:0.876rem;color:#64748B;">No other users available to transfer
                this booking to.</p>
            </div>
          @else
            <div class="mb-3">
              <label class="bv-label">Transfer To <span style="color:#DC2626;">*</span></label>
              <div style="position:relative;">
                <i class="ph ph-user"
                  style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#B45309;font-size:1rem;pointer-events:none;"></i>
                <select wire:model="transferToUserId" class="bv-select-inline"
                  style="width:100%;font-size:0.936rem;padding-left:32px;">
                  <option value="">Select a user…</option>
                  @foreach ($this->transferCandidateUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>
              @error('transferToUserId')
                <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i
                    class="ph ph-warning-circle"></i>{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-1">
              <label class="bv-label">Reason (optional)</label>
              <textarea wire:model="transferNote" rows="2" class="bv-input-inline"
                style="width:100%;font-size:0.912rem;resize:none;" placeholder="Why is this booking being transferred?"></textarea>
              @error('transferNote')
                <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i
                    class="ph ph-warning-circle"></i>{{ $message }}</div>
              @enderror
            </div>
          @endif
          <div class="d-flex gap-2 justify-content-end mt-4 pt-3" style="border-top:1px solid rgba(217,119,6,.1);">
            <button type="button" wire:click="$set('transferBookingOpen',false)"
              style="background:transparent;border:1.5px solid rgba(217,119,6,.2);color:#475569;border-radius:10px;padding:9px 22px;font-size:0.876rem;font-weight:600;cursor:pointer;">Cancel</button>
            @if ($this->transferCandidateUsers->isNotEmpty())
              <button type="button" wire:click="saveTransferBooking"
                onclick="return confirm('Transfer this booking? Its sale, margin and refunds will count toward the new owner.')"
                style="background:linear-gradient(135deg,#B45309,#D97706);color:#fff;border:none;border-radius:10px;padding:9px 24px;font-size:0.876rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(217,119,6,.3);display:flex;align-items:center;gap:6px;">
                <i class="ph ph-check-circle"></i> Transfer
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endif

</div>
