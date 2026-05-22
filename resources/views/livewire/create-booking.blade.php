<div class="col-12">
  <div class="card mb-4">
    <div class="card-body pb-2">
      @if (session()->has('success'))
        <div class="alert alert-success py-2 mb-0">{{ session('success') }}</div>
      @endif
      @if (session()->has('error'))
        <div class="alert alert-danger py-2 mb-0">{{ session('error') }}</div>
      @endif

      @if ($booking_ref && !$errors->any())
        <div class="text-center py-5">
          <h4 class="mb-2">Booking #{{ $booking_ref }}</h4>
          <p class="text-muted mb-3">Saved successfully.</p>
          <a href="{{ route('bookings.index') }}" class="btn btn-primary">View All Bookings</a>
        </div>
      @else
        {{-- Step Indicator --}}
        <div class="d-flex justify-content-between align-items-center mb-4 px-2 wizard-steps">
          @php $labels = ['Booking Info', 'Caller', 'Travellers', 'Flight', 'Hotel', 'Payment & Wrap-up']; @endphp
          @foreach ($labels as $i => $label)
            <div class="d-flex align-items-center {{ $loop->last ? '' : 'flex-grow-1' }}">
              <div class="step-circle d-flex align-items-center justify-content-center rounded-circle fw-bold
                {{ $step > $i + 1 ? 'bg-success text-white' : ($step === $i + 1 ? 'bg-primary text-white' : 'bg-secondary text-white') }}"
                style="width:34px;height:34px;font-size:13px;flex-shrink:0">
                @if($step > $i + 1) &#10003; @else {{ $i + 1 }} @endif
              </div>
              <span class="ms-2 small fw-semibold text-nowrap {{ $step === $i + 1 ? 'text-primary' : ($step > $i + 1 ? 'text-success' : 'text-muted') }}">{{ $label }}</span>
              @if (!$loop->last)
                <div class="flex-grow-1 mx-2" style="height:2px;background:{{ $step > $i + 1 ? '#28a745' : '#dee2e6' }}"></div>
              @endif
            </div>
          @endforeach
        </div>

        <form wire:submit.prevent="save" class="wizard-form">
          @csrf

          {{-- ===== STEP 1: Booking Info & Lead Source ===== --}}
          @if ($step === 1)
            <div class="step-card">
              <h6 class="fw-bold mb-3">Step 1 &mdash; Booking Info &amp; Lead Source</h6>
              <div class="row g-2 mb-3">
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Lead Source <span class="text-danger">*</span></label>
                  <select wire:model="lead_source" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="to_returning">TO Returning</option>
                    <option value="to_referral">TO Referral</option>
                    <option value="referral_client">Referral Client</option>
                    <option value="returning_client">Returning Client</option>
                    <option value="fb">Facebook</option>
                    <option value="wa">WhatsApp</option>
                    <option value="email">Email</option>
                    <option value="diaspora_group">Diaspora Group</option>
                    <option value="instagram">Instagram</option>
                    <option value="tiktok">TikTok</option>
                    <option value="website">Website</option>
                    <option value="google">Google</option>
                  </select>
                  @error('lead_source') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Lead Nature <span class="text-danger">*</span></label>
                  <select wire:model="lead_nature" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="new_booking">New Booking</option>
                    <option value="date_change">Date Change</option>
                    <option value="refund_booking">Refund Booking</option>
                    <option value="previous_booking">Previous Booking</option>
                  </select>
                  @error('lead_nature') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Booking Type <span class="text-danger">*</span></label>
                  <select wire:model="booking_type" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="flight">Flight</option>
                    <option value="hotel">Hotel</option>
                    <option value="umrah">Umrah</option>
                    <option value="holiday">Holiday</option>
                  </select>
                  @error('booking_type') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Referral Name</label>
                  <input type="text" wire:model="referral_name" class="form-control form-control-sm" placeholder="Referral name">
                </div>
              </div>

              <div class="row g-2 mb-2">
                <div class="col-md-3">
                  <div class="form-check form-switch mt-3">
                    <input type="checkbox" wire:model.live="is_returning_or_referral" class="form-check-input" id="is_ret">
                    <label class="form-check-label small fw-semibold" for="is_ret">Returning / Referral?</label>
                  </div>
                </div>
                @if ($is_returning_or_referral)
                  <div class="col-md-3">
                    <label class="form-label small mb-1 fw-semibold">Old Booking Reference</label>
                    <input type="text" wire:model="old_booking_reference" class="form-control form-control-sm">
                  </div>
                @endif
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Last Payment Date</label>
                  <input type="date" wire:model="last_payment_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Last Issue Date</label>
                  <input type="date" wire:model="last_issue_date" class="form-control form-control-sm">
                </div>
              </div>
            </div>
          @endif

          {{-- ===== STEP 2: Caller Information ===== --}}
          @if ($step === 2)
            <div class="step-card">
              <h6 class="fw-bold mb-3">Step 2 &mdash; Caller Information</h6>
              <div class="row g-2">
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Title</label>
                  <select wire:model="booker_title" class="form-select form-select-sm">
                    <option value="">Title</option>
                    <option value="1">1 - Mr.</option>
                    <option value="2">2 - Ms.</option>
                    <option value="3">3 - Mrs.</option>
                    <option value="4">4 - Mstr</option>
                    <option value="5">5 - Miss</option>
                    <option value="6">6 - Dr.</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">First Name <span class="text-danger">*</span></label>
                  <input type="text" wire:model="booker_first_name" class="form-control form-control-sm @error('booker_first_name') is-invalid @enderror">
                  @error('booker_first_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Last Name <span class="text-danger">*</span></label>
                  <input type="text" wire:model="booker_last_name" class="form-control form-control-sm @error('booker_last_name') is-invalid @enderror">
                  @error('booker_last_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Mobile <span class="text-danger">*</span></label>
                  <input type="text" wire:model="booker_mobile" class="form-control form-control-sm @error('booker_mobile') is-invalid @enderror">
                  @error('booker_mobile') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Landline</label>
                  <input type="text" wire:model="booker_landline" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Email</label>
                  <input type="email" wire:model="booker_email" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Country</label>
                  <select wire:model="booker_country" class="form-select form-select-sm">
                    @foreach ($countries as $code => $name)
                      <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Postcode</label>
                  <input type="text" wire:model="booker_postcode" class="form-control form-control-sm">
                </div>
                <div class="col-md-4">
                  <label class="form-label small mb-1 fw-semibold">Address</label>
                  <textarea wire:model="booker_address" class="form-control form-control-sm" rows="1"></textarea>
                </div>
              </div>
            </div>
          @endif

          {{-- ===== STEP 3: Traveller Information ===== --}}
          @if ($step === 3)
            <div class="step-card">
              <h6 class="fw-bold mb-3">Step 3 &mdash; Traveller Information</h6>

              <div class="d-flex gap-3 mb-3 flex-wrap align-items-center">
                @foreach (['adult' => 'Adult', 'gbe' => 'GBE', 'child' => 'Child', 'infant' => 'Infant'] as $type => $label)
                  <div class="d-flex align-items-center gap-1">
                    <span class="small fw-semibold">{{ $label }}</span>
                    <button type="button" wire:click="dec('{{ $type }}')" class="btn btn-sm btn-outline-secondary px-1 py-0" style="line-height:1;">&#8722;</button>
                    <span class="fw-bold small px-1">{{ ${$type.'Count'} }}</span>
                    <button type="button" wire:click="inc('{{ $type }}')" class="btn btn-sm btn-outline-primary px-1 py-0" style="line-height:1;">+</button>
                  </div>
                @endforeach
                <span class="badge bg-primary ms-2">{{ $this->totalPassengers }} traveller(s)</span>
              </div>

              @if (empty($passengers))
                <div class="text-muted small py-3">No travellers added yet. Use the counters above.</div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm table-bordered align-middle mb-0" style="font-size:11px;">
                    <thead class="table-light">
                      <tr>
                        <th>#</th><th>Type</th><th>PTC</th><th>Title</th><th>First Name</th><th>Last Name</th><th>DOB</th>
                        <th>Passport #</th><th>Iss. Country</th><th>Nation.</th><th>NIC</th><th>Freq. Flyer</th>
                        <th>Status Label</th><th>E-Ticket</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($passengers as $i => $p)
                        @php
                          $ptc = !empty($p['date_of_birth']) ? $this->computePtc($p['date_of_birth'], $p['type']) : '';
                          $ptcColor = $ptc === 'CNN' ? '#dc3545' : ($ptc === 'INF' ? '#ffc107' : '#198754');
                        @endphp
                        <tr>
                          <td>{{ $i + 1 }}</td>
                          <td><span class="badge bg-secondary">{{ $this->passengerTypeLabel($p['type']) }}</span></td>
                          <td>
                            @if ($ptc)
                              <span class="fw-bold" style="color:{{ $ptcColor }}">{{ $ptc }}</span>
                            @else
                              <span class="text-muted">—</span>
                            @endif
                          </td>
                          <td>
                            <select wire:model="passengers.{{ $i }}.title" class="form-select form-select-sm" style="width:60px;font-size:11px;">
                              <option value="">--</option>
                              <option value="Mr.">Mr.</option>
                              <option value="Ms.">Ms.</option>
                              <option value="Mrs.">Mrs.</option>
                              <option value="Mstr">Mstr</option>
                              <option value="Miss">Miss</option>
                              <option value="Dr.">Dr.</option>
                            </select>
                          </td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.first_name" class="form-control form-control-sm" style="width:80px;font-size:11px;"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.last_name" class="form-control form-control-sm" style="width:90px;font-size:11px;"></td>
                          <td><input type="date" wire:model.live="passengers.{{ $i }}.date_of_birth" class="form-control form-control-sm" style="width:110px;font-size:11px;"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.passport_number" class="form-control form-control-sm" style="width:85px;font-size:11px;"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.passport_issuing_country" class="form-control form-control-sm" style="width:75px;font-size:11px;" placeholder="UK"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.nationality" class="form-control form-control-sm" style="width:70px;font-size:11px;"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.national_id_number" class="form-control form-control-sm" style="width:80px;font-size:11px;"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.frequent_flyer_number" class="form-control form-control-sm" style="width:80px;font-size:11px;"></td>
                          <td><input type="text" wire:model="passengers.{{ $i }}.passenger_status_label" class="form-control form-control-sm" style="width:75px;font-size:11px;" placeholder="OK"></td>
                          <td>
                            <input type="text" wire:model="passengers.{{ $i }}.e_ticket_number" class="form-control form-control-sm" style="width:100px;font-size:11px;" readonly placeholder="Update separately">
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="mt-2">
                  <button type="button" class="btn btn-sm btn-outline-warning">
                    <i class="ph ph-ticket me-1"></i> Update E-Ticket Numbers
                  </button>
                </div>
              @endif

              @error('passengers') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
          @endif

          {{-- ===== STEP 4: Flight Information ===== --}}
          @if ($step === 4)
            <div class="step-card">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Step 4 &mdash; Flight Information</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" title="Upload flight document">
                  <i class="ph ph-upload-simple me-1"></i> Upload
                </button>
              </div>

              {{-- PNR paste box --}}
              <div class="card bg-light p-3 mb-3 border">
                <div class="row g-2 align-items-end">
                  <div class="col-md-7">
                    <label class="form-label small mb-1 fw-semibold">Paste PNR Details</label>
                    <textarea wire:model="flight_pnr" class="form-control form-control-sm" rows="2" placeholder="Paste full PNR content here... (e.g. RP/LONBA1234...)"></textarea>
                  </div>
                  <div class="col-md-2">
                    <button type="button" class="btn btn-warning btn-sm w-100 fw-bold">
                      <i class="ph ph-magnifying-glass me-1"></i> Fetch PNR Details
                    </button>
                  </div>
                  <div class="col-md-3 text-end small text-muted">
                    <i class="ph ph-info me-1"></i> Paste PNR text and click Fetch to auto-fill
                  </div>
                </div>
              </div>

              {{-- Flight metadata row 1 --}}
              <div class="row g-2 mb-2">
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Folder Number</label>
                  <div class="input-group input-group-sm">
                    <input type="text" wire:model="flight_folder_number" class="form-control form-control-sm">
                    <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Locator</label>
                  <input type="text" wire:model="flight_locator" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Airline Locator</label>
                  <input type="text" wire:model="flight_airline_locator" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Type / Issuer</label>
                  <input type="text" wire:model="flight_type_issuer" class="form-control form-control-sm">
                </div>
              </div>

              {{-- Flight metadata row 2 --}}
              <div class="row g-2 mb-2">
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Airline (2-letter)</label>
                  <input type="text" wire:model="flight_airline" class="form-control form-control-sm" maxlength="2" x-on:input="$el.value = $el.value.toUpperCase().slice(0,2)" placeholder="EK">
                </div>
                <div class="col-md-3">
                  <label class="form-label small mb-1 fw-semibold">Vendor</label>
                  <input type="text" wire:model="flight_vendor" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">GDS</label>
                  <input type="text" wire:model="flight_gds" class="form-control form-control-sm" placeholder="AMADEUS">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Reservation Status</label>
                  <input type="text" wire:model="flight_reservation_status" class="form-control form-control-sm" placeholder="e.g. Confirmed">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Ticket Limit</label>
                  <input type="datetime-local" wire:model="flight_ticket_issue_limit" class="form-control form-control-sm">
                </div>
              </div>

              {{-- Flight airports/dates --}}
              <div class="row g-2 mb-2">
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">City Code</label>
                  <input type="text" wire:model="flight_city_code" class="form-control form-control-sm" maxlength="5">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Dep. Airport</label>
                  <input type="text" wire:model="flight_departure_airport" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Arr. Airport</label>
                  <input type="text" wire:model="flight_arrival_airport" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Dep. Date</label>
                  <input type="date" wire:model="flight_departure_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Ret. Date</label>
                  <input type="date" wire:model="flight_return_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-3">
                  <div class="form-check form-check-inline mb-0">
                    <input type="checkbox" wire:model="flight_atol" class="form-check-input" id="atol">
                    <label class="form-check-label small" for="atol">ATOL</label>
                  </div>
                  <div class="form-check form-check-inline mb-0">
                    <input type="checkbox" wire:model="flight_safi" class="form-check-input" id="safi">
                    <label class="form-check-label small" for="safi">SAFI</label>
                  </div>
                </div>
              </div>

              {{-- Action buttons --}}
              <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary">Split Flight Details</button>
                <button type="button" class="btn btn-sm btn-outline-secondary">Edit Flight Details</button>
              </div>
            </div>
          @endif

          {{-- ===== STEP 5: Hotel Information ===== --}}
          @if ($step === 5)
            <div class="step-card" @unless(in_array($booking_type, ['hotel', 'holiday'])) x-data="{ showHotel: false }" @endunless>
              @if (in_array($booking_type, ['hotel', 'holiday']))
                <h6 class="fw-bold mb-3">Step 5 &mdash; Hotel Information</h6>
                <div class="row g-2">
                  <div class="col-md-4">
                    <label class="form-label small mb-1 fw-semibold">Hotel Name</label>
                    <input type="text" wire:model="hotel_name" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">City</label>
                    <input type="text" wire:model="hotel_city" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Room Type</label>
                    <input type="text" wire:model="hotel_room_type" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Status</label>
                    <select wire:model="hotel_status" class="form-select form-select-sm">
                      <option value="confirmed">Confirmed</option>
                      <option value="on_holding">On Holding</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Check In</label>
                    <input type="date" wire:model="hotel_check_in" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Check Out</label>
                    <input type="date" wire:model="hotel_check_out" class="form-control form-control-sm">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Occupants</label>
                    <input type="number" wire:model="hotel_occupants" class="form-control form-control-sm" min="1">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Actual Cost (£)</label>
                    <input type="number" wire:model="hotel_actual_cost" class="form-control form-control-sm" step="0.01" min="0">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Selling Price (£)</label>
                    <input type="number" wire:model="hotel_selling_price" class="form-control form-control-sm" step="0.01" min="0">
                  </div>
                </div>
              @else
                <div x-show="!showHotel" class="text-center py-3">
                  <p class="text-muted small mb-2">Hotel booking not selected. Add if needed.</p>
                  <button type="button" class="btn btn-sm btn-outline-primary" x-on:click="showHotel = true">+ Add Hotel</button>
                </div>
                <div x-show="showHotel" x-cloak>
                  <h6 class="fw-bold mb-3">Step 5 &mdash; Hotel Information</h6>
                  <div class="row g-2">
                    <div class="col-md-4"><label class="form-label small mb-1 fw-semibold">Hotel Name</label><input type="text" wire:model="hotel_name" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">City</label><input type="text" wire:model="hotel_city" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Room Type</label><input type="text" wire:model="hotel_room_type" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Status</label>
                      <select wire:model="hotel_status" class="form-select form-select-sm"><option value="confirmed">Confirmed</option><option value="on_holding">On Holding</option><option value="cancelled">Cancelled</option></select></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Check In</label><input type="date" wire:model="hotel_check_in" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Check Out</label><input type="date" wire:model="hotel_check_out" class="form-control form-control-sm"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Occupants</label><input type="number" wire:model="hotel_occupants" class="form-control form-control-sm" min="1"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Actual Cost (£)</label><input type="number" wire:model="hotel_actual_cost" class="form-control form-control-sm" step="0.01" min="0"></div>
                    <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Selling Price (£)</label><input type="number" wire:model="hotel_selling_price" class="form-control form-control-sm" step="0.01" min="0"></div>
                  </div>
                </div>
              @endif
            </div>
          @endif

          {{-- ===== STEP 6: Payment & Wrap-up ===== --}}
          @if ($step === 6)
            <div class="step-card">
              <h6 class="fw-bold mb-3">Step 6 &mdash; Payment &amp; Wrap-up</h6>

              <div class="row g-3">
                {{-- Left column: Payment fields --}}
                <div class="col-md-8">

                  {{-- Payment Type + Modes --}}
                  <div class="row g-2 mb-3">
                    <div class="col-md-3">
                      <label class="form-label small mb-1 fw-semibold">Payment Type <span class="text-danger">*</span></label>
                      <select wire:model="payment_type" class="form-select form-select-sm @error('payment_type') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="full">Full Payment</option>
                        <option value="awaiting">Awaiting</option>
                        <option value="payment_plan">Payment Plan</option>
                        <option value="dnpl">DNPL</option>
                      </select>
                      @error('payment_type') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-3">
                      <label class="form-label small mb-1 fw-semibold">Payment Mode 1 <span class="text-danger">*</span></label>
                      <select wire:model="payment_mode" class="form-select form-select-sm">
                        <option value="">Select</option>
                        @foreach ($paymentMethods as $val => $label)
                          <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label small mb-1 fw-semibold">Payment Mode 2</label>
                      <select wire:model="payment_mode_2" class="form-select form-select-sm">
                        <option value="">None</option>
                        @foreach ($paymentMethods as $val => $label)
                          <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                      </select>
                    </div>
                    @if ($payment_type === 'payment_plan')
                      <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Period</label>
                        <select wire:model="installment_period" class="form-select form-select-sm">
                          <option value="none">None</option>
                          <option value="30_days">30 Days</option>
                          <option value="2_months">2 Months</option>
                        </select>
                      </div>
                    @endif
                  </div>

                  {{-- Payment amounts --}}
                  <div class="row g-2 mb-3">
                    @if (in_array($payment_type, ['full', 'awaiting', 'payment_plan']))
                      <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Amount Paid (£)</label>
                        <input type="number" wire:model="amount_paid" class="form-control form-control-sm" step="0.01" min="0">
                      </div>
                    @endif
                    @if ($payment_type === 'payment_plan')
                      <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">1st Payment (£)</label>
                        <input type="number" wire:model="installment_first_amount" class="form-control form-control-sm" step="0.01" min="0">
                      </div>
                    @endif
                    @if (in_array($payment_type, ['awaiting', 'payment_plan']))
                      <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Due Date</label>
                        <input type="date" wire:model="due_date" class="form-control form-control-sm">
                      </div>
                    @endif
                    @if ($payment_type === 'dnpl')
                      <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Deposit (£)</label>
                        <input type="number" wire:model="deposit_amount" class="form-control form-control-sm" step="0.01" min="0">
                      </div>
                    @endif
                    <div class="col-md-3 d-flex align-items-end">
                      <div class="form-check form-switch">
                        <input type="checkbox" wire:model="debit_card_change" class="form-check-input" id="dc_change">
                        <label class="form-check-label small" for="dc_change">Debit Card Change</label>
                      </div>
                    </div>
                  </div>

                  {{-- Payment Charged History --}}
                  <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label small mb-0 fw-semibold">Payment Charged History</label>
                      <button type="button" wire:click="addPaymentHistory" class="btn btn-sm btn-outline-secondary">+ Add Entry</button>
                    </div>
                    @if (!empty($payment_history))
                      <table class="table table-sm table-bordered small mb-0" style="font-size:11px;">
                        <thead class="table-light">
                          <tr><th>Date</th><th>Method</th><th>Amount (£)</th><th>Receipt #</th><th></th></tr>
                        </thead>
                        <tbody>
                          @foreach ($payment_history as $i => $ph)
                            <tr>
                              <td><input type="date" wire:model="payment_history.{{ $i }}.date" class="form-control form-control-sm" style="width:115px;font-size:11px;"></td>
                              <td>
                                <select wire:model="payment_history.{{ $i }}.method" class="form-select form-select-sm" style="width:125px;font-size:11px;">
                                  <option value="">—</option>
                                  @foreach ($paymentMethods as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                  @endforeach
                                </select>
                              </td>
                              <td><input type="number" wire:model="payment_history.{{ $i }}.amount" step="0.01" class="form-control form-control-sm" style="width:75px;font-size:11px;"></td>
                              <td><input type="text" wire:model="payment_history.{{ $i }}.receipt" class="form-control form-control-sm" style="width:95px;font-size:11px;"></td>
                              <td><button type="button" wire:click="removePaymentHistory({{ $i }})" class="btn btn-sm btn-outline-danger px-1 py-0">&times;</button></td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    @else
                      <p class="text-muted small mb-0">No payment entries yet.</p>
                    @endif
                  </div>

                  {{-- Documents --}}
                  <div class="mb-3">
                    <label class="form-label small mb-1 fw-semibold">Documents</label>
                    @foreach ($documents as $i => $doc)
                      <div class="d-flex gap-2 align-items-center mb-1">
                        <input type="file" wire:model="documents.{{ $i }}" class="form-control form-control-sm" style="max-width:260px;font-size:12px;">
                        <select wire:model="document_types.{{ $i }}" class="form-select form-select-sm" style="width:120px;font-size:12px;">
                          <option value="">Type</option>
                          <option value="passport">Passport</option>
                          <option value="visa">Visa</option>
                          <option value="itinerary">Itinerary</option>
                          <option value="invoice">Invoice</option>
                          <option value="other">Other</option>
                        </select>
                        <button type="button" wire:click="removeDocument({{ $i }})" class="btn btn-sm btn-outline-danger px-1 py-0">&times;</button>
                      </div>
                    @endforeach
                    <button type="button" wire:click="addDocument" class="btn btn-sm btn-outline-secondary mt-1">+ Add Document</button>
                  </div>
                </div>

                {{-- Right column: Cost & Margins --}}
                <div class="col-md-4">
                  <div class="card bg-light p-2 border">
                    <h6 class="small fw-bold mb-2">Cost &amp; Margins</h6>

                    {{-- Per-type cost/sold grid --}}
                    <table class="table table-sm table-borderless mb-2" style="font-size:11px;">
                      <thead>
                        <tr class="text-muted"><th>Type</th><th>Qty</th><th>Cost (£)</th><th>Sold (£)</th></tr>
                      </thead>
                      <tbody>
                        @foreach (['adult' => 'Adult', 'gbe' => 'GBE', 'child' => 'Child', 'infant' => 'Infant'] as $type => $label)
                          <tr>
                            <td><span class="fw-semibold">{{ $label }}</span></td>
                            <td>{{ $flight_costs[$type]['qty'] ?? 0 }}</td>
                            <td><input type="number" wire:model="flight_costs.{{ $type }}.cost" step="0.01" min="0" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td>
                            <td><input type="number" wire:model="flight_costs.{{ $type }}.sold" step="0.01" min="0" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td>
                          </tr>
                        @endforeach
                        <tr class="border-top">
                          <td colspan="2" class="fw-bold small">SAFI</td>
                          <td colspan="2"><input type="number" wire:model="safi_charges" step="0.01" min="0" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td>
                        </tr>
                      </tbody>
                    </table>

                    {{-- Summary totals --}}
                    <div class="border-top pt-2">
                      <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">Flight Cost:</span>
                        <span>£{{ number_format($this->totalFlightCost, 2) }}</span>
                      </div>
                      <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">Flight Sold:</span>
                        <input type="number" wire:model="flight_selling_price" step="0.01" min="0" class="form-control form-control-sm" style="width:70px;font-size:11px;">
                      </div>
                      <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">Hotel Cost:</span>
                        <span>£{{ number_format((float)($hotel_actual_cost ?: 0), 2) }}</span>
                      </div>
                      <div class="d-flex justify-content-between small mb-1">
                        <span class="fw-semibold">Hotel Sold:</span>
                        <span>£{{ number_format((float)($hotel_selling_price ?: 0), 2) }}</span>
                      </div>
                      <div class="d-flex justify-content-between small mt-1 pt-1 border-top">
                        <span class="fw-semibold">Total Cost:</span>
                        <span class="fw-bold">£{{ number_format($this->totalCostPrice, 2) }}</span>
                      </div>
                      <div class="d-flex justify-content-between small">
                        <span class="fw-semibold">Total Sold:</span>
                        <span class="fw-bold">£{{ number_format($this->totalSoldPrice, 2) }}</span>
                      </div>
                      <div class="d-flex justify-content-between small mt-1 text-danger fw-bold">
                        <span>CC Charges:</span>
                        <input type="number" wire:model="cc_charges" step="0.01" min="0" class="form-control form-control-sm" style="width:70px;font-size:11px;">
                      </div>
                      <div class="d-flex justify-content-between small text-muted mt-1">
                        <span>Margin w/o CC:</span>
                        <span>£{{ number_format($this->totalSoldPrice - $this->totalCostPrice, 2) }}</span>
                      </div>
                      <div class="d-flex justify-content-between small fw-bold text-success mt-1" style="font-size:13px;">
                        <span>Total Margin:</span>
                        <span>£{{ number_format($this->totalMargin, 2) }}</span>
                      </div>
                      <div class="mt-2">
                        <div class="alert alert-warning py-1 px-2 small mb-0 text-center fw-semibold" style="font-size:10px;">
                          <i class="ph ph-warning-circle me-1"></i> Pending: margin update
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Activity Log with mandatory comment --}}
              <div class="card bg-light p-3 mb-3 border mt-3">
                <h6 class="small fw-bold mb-2">Activity Log</h6>
                @if (!empty($activity_log_entries))
                  <div class="mb-2 small" style="max-height:150px;overflow-y:auto;">
                    @foreach ($activity_log_entries as $entry)
                      <div class="d-flex gap-2 mb-1 align-items-start">
                        <span class="fw-bold text-nowrap">{{ $entry['agent'] }}</span>
                        <span class="text-muted small">{{ $entry['timestamp'] }}</span>
                        <span class="badge bg-secondary">{{ $entry['action'] }}</span>
                        <span>{{ $entry['comment'] }}</span>
                      </div>
                    @endforeach
                  </div>
                @endif
                <div class="d-flex gap-2">
                  <textarea wire:model="mandatory_comment" class="form-control form-control-sm @error('mandatory_comment') border-danger @enderror" rows="2" placeholder="Reason / Comment (REQUIRED — cannot save without this)"></textarea>
                  <button type="button" wire:click="addActivityEntry" class="btn btn-sm btn-outline-primary text-nowrap">Add Note</button>
                </div>
                @error('mandatory_comment') <small class="text-danger fw-bold">{{ $message }}</small> @enderror
              </div>

              {{-- Status toggles --}}
              <div class="row g-2">
                <div class="col-md-2">
                  <label class="form-label small mb-1 fw-semibold">Status <span class="text-danger">*</span></label>
                  <select wire:model="booking_status" class="form-select form-select-sm @error('booking_status') is-invalid @enderror">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  @error('booking_status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4 d-flex align-items-end gap-3">
                  <div class="form-check form-switch">
                    <input type="checkbox" wire:model="issuance_requested" class="form-check-input" id="iss_req">
                    <label class="form-check-label small" for="iss_req">Issuance Requested</label>
                  </div>
                  <div class="form-check form-switch">
                    <input type="checkbox" wire:model="refund_queue" class="form-check-input" id="ref_q">
                    <label class="form-check-label small" for="ref_q">Refund Queue</label>
                  </div>
                </div>
              </div>
            </div>
          @endif

          {{-- Navigation buttons --}}
          <div class="d-flex justify-content-between mt-3 pt-2 border-top">
            <div>
              @if ($step > 1)
                <button type="button" wire:click="prevStep" class="btn btn-sm btn-outline-secondary">Previous</button>
              @endif
              @if ($step > 1)
                <button type="button" wire:click="resetForm" class="btn btn-sm btn-outline-danger ms-2">Reset</button>
              @endif
            </div>
            <div class="d-flex gap-2">
              @if ($step < self::TOTAL_STEPS)
                <button type="button" wire:click="nextStep" class="btn btn-sm btn-primary">Next</button>
              @else
                <button type="button" wire:click="resetForm" class="btn btn-sm btn-outline-secondary">Reset</button>
                <button type="submit" class="btn btn-sm btn-primary px-4">Save Booking</button>
              @endif
            </div>
          </div>
        </form>
      @endif
    </div>
  </div>
</div>

<style>
.wizard-form .step-card { min-height: 200px; }
@media (max-width: 768px) {
  .wizard-steps { flex-wrap: wrap; gap: 0.5rem; }
  .wizard-steps .flex-grow-1 { display: none; }
}
[x-cloak] { display: none !important; }
</style>
