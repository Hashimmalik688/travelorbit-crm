<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Booking #{{ $booking->booking_number }}</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="{{ route('bookings.index') }}">Bookings</a> &rsaquo; #{{ $booking->booking_number }}
            </div>
        </div>
        <div class="to-page-header-right">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ph ph-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-orange btn-sm">
                <i class="ph ph-pencil-simple me-1"></i> Edit
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Status badges row --}}
    <div class="d-flex gap-2 mb-4 flex-wrap animate-in">
        <span class="badge bg-label-{{ $booking->booking_status === 'confirmed' ? 'primary' : ($booking->booking_status === 'pending' ? 'warning' : ($booking->booking_status === 'cancelled' ? 'danger' : 'secondary')) }}">{{ ucfirst($booking->booking_status) }}</span>
        <span class="badge bg-label-secondary">{{ ucfirst($booking->booking_type) }}</span>
        @if($booking->issuance_requested_at)<span class="badge bg-label-warning">Issuance Requested</span>@endif
        @if($booking->refund_requested_at)<span class="badge bg-label-danger">Refund Requested</span>@endif
        <small class="text-muted ms-1">{{ $booking->created_at->format('d M Y H:i') }} · {{ $booking->user?->name ?? 'N/A' }}</small>
    </div>

    <div class="row g-3">

        {{-- Lead + Booker --}}
        <div class="col-md-6 animate-in">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Lead Info</h6></div>
                <div class="card-body py-2">
                    <div class="row g-2 small">
                        <div class="col-6"><span class="text-muted">Source:</span> {{ str_replace('_', ' ', ucfirst($booking->lead_source)) }}</div>
                        <div class="col-6"><span class="text-muted">Type:</span> {{ ucfirst($booking->booking_type) }}</div>
                        @if($booking->referral_name)<div class="col-12"><span class="text-muted">Referral:</span> {{ $booking->referral_name }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 animate-in" style="animation-delay:0.06s;">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Booker Info</h6></div>
                <div class="card-body py-2">
                    <div class="row g-2 small">
                        <div class="col-6"><span class="text-muted">Name:</span> {{ $booking->booker_name }}</div>
                        <div class="col-6"><span class="text-muted">Mobile:</span> {{ $booking->booker_mobile }}</div>
                        @if($booking->booker_title)<div class="col-6"><span class="text-muted">Title:</span> {{ \App\Models\Booking::TITLES[$booking->booker_title] ?? $booking->booker_title }}</div>@endif
                        @if($booking->booker_landline)<div class="col-6"><span class="text-muted">Landline:</span> {{ $booking->booker_landline }}</div>@endif
                        @if($booking->booker_whatsapp)<div class="col-6"><span class="text-muted">WhatsApp:</span> {{ $booking->booker_whatsapp }}</div>@endif
                        @if($booking->booker_email)<div class="col-6"><span class="text-muted">Email:</span> {{ $booking->booker_email }}</div>@endif
                        @if($booking->booker_address)<div class="col-12"><span class="text-muted">Address:</span> {{ $booking->booker_address }}{{ $booking->booker_postcode ? ', '.$booking->booker_postcode : '' }}{{ $booking->booker_country ? ', '.$booking->booker_country : '' }}</div>@endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Flight Detail --}}
        @if($booking->flightDetail)
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Flight Detail</h6></div>
                <div class="card-body py-2">
                    <div class="row g-2 small">
                        @if($booking->flightDetail->pnr)<div class="col-4"><span class="text-muted">PNR:</span> {{ $booking->flightDetail->pnr }}</div>@endif
                        @if($booking->flightDetail->airline)<div class="col-4"><span class="text-muted">Airline:</span> {{ $booking->flightDetail->airline }}</div>@endif
                        @if($booking->flightDetail->vendor)<div class="col-4"><span class="text-muted">Vendor:</span> {{ $booking->flightDetail->vendor }}</div>@endif
                        @if($booking->flightDetail->gds)<div class="col-4"><span class="text-muted">GDS:</span> {{ $booking->flightDetail->gds }}</div>@endif
                        @if($booking->flightDetail->city_code)<div class="col-4"><span class="text-muted">City Code:</span> {{ $booking->flightDetail->city_code }}</div>@endif
                        @if($booking->flightDetail->ticket_issue_limit)<div class="col-4"><span class="text-muted">Ticket Limit:</span> {{ $booking->flightDetail->ticket_issue_limit->format('d M Y H:i') }}</div>@endif
                        <div class="col-4"><span class="text-muted">ATOL:</span> {{ $booking->flightDetail->atol ? 'Yes' : 'No' }}</div>
                        <div class="col-4"><span class="text-muted">SAFI:</span> {{ $booking->flightDetail->safi ? 'Yes' : 'No' }}</div>
                        @if($booking->flightDetail->departure_airport || $booking->flightDetail->arrival_airport)
                            <div class="col-12"><span class="text-muted">Route:</span> {{ $booking->flightDetail->departure_airport }} → {{ $booking->flightDetail->arrival_airport }}</div>
                        @endif
                        @if($booking->flightDetail->departure_date)<div class="col-6"><span class="text-muted">Departure:</span> {{ $booking->flightDetail->departure_date->format('d M Y') }}</div>@endif
                        @if($booking->flightDetail->return_date)<div class="col-6"><span class="text-muted">Return:</span> {{ $booking->flightDetail->return_date->format('d M Y') }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Flight Costs --}}
        @if($booking->flightCosts->isNotEmpty())
        <div class="col-md-{{ $booking->flightDetail ? '6' : '12' }}">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Flight Costs</h6></div>
                <div class="card-body py-2">
                    <table class="table table-sm table-borderless small mb-0">
                        <thead><tr><th>Type</th><th>Cost (£)</th><th>Qty</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach($booking->flightCosts as $c)
                                <tr>
                                    <td>{{ ucfirst($c->cost_type) }}</td>
                                    <td>{{ number_format($c->cost, 2) }}</td>
                                    <td>{{ $c->quantity }}</td>
                                    <td class="text-end">{{ number_format($c->cost * $c->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between small mt-2 pt-1 border-top">
                        <span class="fw-semibold">Total Cost:</span><span class="fw-bold">{{ number_format($booking->total_cost_price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="fw-semibold">Selling:</span><span class="fw-bold">{{ number_format($booking->flightDetail->selling_price ?? 0, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mt-1">
                        <span class="fw-semibold text-success">Margin:</span><span class="fw-bold text-success">{{ number_format($booking->total_margin, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Hotels --}}
        @if($booking->hotels->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Hotels ({{ $booking->hotels->count() }})</h6></div>
                <div class="card-body py-2">
                    @foreach($booking->hotels as $hotel)
                        <div class="row g-2 small mb-2 pb-2 border-bottom">
                            <div class="col-3"><span class="text-muted">Name:</span> {{ $hotel->hotel_name }}</div>
                            <div class="col-2"><span class="text-muted">City:</span> {{ $hotel->city }}</div>
                            <div class="col-2"><span class="text-muted">Room:</span> {{ $hotel->room_type }}</div>
                            <div class="col-2"><span class="text-muted">Status:</span> {{ ucfirst($hotel->booking_status) }}</div>
                            <div class="col-2"><span class="text-muted">Check In:</span> {{ $hotel->check_in?->format('d M Y') }}</div>
                            <div class="col-1"><span class="text-muted">Out:</span> {{ $hotel->check_out?->format('d M Y') }}</div>
                            <div class="col-2"><span class="text-muted">Occupants:</span> {{ $hotel->occupants }}</div>
                            <div class="col-2"><span class="text-muted">Cost:</span> £{{ number_format($hotel->actual_cost, 2) }}</div>
                            <div class="col-2"><span class="text-muted">Selling:</span> £{{ number_format($hotel->selling_price, 2) }}</div>
                            <div class="col-2"><span class="text-muted">Margin:</span> <span class="{{ ($hotel->selling_price - $hotel->actual_cost) >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($hotel->selling_price - $hotel->actual_cost, 2) }}</span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Passengers --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 small">Passengers ({{ $booking->passengers->count() }})</h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-label-primary">Adults: {{ $booking->passengers->where('passenger_type','adult')->count() }}</span>
                        <span class="badge bg-label-info">Youth: {{ $booking->passengers->where('passenger_type','youth')->count() }}</span>
                        <span class="badge bg-label-warning">Child: {{ $booking->passengers->where('passenger_type','child')->count() }}</span>
                        <span class="badge bg-label-secondary">Infant: {{ $booking->passengers->where('passenger_type','infant')->count() }}</span>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th><th>Type</th><th>Name</th><th>DOB</th><th>Passport</th><th>Iss. Country</th><th>Nationality</th><th>NIC</th><th>Status</th><th>Ticket #</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($booking->passengers as $i => $pax)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><span class="badge bg-secondary">{{ $pax->type_label }}</span></td>
                                        <td>{{ $pax->display_name }}</td>
                                        <td>{{ $pax->date_of_birth?->format('d/m/Y') }}</td>
                                        <td>{{ $pax->passport_number }}</td>
                                        <td>{{ $pax->passport_issuing_country }}</td>
                                        <td>{{ $pax->nationality }}</td>
                                        <td>{{ $pax->national_id_number }}</td>
                                        <td>{{ $pax->passenger_status_label }}</td>
                                        <td>{{ $pax->ticket_number }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment + Documents + Comments --}}
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Payment</h6></div>
                <div class="card-body py-2">
                    @if($booking->payment)
                        <div class="small"><span class="text-muted">Type:</span> {{ ucfirst(str_replace('_',' ',$booking->payment->payment_type)) }}</div>
                        <div class="small"><span class="text-muted">Mode:</span> {{ str_replace('_',' ',ucfirst($booking->payment->payment_mode)) }}</div>
                        <div class="small"><span class="text-muted">Total:</span> {{ number_format($booking->payment->total_amount,2) }}</div>
                        <div class="small"><span class="text-muted">Paid:</span> {{ number_format($booking->payment->amount_paid,2) }}</div>
                        <div class="small"><span class="text-muted">Balance:</span> <span class="{{ $booking->payment->balance_remaining > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($booking->payment->balance_remaining,2) }}</span></div>
                        @if($booking->payment->due_date)<div class="small"><span class="text-muted">Due:</span> {{ $booking->payment->due_date->format('d M Y') }}</div>@endif
                        @if($booking->payment->is_deposit_nonrefundable)<div class="small text-danger">Non-refundable deposit</div>@endif
                    @else <p class="text-muted small mb-0">No payment recorded</p> @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Documents</h6></div>
                <div class="card-body py-2">
                    @if($booking->documents->isNotEmpty())
                        @foreach($booking->documents as $doc)
                            <div class="d-flex justify-content-between align-items-center mb-2 small">
                                <span><i class="bx bx-file me-1"></i>{{ $doc->file_name }}</span>
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-download"></i></a>
                            </div>
                        @endforeach
                    @else <p class="text-muted small mb-0">None</p> @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header py-2"><h6 class="card-title mb-0 small">Comments</h6></div>
                <div class="card-body py-2">
                    @if($booking->comments->isNotEmpty())
                        @foreach($booking->comments as $c)
                            <div class="border-bottom mb-2 pb-2 small">
                                <div class="fw-semibold">{{ $c->user?->name ?? 'System' }} <span class="text-muted">{{ $c->created_at->format('d M H:i') }}</span></div>
                                <p class="mb-0">{{ $c->comment }}</p>
                            </div>
                        @endforeach
                    @else <p class="text-muted small mb-0">No comments</p> @endif
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-warning btn-sm"><i class="bx bx-edit"></i> Edit</a>
            </div>
        </div>
    </div>
</div>
