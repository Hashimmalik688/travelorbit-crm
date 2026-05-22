<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Edit Booking #{{ $booking->booking_number }}</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="{{ route('bookings.index') }}">Bookings</a> &rsaquo; <a href="{{ route('bookings.show', $booking) }}">#{{ $booking->booking_number }}</a> &rsaquo; Edit
            </div>
        </div>
        <div class="to-page-header-right">
            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline-secondary btn-sm">
                <i class="ph ph-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" form="edit-booking-form" class="btn btn-orange btn-sm">
                <i class="ph ph-check-circle me-1"></i> Save Changes
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form id="edit-booking-form" wire:submit="save" enctype="multipart/form-data">
        <div class="row g-3">
            {{-- Booking Info --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Booking Info</h6></div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small mb-1 fw-semibold">Lead Source <span class="text-danger">*</span></label>
                                <select wire:model="lead_source" class="form-select form-select-sm @error('lead_source') is-invalid @enderror">
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
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1 fw-semibold">Lead Nature</label>
                                <select wire:model="lead_nature" class="form-select form-select-sm">
                                    <option value="">Select</option>
                                    <option value="new_booking">New Booking</option>
                                    <option value="date_change">Date Change</option>
                                    <option value="refund_booking">Refund Booking</option>
                                    <option value="previous_booking">Previous Booking</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1 fw-semibold">Booking Type <span class="text-danger">*</span></label>
                                <select wire:model="booking_type" class="form-select form-select-sm @error('booking_type') is-invalid @enderror">
                                    <option value="">Select</option>
                                    <option value="flight">Flight</option>
                                    <option value="hotel">Hotel</option>
                                    <option value="umrah">Umrah</option>
                                    <option value="holiday">Holiday</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1 fw-semibold">Referral Name</label>
                                <input type="text" wire:model="referral_name" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-3">
                                    <input type="checkbox" wire:model.live="is_returning_or_referral" class="form-check-input" id="is_ret_edit">
                                    <label class="form-check-label small fw-semibold" for="is_ret_edit">Returning / Referral?</label>
                                </div>
                            </div>
                        </div>
                        @if ($is_returning_or_referral)
                            <div class="row g-2 mt-1">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-semibold">Old Booking Reference</label>
                                    <input type="text" wire:model="old_booking_reference" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1 fw-semibold">Last Payment Date</label>
                                    <input type="date" wire:model="last_payment_date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1 fw-semibold">Last Issue Date</label>
                                    <input type="date" wire:model="last_issue_date" class="form-control form-control-sm">
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Caller Info --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Caller Information</h6></div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Title</label><select wire:model="booker_title" class="form-select form-select-sm"><option value="">Title</option><option value="1">1 - Mr.</option><option value="2">2 - Ms.</option><option value="3">3 - Mrs.</option><option value="4">4 - Mstr</option><option value="5">5 - Miss</option><option value="6">6 - Dr.</option></select></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">First Name <span class="text-danger">*</span></label><input type="text" wire:model="booker_first_name" class="form-control form-control-sm @error('booker_first_name') is-invalid @enderror"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Last Name <span class="text-danger">*</span></label><input type="text" wire:model="booker_last_name" class="form-control form-control-sm @error('booker_last_name') is-invalid @enderror"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Mobile <span class="text-danger">*</span></label><input type="text" wire:model="booker_mobile" class="form-control form-control-sm @error('booker_mobile') is-invalid @enderror"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Landline</label><input type="text" wire:model="booker_landline" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Email</label><input type="email" wire:model="booker_email" class="form-control form-control-sm"></div>
                            <div class="col-md-3"><label class="form-label small mb-1 fw-semibold">Country</label><select wire:model="booker_country" class="form-select form-select-sm">@foreach ($countries as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach</select></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Postcode</label><input type="text" wire:model="booker_postcode" class="form-control form-control-sm"></div>
                            <div class="col-md-4"><label class="form-label small mb-1 fw-semibold">Address</label><textarea wire:model="booker_address" class="form-control form-control-sm" rows="1"></textarea></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Passengers --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Travellers</h6></div>
                    <div class="card-body py-2">
                        <div class="d-flex gap-3 mb-3 flex-wrap align-items-center">
                            @foreach (['adult' => 'Adult', 'gbe' => 'GBE', 'child' => 'Child', 'infant' => 'Infant'] as $type => $label)
                                <div class="d-flex align-items-center gap-1">
                                    <span class="small fw-semibold">{{ $label }}</span>
                                    <button type="button" wire:click="dec('{{ $type }}')" class="btn btn-sm btn-outline-secondary px-1 py-0">&#8722;</button>
                                    <span class="fw-bold small px-1">{{ ${$type.'Count'} }}</span>
                                    <button type="button" wire:click="inc('{{ $type }}')" class="btn btn-sm btn-outline-primary px-1 py-0">+</button>
                                </div>
                            @endforeach
                            <span class="badge bg-primary ms-2">{{ $this->totalPassengers }} traveller(s)</span>
                        </div>
                        @if (!empty($passengers))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0" style="font-size:11px;">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>Type</th><th>PTC</th><th>Title</th><th>First Name</th><th>Last Name</th><th>DOB</th><th>Passport #</th><th>Iss. Country</th><th>Nation.</th><th>NIC</th><th>Freq. Flyer</th><th>Status</th><th>E-Ticket</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($passengers as $i => $p)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td><span class="badge bg-secondary">{{ $this->passengerTypeLabel($p['type']) }}</span></td>
                                                <td>@if(!empty($p['ptc']))<span class="fw-bold" style="color:{{ $p['ptc'] === 'CNN' ? '#dc3545' : ($p['ptc'] === 'INF' ? '#ffc107' : '#198754') }}">{{ $p['ptc'] }}</span>@else<span class="text-muted">—</span>@endif</td>
                                                <td><select wire:model="passengers.{{ $i }}.title" class="form-select form-select-sm" style="width:60px;font-size:11px;"><option value="">--</option><option value="Mr.">Mr.</option><option value="Ms.">Ms.</option><option value="Mrs.">Mrs.</option><option value="Mstr">Mstr</option><option value="Miss">Miss</option><option value="Dr.">Dr.</option></select></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.first_name" class="form-control form-control-sm" style="width:80px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.last_name" class="form-control form-control-sm" style="width:85px;font-size:11px;"></td>
                                                <td><input type="date" wire:model="passengers.{{ $i }}.date_of_birth" class="form-control form-control-sm" style="width:105px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.passport_number" class="form-control form-control-sm" style="width:85px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.passport_issuing_country" class="form-control form-control-sm" style="width:70px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.nationality" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.national_id_number" class="form-control form-control-sm" style="width:75px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.frequent_flyer_number" class="form-control form-control-sm" style="width:75px;font-size:11px;"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.passenger_status_label" class="form-control form-control-sm" style="width:65px;font-size:11px;" placeholder="OK"></td>
                                                <td><input type="text" wire:model="passengers.{{ $i }}.e_ticket_number" class="form-control form-control-sm" style="width:95px;font-size:11px;" readonly></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Flight --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 small">Flight Information</h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary"><i class="ph ph-upload-simple me-1"></i> Upload</button>
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2 mb-2">
                            <div class="col-md-4"><label class="form-label small mb-1 fw-semibold">PNR</label><textarea wire:model="flight_pnr" class="form-control form-control-sm" rows="2" placeholder="Paste PNR content..."></textarea></div>
                            <div class="col-md-2 d-flex align-items-end"><button type="button" class="btn btn-warning btn-sm w-100 fw-bold mb-1"><i class="ph ph-magnifying-glass me-1"></i> Fetch PNR</button></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Folder Number</label><div class="input-group input-group-sm"><input type="text" wire:model="flight_folder_number" class="form-control form-control-sm"><button type="button" class="btn btn-sm btn-outline-secondary">Edit</button></div></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Locator</label><input type="text" wire:model="flight_locator" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Airline Locator</label><input type="text" wire:model="flight_airline_locator" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Type/Issuer</label><input type="text" wire:model="flight_type_issuer" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Reservation Status</label><input type="text" wire:model="flight_reservation_status" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Airline</label><input type="text" wire:model="flight_airline" class="form-control form-control-sm" maxlength="2" placeholder="EK"></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Vendor</label><input type="text" wire:model="flight_vendor" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">GDS</label><input type="text" wire:model="flight_gds" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Ticket Limit</label><input type="datetime-local" wire:model="flight_ticket_issue_limit" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">City Code</label><input type="text" wire:model="flight_city_code" class="form-control form-control-sm" maxlength="5"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Dep. Airport</label><input type="text" wire:model="flight_departure_airport" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Arr. Airport</label><input type="text" wire:model="flight_arrival_airport" class="form-control form-control-sm"></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Dep. Date</label><input type="date" wire:model="flight_departure_date" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Ret. Date</label><input type="date" wire:model="flight_return_date" class="form-control form-control-sm"></div>
                            <div class="col-md-4 d-flex align-items-end gap-3">
                                <div class="form-check form-check-inline mb-1"><input type="checkbox" wire:model="flight_atol" class="form-check-input" id="eatol"><label class="form-check-label small" for="eatol">ATOL</label></div>
                                <div class="form-check form-check-inline mb-1"><input type="checkbox" wire:model="flight_safi" class="form-check-input" id="esafi"><label class="form-check-label small" for="esafi">SAFI</label></div>
                            </div>
                            <div class="col-md-4 d-flex gap-2 align-items-end">
                                <button type="button" class="btn btn-sm btn-outline-primary">Split Flight Details</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary">Edit Flight Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hotel --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Hotel Information</h6></div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <div class="col-md-4"><label class="form-label small mb-1 fw-semibold">Hotel Name</label><input type="text" wire:model="hotel_name" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">City</label><input type="text" wire:model="hotel_city" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Room Type</label><input type="text" wire:model="hotel_room_type" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Status</label><select wire:model="hotel_status" class="form-select form-select-sm"><option value="confirmed">Confirmed</option><option value="on_holding">On Holding</option><option value="cancelled">Cancelled</option></select></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Check In</label><input type="date" wire:model="hotel_check_in" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Check Out</label><input type="date" wire:model="hotel_check_out" class="form-control form-control-sm"></div>
                            <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Occupants</label><input type="number" wire:model="hotel_occupants" class="form-control form-control-sm" min="1"></div>
                            <div class="col-md-3"><label class="form-label small mb-1 fw-semibold">Actual Cost (£)</label><input type="number" wire:model="hotel_actual_cost" class="form-control form-control-sm" step="0.01" min="0"></div>
                            <div class="col-md-3"><label class="form-label small mb-1 fw-semibold">Selling Price (£)</label><input type="number" wire:model="hotel_selling_price" class="form-control form-control-sm" step="0.01" min="0"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment + Cost & Margins --}}
            <div class="col-md-7">
                <div class="card h-100">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Payment</h6></div>
                    <div class="card-body py-2">
                        <div class="row g-2 mb-2">
                            <div class="col-md-3"><label class="form-label small mb-1 fw-semibold">Payment Type <span class="text-danger">*</span></label><select wire:model="payment_type" class="form-select form-select-sm @error('payment_type') is-invalid @enderror"><option value="">Select</option><option value="full">Full Payment</option><option value="awaiting">Awaiting</option><option value="payment_plan">Payment Plan</option><option value="dnpl">DNPL</option></select></div>
                            <div class="col-md-4"><label class="form-label small mb-1 fw-semibold">Payment Mode 1 <span class="text-danger">*</span></label><select wire:model="payment_mode" class="form-select form-select-sm"><option value="">Select</option><option value="epay_debit">Epay Debit</option><option value="epay_credit">Epay Credit</option><option value="amex">AMEX</option><option value="klarna">Klarna</option><option value="superpay">SuperPay</option><option value="clearpay">ClearPay</option><option value="stripe">Stripe</option><option value="cash">Cash/Bank</option><option value="debit_card">Debit Card</option><option value="credit_card">Credit Card</option><option value="refund">Refund</option><option value="previous_booking">Previous Booking</option><option value="dnpl">DNPL</option></select></div>
                            <div class="col-md-4"><label class="form-label small mb-1 fw-semibold">Payment Mode 2</label><select wire:model="payment_mode_2" class="form-select form-select-sm"><option value="">None</option><option value="epay_debit">Epay Debit</option><option value="epay_credit">Epay Credit</option><option value="amex">AMEX</option><option value="klarna">Klarna</option><option value="superpay">SuperPay</option><option value="clearpay">ClearPay</option><option value="stripe">Stripe</option><option value="cash">Cash/Bank</option><option value="debit_card">Debit Card</option><option value="credit_card">Credit Card</option><option value="refund">Refund</option><option value="previous_booking">Previous Booking</option><option value="dnpl">DNPL</option></select></div>
                        </div>
                        <div class="row g-2 mb-2">
                            @if (in_array($payment_type, ['full', 'awaiting', 'payment_plan']))
                                <div class="col-md-3"><label class="form-label small mb-1 fw-semibold">Amount Paid (£)</label><input type="number" wire:model="amount_paid" class="form-control form-control-sm" step="0.01" min="0"></div>
                            @endif
                            @if ($payment_type === 'payment_plan')
                                <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Period</label><select wire:model="installment_period" class="form-select form-select-sm"><option value="none">None</option><option value="30_days">30 Days</option><option value="2_months">2 Months</option></select></div>
                                <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">1st Payment (£)</label><input type="number" wire:model="installment_first_amount" class="form-control form-control-sm" step="0.01" min="0"></div>
                            @endif
                            @if (in_array($payment_type, ['awaiting', 'payment_plan']))
                                <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Due Date</label><input type="date" wire:model="due_date" class="form-control form-control-sm"></div>
                            @endif
                            @if ($payment_type === 'dnpl')
                                <div class="col-md-2"><label class="form-label small mb-1 fw-semibold">Deposit (£)</label><input type="number" wire:model="deposit_amount" class="form-control form-control-sm" step="0.01" min="0"></div>
                            @endif
                            <div class="col-md-2 d-flex align-items-end"><div class="form-check form-switch"><input type="checkbox" wire:model="debit_card_change" class="form-check-input" id="edc_change"><label class="form-check-label small" for="edc_change">Debit Card Change</label></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Cost &amp; Margins</h6></div>
                    <div class="card-body py-2">
                        <table class="table table-sm table-borderless mb-2" style="font-size:11px;">
                            <thead><tr class="text-muted"><th>Type</th><th>Qty</th><th>Cost (£)</th><th>Sold (£)</th></tr></thead>
                            <tbody>
                                @foreach (['adult' => 'Adult', 'gbe' => 'GBE', 'child' => 'Child', 'infant' => 'Infant'] as $type => $label)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $label }}</span></td>
                                        <td>{{ $flight_costs[$type]['qty'] ?? 0 }}</td>
                                        <td><input type="number" wire:model="flight_costs.{{ $type }}.cost" step="0.01" min="0" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td>
                                        <td><input type="number" wire:model="flight_costs.{{ $type }}.sold" step="0.01" min="0" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td>
                                    </tr>
                                @endforeach
                                <tr class="border-top"><td colspan="2" class="fw-bold small">SAFI</td><td colspan="2"><input type="number" wire:model="safi_charges" step="0.01" min="0" class="form-control form-control-sm" style="width:65px;font-size:11px;"></td></tr>
                            </tbody>
                        </table>
                        <div class="border-top pt-1">
                            <div class="d-flex justify-content-between small"><span class="fw-semibold">Flight Cost:</span><span>{{ number_format($this->totalFlightCost, 2) }}</span></div>
                            <div class="d-flex justify-content-between small"><span class="fw-semibold">Flight Sold:</span><input type="number" wire:model="flight_selling_price" step="0.01" min="0" class="form-control form-control-sm" style="width:70px;font-size:11px;"></div>
                            <div class="d-flex justify-content-between small text-danger fw-bold"><span>CC Charges:</span><input type="number" wire:model="cc_charges" step="0.01" min="0" class="form-control form-control-sm" style="width:70px;font-size:11px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Status</h6></div>
                    <div class="card-body py-2">
                        <div class="mb-2"><label class="form-label small mb-1 fw-semibold">Status <span class="text-danger">*</span></label><select wire:model="booking_status" class="form-select form-select-sm @error('booking_status') is-invalid @enderror"><option value="pending">Pending</option><option value="confirmed">Confirmed</option><option value="cancelled">Cancelled</option></select></div>
                        <div class="form-check form-switch mb-1"><input type="checkbox" wire:model="issuance_requested" class="form-check-input" id="e_iss_req"><label class="form-check-label small" for="e_iss_req">Issuance Requested</label></div>
                        <div class="form-check form-switch"><input type="checkbox" wire:model="refund_queue" class="form-check-input" id="e_ref_q"><label class="form-check-label small" for="e_ref_q">Refund Queue</label></div>
                    </div>
                </div>
            </div>

            {{-- Documents --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Documents</h6></div>
                    <div class="card-body py-2">
                        @foreach ($existingDocuments as $ed)
                            <div class="d-flex justify-content-between align-items-center mb-1 small"><span>{{ $ed['file_name'] ?? 'Unknown' }}</span><button type="button" wire:click="deleteExistingDocument({{ $ed['id'] ?? 0 }})" class="btn btn-sm btn-outline-danger px-1 py-0">&times;</button></div>
                        @endforeach
                        @foreach ($newDocuments as $i => $doc)
                            <div class="d-flex gap-2 align-items-center mb-1"><input type="file" wire:model="newDocuments.{{ $i }}" class="form-control form-control-sm" style="font-size:11px;"><select wire:model="newDocumentTypes.{{ $i }}" class="form-select form-select-sm" style="width:90px;font-size:11px;"><option value="">Type</option><option value="passport">Passport</option><option value="visa">Visa</option><option value="itinerary">Itinerary</option><option value="invoice">Invoice</option><option value="other">Other</option></select><button type="button" wire:click="removeDocument({{ $i }})" class="btn btn-sm btn-outline-danger px-1 py-0">&times;</button></div>
                        @endforeach
                        <button type="button" wire:click="addDocument" class="btn btn-sm btn-outline-secondary mt-1">+ Add</button>
                    </div>
                </div>
            </div>

            {{-- Activity Log — mandatory comment --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Activity Log</h6></div>
                    <div class="card-body py-2">
                        @if ($booking->activityLogs && $booking->activityLogs->count() > 0)
                            <div class="mb-2 small" style="max-height:120px;overflow-y:auto;">
                                @foreach ($booking->activityLogs->take(10) as $log)
                                    <div class="d-flex gap-2 mb-1 align-items-start" style="font-size:10px;">
                                        <span class="fw-bold text-nowrap">{{ $log->user?->name ?? 'System' }}</span>
                                        <span class="text-muted">{{ $log->created_at->format('d M H:i') }}</span>
                                        <span class="badge bg-secondary">{{ $log->action }}</span>
                                        <span>{{ $log->comment }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <textarea wire:model="mandatory_comment" class="form-control form-control-sm @error('mandatory_comment') border-danger @enderror" rows="2" placeholder="Reason / Comment (REQUIRED — cannot save without this)"></textarea>
                        @error('mandatory_comment') <small class="text-danger fw-bold d-block mt-1">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>

            {{-- Existing Comments --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-2"><h6 class="card-title mb-0 small">Previous Comments</h6></div>
                    <div class="card-body py-2">
                        @forelse ($existingComments as $c)
                            <div class="border-bottom mb-2 pb-2 small">
                                <div class="fw-semibold">{{ $c['user']['name'] ?? 'System' }} <span class="text-muted">{{ \Carbon\Carbon::parse($c['created_at'])->format('d M H:i') }}</span>
                                    @if ($c['is_mandatory'] ?? false) <span class="badge bg-warning text-dark ms-1">mandatory</span> @endif
                                </div>
                                <p class="mb-0">{{ $c['comment'] }}</p>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No comments yet.</p>
                        @endforelse
                        <textarea wire:model="newComment" class="form-control form-control-sm mt-2" rows="2" placeholder="Add optional comment..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-orange btn-sm px-4">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>
