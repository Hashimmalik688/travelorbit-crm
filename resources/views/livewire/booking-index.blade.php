<div>
    {{-- Page Header — unified pattern --}}
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>All Bookings</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; Bookings
            </div>
        </div>
        <div class="to-page-header-right">
            <a href="{{ route('bookings.create') }}" class="btn btn-orange btn-sm">
                <i class="ph ph-plus me-1"></i> New Booking
            </a>
        </div>
    </div>

    {{-- Filter bar — unified --}}
    <div class="to-filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control" placeholder="Search by booking # or booker name..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" wire:model.live="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="issued">Issued</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="refund_queue">Refund Queue</option>
                    <option value="awaiting_issuance">Awaiting Issuance</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model.live="typeFilter">
                    <option value="">All Types</option>
                    <option value="flight">Flight</option>
                    <option value="hotel">Hotel</option>
                    <option value="umrah">Umrah</option>
                    <option value="holiday">Holiday</option>
                    <option value="transfers">Transfers</option>
                    <option value="ancillary_services">Ancillary Services</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                @if ($search || $statusFilter || $typeFilter)
                    <button class="btn btn-outline-primary btn-sm" wire:click="$set('search', ''); $set('statusFilter', ''); $set('typeFilter', '')">
                        <i class="ph ph-x me-1"></i> Clear Filters
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Table — unified card --}}
    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Booker</th>
                        <th>Pax</th>
                        <th>Route</th>
                        <th>Departure</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-end">Margin</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                <span class="fw-semibold">#{{ $booking->booking_number }}</span>
                                <small class="d-block text-muted">{{ $booking->booking_ref }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle">
                                            {{ strtoupper(substr($booking->booker_name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="fw-semibold d-block">{{ $booking->booker_name }}</span>
                                        <small class="text-muted">{{ $booking->booker_mobile }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $booking->passengers->count() }}</td>
                            <td>
                                @php $fd = $booking->flightDetail; @endphp
                                @if ($fd && ($fd->departure_airport || $fd->arrival_airport))
                                    <span class="badge bg-label-primary">{{ $fd->departure_airport }}→{{ $fd->arrival_airport }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $booking->flightDetail?->departure_date ? \Carbon\Carbon::parse($booking->flightDetail->departure_date)->format('d M Y') : '—' }}</td>
                            <td class="text-end fw-semibold">£{{ number_format($booking->total_sale_price, 0) }}</td>
                            <td class="text-end">
                                <span class="fw-semibold {{ $booking->total_margin >= 0 ? 'text-success' : 'text-danger' }}">
                                    £{{ number_format($booking->total_margin, 0) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $colorMap = ['pending' => 'warning', 'confirmed' => 'primary', 'issued' => 'success',
                                                 'cancelled' => 'danger', 'refund_queue' => 'danger', 'awaiting_issuance' => 'info'];
                                @endphp
                                <span class="badge bg-label-{{ $colorMap[$booking->booking_status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $paymentType = $booking->payment?->payment_type;
                                    $paymentMap = ['full' => 'success', 'awaiting' => 'warning', 'payment_plan' => 'info', 'dnpl' => 'danger'];
                                    $paymentLabel = ['full' => 'Full', 'awaiting' => 'Awaiting', 'payment_plan' => 'Plan', 'dnpl' => 'DNPL'];
                                @endphp
                                @if ($paymentType)
                                    <span class="badge bg-label-{{ $paymentMap[$paymentType] ?? 'secondary' }}">
                                        {{ $paymentLabel[$paymentType] ?? $paymentType }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View">
                                        <i class="ph ph-eye"></i>
                                    </a>
                                    <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-book-open"></i></div>
                                    <h5>No bookings found</h5>
                                    <p>{{ ($search || $statusFilter || $typeFilter) ? 'Try adjusting your filters.' : 'Create your first booking to get started.' }}</p>
                                    @if (!$search && !$statusFilter && !$typeFilter)
                                        <a href="{{ route('bookings.create') }}" class="btn btn-orange btn-sm">
                                            <i class="ph ph-plus me-1"></i> New Booking
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Showing {{ $bookings->firstItem() ?? 0 }} – {{ $bookings->lastItem() ?? 0 }} of {{ $bookings->total() }} bookings
            </small>
            {{ $bookings->links() }}
        </div>
    </div>
</div>
