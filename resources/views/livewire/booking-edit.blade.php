<div>
<style>
.be-section { background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);margin-bottom:20px; }
.be-hdr { padding:14px 22px;border-bottom:1px solid rgba(51,46,158,.06);display:flex;align-items:center;gap:10px; }
.be-hdr .be-icon { width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.be-hdr h2 { font-size:.88rem;font-weight:700;color:#0F172A;margin:0; }
.be-body { padding:20px 22px; }
.be-label { display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;margin-bottom:5px; }
.be-input { border-radius:10px;border:1.5px solid rgba(51,46,158,.12);padding:.45rem .75rem;font-size:.83rem;width:100%;background:#fff;color:#1E293B;transition:border-color .15s; }
.be-input:focus { border-color:#332E9E;box-shadow:0 0 0 3px rgba(51,46,158,.08);outline:none; }
.be-select { border-radius:10px;border:1.5px solid rgba(51,46,158,.12);padding:.42rem .75rem;font-size:.83rem;width:100%;background:#fff;color:#1E293B; }
.be-select:focus { border-color:#332E9E;outline:none; }
.pax-table th { font-size:.6rem;text-transform:uppercase;letter-spacing:.05em;color:#94A3B8;font-weight:700;padding:8px 10px;border:none;background:rgba(51,46,158,.03);white-space:nowrap; }
.pax-table td { padding:7px 8px;border-color:rgba(51,46,158,.05);vertical-align:middle; }
.pax-table input,.pax-table select { border-radius:7px;border:1px solid rgba(51,46,158,.12);padding:4px 7px;font-size:.75rem;background:#fff;color:#1E293B;width:100%; }
.pax-table input:focus,.pax-table select:focus { border-color:#332E9E;outline:none; }
.be-counter { display:flex;align-items:center;gap:1px;background:#fff;border:1px solid rgba(51,46,158,.1);border-radius:10px;overflow:hidden; }
.be-counter-lbl { font-size:.65rem;font-weight:700;padding:0 10px;border-right:1px solid rgba(51,46,158,.08); }
.be-counter-btn { width:28px;height:32px;border:none;background:transparent;cursor:pointer;font-size:1rem;color:#332E9E;transition:background .12s; }
.be-counter-btn:hover { background:rgba(51,46,158,.08); }
.be-counter-val { width:28px;text-align:center;font-size:.9rem;font-weight:700;color:#1E293B; }
</style>

{{-- PAGE HEADER --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <div style="font-size:.72rem;color:#94A3B8;margin-bottom:4px;">
      <a href="{{ route('dashboard') }}" style="color:#94A3B8;text-decoration:none;">Dashboard</a> ›
      <a href="{{ route('bookings.index') }}" style="color:#94A3B8;text-decoration:none;">Bookings</a> ›
      <a href="{{ route('bookings.show', $booking) }}" style="color:#94A3B8;text-decoration:none;">#{{ $booking->booking_number }}</a> ›
      <span style="color:#332E9E;font-weight:600;">Edit</span>
    </div>
    <h1 style="font-size:1.55rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;margin:0;">Edit Booking #{{ $booking->booking_number }}</h1>
    <div style="font-size:.74rem;color:#94A3B8;margin-top:3px;">Last updated {{ $booking->updated_at->diffForHumans() }}</div>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('bookings.show', $booking) }}" style="padding:8px 18px;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);color:#374151;font-size:.81rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
      <i class="ph ph-arrow-left"></i> Cancel
    </a>
    <button type="submit" form="edit-booking-form"
      style="background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:10px;padding:8px 22px;font-size:.81rem;font-weight:700;cursor:pointer;box-shadow:0 3px 12px rgba(255,107,53,.3);display:inline-flex;align-items:center;gap:5px;">
      <i class="ph ph-check-circle"></i> Save Changes
    </button>
  </div>
</div>

@if(session()->has('success'))
  <div class="d-flex align-items-center gap-2 mb-3 px-4 py-3" style="background:rgba(22,163,74,.08);border-radius:12px;border:1px solid rgba(22,163,74,.2);color:#15803D;font-size:.82rem;">
    <i class="ph ph-check-circle" style="font-size:1.1rem;"></i> {{ session('success') }}
  </div>
@endif
@if($errors->any())
  <div class="mb-3 px-4 py-3" style="background:rgba(220,38,38,.06);border-radius:12px;border:1px solid rgba(220,38,38,.15);font-size:.79rem;color:#DC2626;">
    <div class="fw-semibold mb-1"><i class="ph ph-warning-circle me-1"></i>Please fix the following:</div>
    <ul class="mb-0 ps-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<form id="edit-booking-form" wire:submit="save" enctype="multipart/form-data">
<div class="row g-0">
<div class="col-lg-8 pe-lg-3">

{{-- ═══ BOOKING INFO ═══ --}}
<div class="be-section">
  <div class="be-hdr">
    <div class="be-icon" style="background:rgba(51,46,158,.08);"><i class="ph ph-clipboard-text" style="color:#332E9E;font-size:1rem;"></i></div>
    <h2>Booking Information</h2>
  </div>
  <div class="be-body">
    <div class="row g-3">
      <div class="col-md-3">
        <label class="be-label">Lead Source</label>
        <x-styled-select modelName="lead_source" :options="[['value'=>'','label'=>'Select'],['value'=>'to_returning','label'=>'TO Returning'],['value'=>'to_referral','label'=>'TO Referral'],['value'=>'referral_client','label'=>'Referral Client'],['value'=>'returning_client','label'=>'Returning Client'],['value'=>'fb','label'=>'Facebook'],['value'=>'wa','label'=>'WhatsApp'],['value'=>'email','label'=>'Email'],['value'=>'diaspora_group','label'=>'Diaspora Group'],['value'=>'instagram','label'=>'Instagram'],['value'=>'tiktok','label'=>'TikTok'],['value'=>'website','label'=>'Website'],['value'=>'google','label'=>'Google']]" placeholder="Select" />
      </div>
      <div class="col-md-3">
        <label class="be-label">Lead Nature</label>
        <x-styled-select modelName="lead_nature" :options="[['value'=>'','label'=>'Select'],['value'=>'new_booking','label'=>'New Booking'],['value'=>'date_change','label'=>'Date Change'],['value'=>'refund_booking','label'=>'Refund Booking'],['value'=>'previous_booking','label'=>'Previous Booking']]" placeholder="Select" />
      </div>
      <div class="col-md-3">
        <label class="be-label">Booking Type <span class="text-danger">*</span></label>
        <x-styled-select modelName="booking_type" :options="[['value'=>'','label'=>'Select'],['value'=>'flight','label'=>'Flight'],['value'=>'hotel','label'=>'Hotel'],['value'=>'umrah','label'=>'Umrah'],['value'=>'holiday','label'=>'Holiday'],['value'=>'visa','label'=>'Visa'],['value'=>'transfers','label'=>'Transfers'],['value'=>'excursion','label'=>'Excursion']]" placeholder="Select" />
      </div>
      <div class="col-md-4"><label class="be-label">Old Booking Reference</label><input type="text" wire:model="old_booking_reference" class="be-input" placeholder="Previous booking ref"></div>
      <div class="col-md-2"><label class="be-label">Last Payment Date</label><x-date-picker modelName="last_payment_date" /></div>
      <div class="col-md-2"><label class="be-label">Last Issue Date</label><x-date-picker modelName="last_issue_date" /></div>
    </div>
  </div>
</div>

{{-- ═══ CALLER ═══ --}}
<div class="be-section">
  <div class="be-hdr">
    <div class="be-icon" style="background:rgba(14,165,233,.08);"><i class="ph ph-user-circle" style="color:#0EA5E9;font-size:1rem;"></i></div>
    <h2>Caller / Booker</h2>
  </div>
  <div class="be-body">
    <div class="row g-3">
      <div class="col-md-1">
        <label class="be-label">Title</label>
        <x-styled-select modelName="booker_title" :options="[['value'=>'','label'=>'-'],['value'=>'Mr.','label'=>'Mr.'],['value'=>'Ms.','label'=>'Ms.'],['value'=>'Mrs.','label'=>'Mrs.'],['value'=>'Mstr','label'=>'Mstr'],['value'=>'Miss','label'=>'Miss'],['value'=>'Dr.','label'=>'Dr.']]" placeholder="-" />
      </div>
      <div class="col-md-3"><label class="be-label">First Name <span class="text-danger">*</span></label><input type="text" wire:model="booker_first_name" class="be-input @error('booker_first_name') border-danger @enderror" placeholder="First"></div>
      <div class="col-md-3"><label class="be-label">Last Name <span class="text-danger">*</span></label><input type="text" wire:model="booker_last_name" class="be-input @error('booker_last_name') border-danger @enderror" placeholder="Last"></div>
      <div class="col-md-2"><label class="be-label">Mobile <span class="text-danger">*</span></label><input type="tel" wire:model="booker_mobile" class="be-input" placeholder="07xxx" oninput="this.value=this.value.replace(/[^0-9+]/g,'')"></div>
      <div class="col-md-2"><label class="be-label">Landline</label><input type="tel" wire:model="booker_landline" class="be-input" placeholder="01xxx" oninput="this.value=this.value.replace(/[^0-9+]/g,'')"></div>
      <div class="col-md-3"><label class="be-label">Email</label><input type="email" wire:model="booker_email" class="be-input" placeholder="email@example.com"></div>
      <div class="col-md-2"><label class="be-label">Postcode</label><input type="text" wire:model="booker_postcode" class="be-input" oninput="this.value=this.value.toUpperCase()"></div>
      @php $beCountryOpts = collect($countries)->map(fn($name,$code)=>['value'=>$code,'label'=>$name])->values()->toArray(); @endphp
      <div class="col-md-2"><label class="be-label">Country</label><x-styled-select modelName="booker_country" :options="$beCountryOpts" placeholder="Country" :searchable="true" /></div>
      <div class="col-md-5"><label class="be-label">Address</label><input type="text" wire:model="booker_address" class="be-input" placeholder="Street address"></div>
    </div>
  </div>
</div>

{{-- ═══ TRAVELLERS ═══ --}}
<div class="be-section">
  <div class="be-hdr">
    <div class="be-icon" style="background:rgba(22,163,74,.08);"><i class="ph ph-users" style="color:#16A34A;font-size:1rem;"></i></div>
    <h2>Travellers</h2>
    <span class="ms-auto" style="font-size:.72rem;color:#94A3B8;">{{ $this->totalPassengers }} passenger{{ $this->totalPassengers !== 1 ? 's' : '' }}</span>
  </div>
  <div class="be-body" style="padding-bottom:12px;">
    {{-- Counters --}}
    <div class="d-flex gap-2 mb-4 flex-wrap">
      @foreach(['adult'=>['ADT','#332E9E'],'gbe'=>['GBE','#D83F87'],'child'=>['CNN','#D97706'],'infant'=>['INF','#16A34A']] as $type=>[$code,$col])
        @php $cnt = ${$type.'Count'}; @endphp
        <div class="be-counter">
          <span class="be-counter-lbl" style="color:{{ $col }};min-width:38px;text-align:center;">{{ $code }}</span>
          <button type="button" wire:click="dec('{{ $type }}')" class="be-counter-btn">&minus;</button>
          <span class="be-counter-val">{{ $cnt }}</span>
          <button type="button" wire:click="inc('{{ $type }}')" class="be-counter-btn" style="border-left:1px solid rgba(51,46,158,.08);">+</button>
        </div>
      @endforeach
    </div>
    {{-- Passenger rows --}}
    @if(!empty($passengers))
    <div class="table-responsive">
      <table class="table pax-table mb-0">
        <thead>
          <tr><th>#</th><th>Type</th><th>PTC</th><th>Title</th><th>First Name</th><th>Last Name</th><th>DOB</th><th>Contact</th><th>Passport #</th><th>E-Ticket</th><th>Cost (£)</th><th>Sold (£)</th></tr>
        </thead>
        <tbody>
          @foreach($passengers as $i => $p)
            @php
              $ptColors = ['adult'=>'#332E9E','gbe'=>'#D83F87','child'=>'#D97706','infant'=>'#16A34A'];
              $ptLabels = ['adult'=>'ADT','gbe'=>'GBE','child'=>'CNN','infant'=>'INF'];
              $ptc = $p['ptc'] ?? '';
              $col = $ptColors[$p['type']] ?? '#6B7280';
            @endphp
            <tr style="{{ $loop->even ? 'background:#FAFBFF;' : '' }}">
              <td style="color:#94A3B8;font-size:.72rem;font-weight:600;">{{ $i+1 }}</td>
              <td><span style="background:{{ $col }}15;color:{{ $col }};padding:2px 8px;border-radius:20px;font-size:.62rem;font-weight:700;">{{ $ptLabels[$p['type']] ?? '-' }}</span></td>
              <td><span style="font-size:.7rem;font-weight:700;color:{{ $col }};">{{ $ptc ?: '-' }}</span></td>
              <td><x-styled-select-sm :modelName="'passengers.'.$i.'.title'" :options="[['value'=>'','label'=>'-'],['value'=>'Mr.','label'=>'Mr.'],['value'=>'Ms.','label'=>'Ms.'],['value'=>'Mrs.','label'=>'Mrs.'],['value'=>'Mstr','label'=>'Mstr'],['value'=>'Miss','label'=>'Miss'],['value'=>'Dr.','label'=>'Dr.']]" placeholder="-" /></td>
              <td><input type="text" wire:model="passengers.{{ $i }}.first_name" placeholder="First" style="min-width:90px;"></td>
              <td><input type="text" wire:model="passengers.{{ $i }}.last_name" placeholder="Last" style="min-width:90px;"></td>
              <td><x-date-picker :modelName="'passengers.'.$i.'.date_of_birth'" :compact="true" />@error("passengers.{$i}.date_of_birth")<small style="color:#DC2626;font-size:.62rem;">{{ $message }}</small>@enderror</td>
              <td><input type="tel" wire:model="passengers.{{ $i }}.contact_number" placeholder="07xxx" style="min-width:80px;" oninput="this.value=this.value.replace(/[^0-9+]/g,'')"></td>
              <td><input type="text" wire:model="passengers.{{ $i }}.passport_number" placeholder="Passport #" style="min-width:90px;"></td>
              <td><input type="text" wire:model="passengers.{{ $i }}.e_ticket_number" placeholder="e.g. 176-xxx" style="min-width:100px;font-family:monospace;"></td>
              <td><input type="number" wire:model.blur="passengers.{{ $i }}.cost_per_pax" step="0.01" min="0" placeholder="0.00" style="min-width:72px;"></td>
              <td><input type="number" wire:model.blur="passengers.{{ $i }}.sold_per_pax" step="0.01" min="0" placeholder="0.00" style="min-width:72px;"></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
      <div class="text-center py-3" style="color:#C4C9D4;font-size:.76rem;">Add travellers using the counters above.</div>
    @endif
  </div>
</div>

{{-- ═══ FLIGHT ═══ --}}
@php $canEditFlightHotel = in_array(Auth::user()->role, ['admin','manager','operations','issuance']); @endphp
<div class="be-section">
  <div class="be-hdr" style="{{ !$canEditFlightHotel ? 'background:rgba(51,46,158,.03);' : '' }}">
    <div class="be-icon" style="background:rgba(14,165,233,.08);"><i class="ph ph-airplane" style="color:#0EA5E9;font-size:1rem;"></i></div>
    <h2>Flight Information</h2>
    @if(!$canEditFlightHotel)
      <span class="ms-auto d-flex align-items-center gap-1" style="font-size:.7rem;color:#94A3B8;font-weight:600;">
        <i class="ph ph-lock" style="font-size:.8rem;"></i> Manager access only
      </span>
    @endif
  </div>
  @if(!$canEditFlightHotel)
    {{-- Read-only view for agents --}}
    <div class="be-body" style="background:rgba(51,46,158,.02);">
      @if($booking->flightDetail)
        @php $fd = $booking->flightDetail; @endphp
        <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2" style="background:rgba(14,165,233,.06);border-radius:10px;border:1px solid rgba(14,165,233,.15);">
          <i class="ph ph-info" style="color:#0EA5E9;font-size:1rem;"></i>
          <span style="font-size:.76rem;color:#0369A1;">Flight details can only be modified by a Manager. Contact your manager to add or change flight information.</span>
        </div>
        <div class="row g-3">
          @if($fd->pnr)<div class="col-md-3"><div class="be-label">PNR</div><div style="font-size:.83rem;font-weight:600;font-family:monospace;color:#1E293B;">{{ $fd->pnr }}</div></div>@endif
          @if($fd->airline)<div class="col-md-2"><div class="be-label">Airline</div><div style="font-size:.9rem;font-weight:800;color:#0EA5E9;">{{ $fd->airline }}</div></div>@endif
          @if($fd->departure_airport || $fd->arrival_airport)
            <div class="col-md-3"><div class="be-label">Route</div><div style="font-size:.88rem;font-weight:700;color:#1E293B;">{{ strtoupper($fd->departure_airport) }} → {{ strtoupper($fd->arrival_airport) }}</div></div>
          @endif
          @if($fd->departure_date)<div class="col-md-2"><div class="be-label">Departure</div><div style="font-size:.83rem;color:#374151;">{{ \Carbon\Carbon::parse($fd->departure_date)->format('d M Y') }}</div></div>@endif
          @if($fd->flight_type)<div class="col-md-2"><div class="be-label">Flight Type</div><div style="font-size:.83rem;font-weight:700;color:#1E293B;">{{ $fd->flight_type === 'one_way' ? 'One Way' : 'Return' }}</div></div>@endif
          @if(($fd->flight_type ?? 'return') !== 'one_way' && $fd->return_date)<div class="col-md-2"><div class="be-label">Return</div><div style="font-size:.83rem;color:#374151;">{{ \Carbon\Carbon::parse($fd->return_date)->format('d M Y') }}</div></div>@endif
        </div>
      @else
        <div class="d-flex align-items-center gap-2 px-3 py-2" style="background:rgba(51,46,158,.04);border-radius:10px;">
          <i class="ph ph-lock" style="color:#94A3B8;"></i>
          <span style="font-size:.76rem;color:#94A3B8;">No flight details added yet. Ask your manager to add flight information.</span>
        </div>
      @endif
    </div>
  @else
    <div class="be-body">
    <div class="row g-3 mb-3">
      <div class="col-md-6"><label class="be-label">PNR Details</label><textarea wire:model="flight_pnr" class="be-input" rows="3" placeholder="Paste PNR content..." style="font-family:monospace;font-size:.76rem;"></textarea></div>
      <div class="col-md-3"><label class="be-label">Locator</label><input type="text" wire:model="flight_locator" class="be-input"></div>
      <div class="col-md-3"><label class="be-label">Airline Locator</label><input type="text" wire:model="flight_airline_locator" class="be-input"></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-2"><label class="be-label">Airline (2 chars)</label><input type="text" wire:model="flight_airline" class="be-input" maxlength="2" placeholder="EK" oninput="this.value=this.value.toUpperCase()"></div>
      <div class="col-md-3"><label class="be-label">Vendor</label><x-styled-select modelName="flight_vendor" :options="array_merge([['value'=>'','label'=>'Select Vendor']], $vendorOptions)" placeholder="Select Vendor" /></div>
      <div class="col-md-3"><label class="be-label">GDS</label><x-styled-select modelName="flight_gds" :options="[['value'=>'','label'=>'Select GDS'],['value'=>'AMADEUS','label'=>'Amadeus'],['value'=>'GALILEO','label'=>'Galileo'],['value'=>'SABRE','label'=>'Sabre'],['value'=>'WORLDSPAN','label'=>'Worldspan'],['value'=>'TRAVELPORT','label'=>'Travelport']]" placeholder="Select GDS" /></div>
      <div class="col-md-4"><label class="be-label">Cabin</label><x-styled-select modelName="flight_cabin" :options="[['value'=>'','label'=>'Select Cabin'],['value'=>'Economy','label'=>'Economy'],['value'=>'Premium Economy','label'=>'Premium Economy'],['value'=>'Business','label'=>'Business'],['value'=>'First Class','label'=>'First Class']]" placeholder="Select Cabin" /></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-md-3"><label class="be-label">Reservation Status</label><x-styled-select modelName="flight_reservation_status" :options="[['value'=>'','label'=>'Status'],['value'=>'Confirmed','label'=>'Confirmed'],['value'=>'Ticketed','label'=>'Ticketed'],['value'=>'Pending','label'=>'Pending'],['value'=>'On Hold','label'=>'On Hold'],['value'=>'Cancelled','label'=>'Cancelled']]" placeholder="Status" /></div>
      <div class="col-md-3"><label class="be-label">Ticket Issue Limit</label><x-date-picker modelName="flight_ticket_issue_limit" :isDateTime="true" /></div>
      <div class="col-md-2"><label class="be-label">City Code</label><input type="text" wire:model="flight_city_code" class="be-input" maxlength="5" oninput="this.value=this.value.toUpperCase()"></div>
      <div class="col-md-2"><label class="be-label">Dep. Airport</label><input type="text" wire:model="flight_departure_airport" class="be-input" maxlength="3" placeholder="LHR" oninput="this.value=this.value.toUpperCase().slice(0,3)"></div>
      <div class="col-md-2"><label class="be-label">Arr. Airport</label><input type="text" wire:model="flight_arrival_airport" class="be-input" maxlength="3" placeholder="DXB" oninput="this.value=this.value.toUpperCase().slice(0,3)"></div>
    </div>
    <div class="row g-3 align-items-end">
      <div class="col-md-2"><label class="be-label">Flight Type</label><x-styled-select modelName="flight_type" :options="[['value'=>'return','label'=>'Return'],['value'=>'one_way','label'=>'One Way']]" placeholder="" /></div>
      <div class="col-md-3"><label class="be-label">Departure Date</label><x-date-picker modelName="flight_departure_date" /></div>
      @if($flight_type !== 'one_way')
      <div class="col-md-3"><label class="be-label">Return Date</label><x-date-picker modelName="flight_return_date" /></div>
      @endif
      <div class="col-md-3 d-flex gap-4 pb-1">
        <label class="d-flex align-items-center gap-2 mb-0 cursor-pointer" style="cursor:pointer;font-size:.8rem;font-weight:600;">
          <input type="checkbox" wire:model="flight_atol" style="width:16px;height:16px;cursor:pointer;accent-color:#FF6B35;"> ATOL
        </label>
        <label class="d-flex align-items-center gap-2 mb-0 cursor-pointer" style="cursor:pointer;font-size:.8rem;font-weight:600;">
          <input type="checkbox" wire:model="flight_safi" style="width:16px;height:16px;cursor:pointer;accent-color:#332E9E;"> SAFI
        </label>
      </div>
    </div>
  </div>
  @endif {{-- end canEditFlightHotel flight --}}
</div>

{{-- ═══ HOTEL ═══ --}}
<div class="be-section">
  <div class="be-hdr" style="{{ !$canEditFlightHotel ? 'background:rgba(51,46,158,.03);' : '' }}">
    <div class="be-icon" style="background:rgba(124,58,237,.08);"><i class="ph ph-buildings" style="color:#7C3AED;font-size:1rem;"></i></div>
    <h2>Hotel Information</h2>
    @if(!$canEditFlightHotel)
      <span class="ms-auto d-flex align-items-center gap-1" style="font-size:.7rem;color:#94A3B8;font-weight:600;">
        <i class="ph ph-lock" style="font-size:.8rem;"></i> Manager access only
      </span>
    @else
      <span style="font-size:.7rem;color:#94A3B8;margin-left:4px;">(optional - add if customer wants hotel)</span>
    @endif
  </div>
  @if(!$canEditFlightHotel)
    <div class="be-body" style="background:rgba(51,46,158,.02);">
      @if($booking->hotels?->isNotEmpty())
        <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2" style="background:rgba(124,58,237,.06);border-radius:10px;border:1px solid rgba(124,58,237,.15);">
          <i class="ph ph-info" style="color:#7C3AED;font-size:1rem;"></i>
          <span style="font-size:.76rem;color:#6D28D9;">Hotel details can only be modified by a Manager.</span>
        </div>
        @foreach($booking->hotels as $hotel)
          <div class="mb-3" style="border:1px solid rgba(124,58,237,.10);border-radius:10px;padding:14px 16px;background:#fff;">
            <div class="fw-bold mb-2" style="font-size:.82rem;color:#7C3AED;">{{ $hotel->hotel_name }}</div>
            <div class="row g-2" style="font-size:.78rem;">
              <div class="col-md-3"><span style="color:#94A3B8;">City:</span> {{ $hotel->city ?? '-' }}</div>
              <div class="col-md-3"><span style="color:#94A3B8;">Status:</span> {{ ucfirst($hotel->booking_status) }}</div>
              <div class="col-md-3"><span style="color:#94A3B8;">Check In:</span> {{ $hotel->check_in?->format('d M Y') ?? '-' }}</div>
              <div class="col-md-3"><span style="color:#94A3B8;">Check Out:</span> {{ $hotel->check_out?->format('d M Y') ?? '-' }}</div>
              <div class="col-md-3"><span style="color:#94A3B8;">Cost:</span> £{{ number_format($hotel->actual_cost, 2) }}</div>
              <div class="col-md-3"><span style="color:#94A3B8;">Selling:</span> £{{ number_format($hotel->selling_price, 2) }}</div>
            </div>
            @if($hotel->rooms->isNotEmpty())
              <div class="mt-2 pt-2" style="border-top:1px solid rgba(124,58,237,.08);">
                <span style="font-size:.7rem;color:#7C3AED;font-weight:600;">Rooms ({{ $hotel->rooms->count() }})</span>
                <div class="d-flex flex-wrap gap-2 mt-1">
                  @foreach($hotel->rooms as $room)
                    <span class="badge" style="background:rgba(124,58,237,.06);color:#5A6080;font-size:.67rem;padding:3px 8px;border-radius:6px;">
                      R{{ $room->room_number }}: {{ $room->room_type ?: 'N/A' }} · {{ $room->occupants }}pax · {{ ucwords(str_replace('_',' ',$room->meal_basis)) }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
        @endforeach
      @else
        <div class="d-flex align-items-center gap-2 px-3 py-2" style="background:rgba(51,46,158,.04);border-radius:10px;">
          <i class="ph ph-lock" style="color:#94A3B8;"></i>
          <span style="font-size:.76rem;color:#94A3B8;">No hotel added. Ask your manager to add hotel details to this booking.</span>
        </div>
      @endif
    </div>
  @else
    <div class="be-body">
      @foreach($hotels as $hi => $hotel)
        <div class="mb-3" style="border:1px solid rgba(124,58,237,.08);border-radius:10px;overflow:hidden;">
          <div class="d-flex align-items-center justify-content-between px-3 py-2" style="background:rgba(124,58,237,.04);">
            <span class="fw-semibold" style="font-size:.76rem;color:#7C3AED;">Hotel {{ $hi + 1 }}</span>
            <button type="button" wire:click="removeHotel({{ $hi }})" style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:6px;padding:2px 10px;font-size:.68rem;cursor:pointer;">Remove</button>
          </div>
          <div class="p-3">
            <div class="row g-2 mb-2">
              <div class="col-md-4"><label class="be-label">Hotel Name</label><input type="text" wire:model="hotels.{{ $hi }}.hotel_name" class="be-input" placeholder="Hotel name"></div>
              <div class="col-md-2"><label class="be-label">City</label><input type="text" wire:model="hotels.{{ $hi }}.city" class="be-input" placeholder="City"></div>
              <div class="col-md-2"><label class="be-label">Status</label><x-styled-select :modelName="'hotels.'.$hi.'.status'" :options="[['value'=>'confirmed','label'=>'Confirmed'],['value'=>'on_holding','label'=>'On Holding'],['value'=>'cancelled','label'=>'Cancelled']]" placeholder="Status" /></div>
              <div class="col-md-2"><label class="be-label">Check In</label><x-date-picker :modelName="'hotels.'.$hi.'.check_in'" :compact="true" /></div>
              <div class="col-md-2"><label class="be-label">Check Out</label><x-date-picker :modelName="'hotels.'.$hi.'.check_out'" :compact="true" /></div>
            </div>
            <div class="row g-2 mb-2">
              <div class="col-md-3"><label class="be-label">Actual Cost (£)</label><input type="number" wire:model="hotels.{{ $hi }}.actual_cost" class="be-input" step="0.01" min="0" placeholder="0.00"></div>
              <div class="col-md-3"><label class="be-label">Selling Price (£)</label><input type="number" wire:model="hotels.{{ $hi }}.selling_price" class="be-input" step="0.01" min="0" placeholder="0.00"></div>
              <div class="col-md-2"><label class="be-label">No. of Rooms</label><input type="number" wire:model.live="hotels.{{ $hi }}.number_of_rooms" class="be-input" min="1"></div>
            </div>
            @foreach($hotel['rooms'] ?? [] as $ri => $room)
              <div class="mt-2 p-2" style="background:rgba(124,58,237,.03);border-radius:8px;border:1px solid rgba(124,58,237,.06);">
                <span style="font-size:.68rem;font-weight:600;color:#5A6080;">Room {{ $ri + 1 }}</span>
                <div class="row g-2 mt-1">
                  <div class="col-md-4"><label class="be-label" style="font-size:.63rem;">Room Type</label><input type="text" wire:model="hotels.{{ $hi }}.rooms.{{ $ri }}.room_type" class="be-input" style="font-size:.73rem;padding:.35rem .5rem;" placeholder="Double"></div>
                  <div class="col-md-3"><label class="be-label" style="font-size:.63rem;">Occupants</label><input type="number" wire:model="hotels.{{ $hi }}.rooms.{{ $ri }}.occupants" class="be-input" style="font-size:.73rem;padding:.35rem .5rem;" min="1"></div>
                  <div class="col-md-5"><label class="be-label" style="font-size:.63rem;">Meal Basis</label><x-styled-select-sm :modelName="'hotels.'.$hi.'.rooms.'.$ri.'.meal_basis'" :options="[['value'=>'room_only','label'=>'Room Only'],['value'=>'breakfast','label'=>'Breakfast'],['value'=>'half_board','label'=>'Half Board'],['value'=>'full_board','label'=>'Full Board'],['value'=>'all_inclusive','label'=>'All Inclusive']]" placeholder="Select" /></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
      <button type="button" wire:click="addHotel"
        style="background:rgba(124,58,237,.07);border:1.5px dashed rgba(124,58,237,.2);color:#7C3AED;border-radius:10px;padding:6px 16px;font-size:.76rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;margin-top:4px;">
        <i class="ph ph-plus"></i> Add Hotel
      </button>
    </div>
  @endif
</div>

{{-- ═══ TRANSFERS ═══ --}}
@if($booking->booking_type === 'transfers')
<div class="be-section">
  <div class="be-hdr" style="{{ !$canEditFlightHotel ? 'background:rgba(51,46,158,.03);' : '' }}">
    <div class="be-icon" style="background:rgba(51,46,158,.08);"><i class="ph ph-van" style="color:#332E9E;font-size:1rem;"></i></div>
    <h2>Transfers</h2>
    @if(!$canEditFlightHotel)
      <span class="ms-auto d-flex align-items-center gap-1" style="font-size:.7rem;color:#94A3B8;font-weight:600;">
        <i class="ph ph-lock" style="font-size:.8rem;"></i> Manager access only
      </span>
    @endif
  </div>
  @if(!$canEditFlightHotel)
    <div class="be-body" style="background:rgba(51,46,158,.02);">
      @if($booking->transfers?->isNotEmpty())
        @php $pickups = $booking->transfers->where('type','pickup'); $dropoffs = $booking->transfers->where('type','dropoff'); @endphp
        @if($pickups->isNotEmpty())
          <div class="mb-2"><span style="font-size:.72rem;font-weight:600;color:#332E9E;">Pickups ({{ $pickups->count() }})</span></div>
          @foreach($pickups as $t)
            <div class="mb-2 px-3 py-2" style="background:rgba(51,46,158,.03);border-radius:8px;font-size:.75rem;">
              <span class="fw-semibold">{{ $t->location }}</span>
              @if($t->date_time)<span class="text-muted ms-2">{{ $t->date_time->format('d M Y, H:i') }}</span>@endif
              @if($t->flight_number)<span class="text-muted ms-2">· {{ $t->flight_number }}</span>@endif
              @if($t->route)<div class="text-muted mt-1" style="font-size:.7rem;">Route: {{ $t->route }}</div>@endif
            </div>
          @endforeach
        @endif
        @if($dropoffs->isNotEmpty())
          <div class="mb-2 mt-3"><span style="font-size:.72rem;font-weight:600;color:#D83F87;">Dropoffs ({{ $dropoffs->count() }})</span></div>
          @foreach($dropoffs as $t)
            <div class="mb-2 px-3 py-2" style="background:rgba(216,63,135,.03);border-radius:8px;font-size:.75rem;">
              <span class="fw-semibold">{{ $t->location }}</span>
              @if($t->date_time)<span class="text-muted ms-2">{{ $t->date_time->format('d M Y, H:i') }}</span>@endif
              @if($t->route)<div class="text-muted mt-1" style="font-size:.7rem;">Route: {{ $t->route }}</div>@endif
            </div>
          @endforeach
        @endif
      @else
        <span style="font-size:.76rem;color:#94A3B8;">No transfers added.</span>
      @endif
    </div>
  @else
    <div class="be-body">
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-semibold" style="font-size:.76rem;color:#332E9E;">Pickups ({{ count($transferPickups) }})</span>
          <button type="button" wire:click="addPickup" style="background:rgba(51,46,158,.07);border:1px solid rgba(51,46,158,.15);color:#332E9E;border-radius:7px;padding:3px 12px;font-size:.7rem;cursor:pointer;">+ Add Pickup</button>
        </div>
        @foreach($transferPickups as $pi => $pickup)
          <div class="mb-2 p-2" style="border:1px solid rgba(51,46,158,.08);border-radius:8px;">
            <div class="d-flex justify-content-between mb-1"><span style="font-size:.68rem;font-weight:600;color:#5A6080;">Pickup {{ $pi + 1 }}</span><button type="button" wire:click="removePickup({{ $pi }})" style="background:none;border:none;color:#DC2626;font-size:.65rem;cursor:pointer;">Remove</button></div>
            <div class="row g-1">
              <div class="col-md-5"><input type="text" wire:model="transferPickups.{{ $pi }}.location" class="be-input" style="font-size:.72rem;padding:.3rem .45rem;" placeholder="Location"></div>
              <div class="col-md-3"><x-date-picker :modelName="'transferPickups.'.$pi.'.date_time'" :compact="true" :isDateTime="true" /></div>
              <div class="col-md-2"><input type="text" wire:model="transferPickups.{{ $pi }}.flight_number" class="be-input" style="font-size:.72rem;padding:.3rem .45rem;" placeholder="Flight #"></div>
              <div class="col-md-2"><input type="text" wire:model="transferPickups.{{ $pi }}.route" class="be-input" style="font-size:.72rem;padding:.3rem .45rem;" placeholder="Route"></div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-semibold" style="font-size:.76rem;color:#D83F87;">Dropoffs ({{ count($transferDropoffs) }})</span>
          <button type="button" wire:click="addDropoff" style="background:rgba(216,63,135,.07);border:1px solid rgba(216,63,135,.15);color:#D83F87;border-radius:7px;padding:3px 12px;font-size:.7rem;cursor:pointer;">+ Add Dropoff</button>
        </div>
        @foreach($transferDropoffs as $di => $dropoff)
          <div class="mb-2 p-2" style="border:1px solid rgba(216,63,135,.08);border-radius:8px;">
            <div class="d-flex justify-content-between mb-1"><span style="font-size:.68rem;font-weight:600;color:#5A6080;">Dropoff {{ $di + 1 }}</span><button type="button" wire:click="removeDropoff({{ $di }})" style="background:none;border:none;color:#DC2626;font-size:.65rem;cursor:pointer;">Remove</button></div>
            <div class="row g-1 mb-1">
              <div class="col-md-5"><input type="text" wire:model="transferDropoffs.{{ $di }}.location" class="be-input" style="font-size:.72rem;padding:.3rem .45rem;" placeholder="Location"></div>
              <div class="col-md-3"><x-date-picker :modelName="'transferDropoffs.'.$di.'.date_time'" :compact="true" :isDateTime="true" /></div>
              <div class="col-md-2"><input type="text" wire:model="transferDropoffs.{{ $di }}.flight_number" class="be-input" style="font-size:.72rem;padding:.3rem .45rem;" placeholder="Flight #"></div>
            </div>
            <textarea wire:model="transferDropoffs.{{ $di }}.route" class="be-input" rows="2" style="font-size:.72rem;padding:.3rem .45rem;" placeholder="Route description..."></textarea>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</div>
@endif

{{-- ═══ DOCUMENTS ═══ --}}
<div class="be-section">
  <div class="be-hdr">
    <div class="be-icon" style="background:rgba(124,58,237,.08);"><i class="ph ph-file-text" style="color:#7C3AED;font-size:1rem;"></i></div>
    <h2>Documents</h2>
  </div>
  <div class="be-body">
    @foreach($existingDocuments as $ed)
      <div class="d-flex align-items-center justify-content-between mb-2 px-3 py-2" style="background:#F8FAFF;border-radius:9px;">
        <span style="font-size:.78rem;"><i class="ph ph-file me-1" style="color:#7C3AED;"></i>{{ $ed['file_name'] ?? 'File' }}</span>
        <button type="button" wire:click="deleteExistingDocument({{ $ed['id'] ?? 0 }})"
          style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:7px;padding:3px 10px;font-size:.7rem;cursor:pointer;">Remove</button>
      </div>
    @endforeach
    @foreach($newDocuments as $i => $doc)
      <div class="d-flex gap-2 align-items-center mb-2">
        <input type="file" wire:model="newDocuments.{{ $i }}" class="be-input" style="font-size:.78rem;padding:.35rem .65rem;">
        <x-styled-select-sm :modelName="'newDocumentTypes.'.$i" :options="[['value'=>'','label'=>'Type'],['value'=>'passport','label'=>'Passport'],['value'=>'visa','label'=>'Visa'],['value'=>'itinerary','label'=>'Itinerary'],['value'=>'invoice','label'=>'Invoice'],['value'=>'other','label'=>'Other']]" placeholder="Type" />
        <button type="button" wire:click="removeDocument({{ $i }})" style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:7px;padding:5px 10px;font-size:.8rem;cursor:pointer;flex-shrink:0;">✕</button>
      </div>
    @endforeach
    <button type="button" wire:click="addDocument"
      style="background:rgba(51,46,158,.07);border:1.5px dashed rgba(51,46,158,.2);color:#332E9E;border-radius:10px;padding:6px 16px;font-size:.76rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;margin-top:4px;">
      <i class="ph ph-plus"></i> Add Document
    </button>
  </div>
</div>

</div>{{-- end left col --}}

{{-- RIGHT COLUMN --}}
<div class="col-lg-4">

  {{-- Payment --}}
  <div class="be-section">
    <div class="be-hdr">
      <div class="be-icon" style="background:rgba(14,165,233,.08);"><i class="ph ph-credit-card" style="color:#0EA5E9;font-size:1rem;"></i></div>
      <h2>Payment</h2>
    </div>
    <div class="be-body">
      <div class="be-field mb-3"><label class="be-label">Payment Type <span class="text-danger">*</span></label>
        <x-styled-select modelName="booking_plan" :options="[['value'=>'','label'=>'Select'],['value'=>'full','label'=>'Full Payment'],['value'=>'awaiting','label'=>'Awaiting'],['value'=>'payment_plan','label'=>'Payment Plan'],['value'=>'dnpl','label'=>'DNPL']]" placeholder="Select" :live="true" />
      </div>
      @if(in_array($booking_plan,['full','awaiting','payment_plan']))
        <div class="mb-3"><label class="be-label">Amount Paid (£)</label><input type="number" wire:model="amount_paid" class="be-input" step="0.01" min="0" placeholder="0.00"></div>
      @endif
      @if($booking_plan==='dnpl')
        <div class="mb-3"><label class="be-label">Deposit (£) - non-refundable</label><input type="number" wire:model="deposit_amount" class="be-input" step="0.01" min="0" placeholder="0.00"></div>
      @endif
      @if(in_array($booking_plan,['awaiting','payment_plan']))
        <div class="mb-3"><label class="be-label">Balance Due By</label><x-date-picker modelName="due_date" /></div>
      @endif
      @if($booking_plan==='payment_plan')
        <div class="mb-3"><label class="be-label">First Instalment (£)</label><input type="number" wire:model="installment_first_amount" class="be-input" step="0.01" min="0" placeholder="0.00"></div>
      @endif
    </div>
  </div>

  {{-- Cost & Margins --}}
  <div class="be-section">
    <div class="be-hdr">
      <div class="be-icon" style="background:rgba(51,46,158,.08);"><i class="ph ph-currency-pound" style="color:#332E9E;font-size:1rem;"></i></div>
      <h2>Cost &amp; Margins</h2>
    </div>
    <div class="be-body">
      @php
        $totalCost = collect($passengers)->sum(fn($p) => (float)($p['cost_per_pax'] ?? 0));
        $totalSold = collect($passengers)->sum(fn($p) => (float)($p['sold_per_pax'] ?? 0));
        $margin    = $totalSold - $totalCost;
      @endphp
      <div class="d-flex justify-content-between mb-2" style="font-size:.8rem;">
        <span style="color:#64748B;">Total Cost</span>
        <span class="fw-semibold">£{{ number_format($totalCost, 2) }}</span>
      </div>
      <div class="d-flex justify-content-between mb-2" style="font-size:.8rem;">
        <span style="color:#64748B;">Total Sold</span>
        <span class="fw-semibold">£{{ number_format($totalSold, 2) }}</span>
      </div>
      <div class="bv-divider" style="height:1px;background:rgba(51,46,158,.06);margin:12px 0;"></div>
      <div class="d-flex justify-content-between align-items-center px-3 py-2 rounded-3" style="background:{{ $margin >= 0 ? 'rgba(22,163,74,.08)' : 'rgba(220,38,38,.08)' }};">
        <span style="font-size:.78rem;font-weight:700;color:{{ $margin >= 0 ? '#15803D' : '#DC2626' }};">Total Margin</span>
        <span style="font-size:1.05rem;font-weight:800;color:{{ $margin >= 0 ? '#15803D' : '#DC2626' }};">£{{ number_format($margin, 2) }}</span>
      </div>
    </div>
  </div>

  {{-- Activity Log - full payment-step style --}}
  @php
    $logCfg = [
      'added'     => ['dot'=>'#16A34A','pbg'=>'rgba(22,163,74,.10)',  'pc'=>'#16A34A',  'lbl'=>'Added'],
      'removed'   => ['dot'=>'#DC2626','pbg'=>'rgba(220,38,38,.10)',  'pc'=>'#DC2626',  'lbl'=>'Removed'],
      'navigated' => ['dot'=>'#94A3B8','pbg'=>'rgba(148,163,184,.10)','pc'=>'#64748B',  'lbl'=>'Nav'],
      'updated'   => ['dot'=>'#F59E0B','pbg'=>'rgba(245,158,11,.10)', 'pc'=>'#B45309',  'lbl'=>'Changed'],
      'note'      => ['dot'=>'#332E9E','pbg'=>'rgba(51,46,158,.10)',  'pc'=>'#332E9E',  'lbl'=>'Note'],
      'info'      => ['dot'=>'#CBD5E1','pbg'=>'rgba(203,213,225,.15)','pc'=>'#94A3B8',  'lbl'=>'Info'],
    ];
  @endphp
  <div class="be-section">
    <div class="be-hdr" style="background:linear-gradient(135deg,#332E9E,#4A45B5);border-radius:16px 16px 0 0;">
      <div class="be-icon" style="background:rgba(255,255,255,.15);"><i class="ph ph-clock-countdown" style="color:#fff;font-size:1rem;"></i></div>
      <h2 style="color:#fff;">Activity Log</h2>
      <span style="font-size:.6rem;color:rgba(255,255,255,.45);background:rgba(255,255,255,.10);padding:1px 8px;border-radius:20px;margin-left:4px;">auto-recorded</span>
      <span class="ms-auto" style="font-size:.65rem;color:rgba(255,255,255,.45);">{{ count($activity_log_entries) }} event{{ count($activity_log_entries)!==1?'s':'' }}</span>
    </div>

    {{-- Timeline --}}
    <div style="max-height:280px;overflow-y:auto;padding:16px 20px 4px;">
      @if(empty($activity_log_entries))
        <div class="text-center py-3" style="color:#C4C9D4;font-size:.73rem;">No activity yet.</div>
      @else
        @foreach($activity_log_entries as $i => $entry)
          @php $cfg = $logCfg[$entry['type'] ?? 'info'] ?? $logCfg['info']; $isLast = $loop->last; $canReason = ($entry['type']??'info') !== 'note'; @endphp
          <div class="d-flex gap-3" style="{{ !$isLast ? 'padding-bottom:2px;' : '' }}">
            <div class="d-flex flex-column align-items-center flex-shrink-0" style="width:16px;padding-top:3px;">
              <div style="width:9px;height:9px;border-radius:50%;background:{{ $cfg['dot'] }};box-shadow:0 0 0 3px {{ $cfg['pbg'] }};flex-shrink:0;"></div>
              @if(!$isLast)<div style="width:1px;flex:1;background:rgba(51,46,158,.07);min-height:26px;margin-top:5px;"></div>@endif
            </div>
            <div class="flex-grow-1" style="{{ !$isLast ? 'padding-bottom:16px;' : 'padding-bottom:8px;' }}">
              <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <span class="fw-semibold" style="font-size:.76rem;color:#1E293B;">{{ $entry['agent'] }}</span>
                  <span style="font-size:.71rem;color:#475569;">{{ $entry['action'] }}</span>
                  <span style="font-size:.57rem;font-weight:700;color:{{ $cfg['pc'] }};background:{{ $cfg['pbg'] }};padding:1px 6px;border-radius:20px;text-transform:uppercase;letter-spacing:.04em;">{{ $cfg['lbl'] }}</span>
                </div>
                <span style="font-size:.62rem;color:#94A3B8;white-space:nowrap;padding-top:2px;">{{ $entry['timestamp'] }}</span>
              </div>
              @if(!empty($entry['detail']))
                <div class="mt-1 px-2 py-1" style="background:{{ ($entry['type']??'')!=='note' ? $cfg['pbg'] : 'rgba(51,46,158,.04)' }};border-radius:6px;border-left:3px solid {{ $cfg['dot'] }};">
                  <span style="font-size:.69rem;color:#475569;font-style:italic;">{{ $entry['detail'] }}</span>
                </div>
              @elseif($canReason)
                @if($reasonEditIndex === $i)
                  <div class="mt-1 d-flex gap-1 align-items-center">
                    <input type="text" wire:model="reasonEditText" placeholder="Why did you do this?"
                      wire:keydown.enter="saveReasonEdit" wire:keydown.escape="cancelReasonEdit"
                      style="font-size:.72rem;border:1.5px solid rgba(51,46,158,.2);border-radius:7px;padding:4px 10px;flex:1;outline:none;color:#374151;background:#F8FAFF;">
                    <button type="button" wire:click="saveReasonEdit" style="font-size:.68rem;background:#332E9E;color:#fff;border:none;border-radius:7px;padding:4px 12px;cursor:pointer;font-weight:600;">Save</button>
                    <button type="button" wire:click="cancelReasonEdit" style="font-size:.7rem;background:transparent;color:#94A3B8;border:1px solid #E2E8F0;border-radius:7px;padding:4px 8px;cursor:pointer;">✕</button>
                  </div>
                @else
                  <div class="mt-1">
                    <button type="button" wire:click="openReasonEdit({{ $i }})"
                      style="font-size:.67rem;color:#94A3B8;background:none;border:none;padding:0;cursor:pointer;text-decoration:underline;text-underline-offset:2px;">
                      + add reason
                    </button>
                  </div>
                @endif
              @endif
            </div>
          </div>
        @endforeach
      @endif
    </div>

    {{-- Add note input --}}
    <div class="d-flex gap-2 align-items-center px-4 py-3" style="border-top:1px solid rgba(51,46,158,.07);background:#F8FAFF;">
      <input type="text" wire:model="mandatory_comment" placeholder="Add a note about this edit..."
        class="be-input flex-grow-1 @error('mandatory_comment') border-danger @enderror"
        style="border-radius:20px;font-size:.77rem;" wire:keydown.enter="addActivityEntry">
      <button type="button" wire:click="addActivityEntry"
        class="flex-shrink-0"
        style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:20px;padding:6px 16px;font-size:.74rem;font-weight:700;cursor:pointer;white-space:nowrap;">
        Add Note
      </button>
    </div>
    @error('mandatory_comment')<div class="px-4 pb-2" style="font-size:.72rem;color:#DC2626;">{{ $message }}</div>@enderror
  </div>

  {{-- Save --}}
  <button type="submit"
    style="width:100%;background:linear-gradient(135deg,#FF6B35,#FF8C5A);color:#fff;border:none;border-radius:12px;padding:12px;font-size:.88rem;font-weight:700;cursor:pointer;box-shadow:0 4px 16px rgba(255,107,53,.3);display:flex;align-items:center;justify-content:center;gap:6px;">
    <i class="ph ph-check-circle" style="font-size:1.1rem;"></i> Save Changes
  </button>

</div>{{-- end right col --}}
</div>{{-- end row --}}
</form>
</div>
