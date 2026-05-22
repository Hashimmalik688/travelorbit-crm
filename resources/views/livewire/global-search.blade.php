<div class="position-relative" x-data="{ open: @entangle('isOpen') }" @click.away="open = false; $wire.closeDropdown()">
    <div class="d-flex align-items-center">
        <i class="icon-base bx bx-search icon-md" wire:click="$set('isOpen', true)"></i>
        <input
            type="text"
            class="form-control border-0 shadow-none ps-1 ps-sm-2"
            placeholder="Search bookings or customers..."
            wire:model.live.debounce.250ms="query"
            @focus="$wire.set('isOpen', true)"
        >
    </div>

    @if ($isOpen && (count($bookings) > 0 || count($customers) > 0))
        <div class="position-absolute top-100 start-0 mt-2 bg-white rounded-3 shadow-lg p-3" style="width: 360px; z-index: 1050; max-height: 400px; overflow-y: auto;">
            @if (count($bookings) > 0)
                <small class="text-muted text-uppercase fw-semibold mb-2 d-block">Bookings</small>
                @foreach ($bookings as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="d-block text-decoration-none py-2 px-2 rounded-2 hover-bg-light" wire:click="closeDropdown">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold text-dark">#{{ $booking->booking_number }}</span>
                                <small class="text-muted ms-2">{{ $booking->booker_name }}</small>
                            </div>
                            <span class="badge bg-label-{{ match($booking->booking_status) {'confirmed' => 'primary', 'issued' => 'success', 'pending' => 'warning', 'cancelled' => 'danger', default => 'secondary'} }}">
                                {{ ucfirst($booking->booking_status) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            @endif

            @if (count($customers) > 0)
                @if (count($bookings) > 0)
                    <div class="dropdown-divider my-2"></div>
                @endif
                <small class="text-muted text-uppercase fw-semibold mb-2 d-block">Customers</small>
                @foreach ($customers as $customer)
                    <a href="{{ route('customers.show', $customer->phone) }}" class="d-block text-decoration-none py-2 px-2 rounded-2 hover-bg-light" wire:click="closeDropdown">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <span class="fw-semibold text-dark">{{ $customer->name }}</span>
                                <small class="text-muted d-block">{{ $customer->phone }}</small>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    @elseif ($isOpen && strlen($query) >= 2)
        <div class="position-absolute top-100 start-0 mt-2 bg-white rounded-3 shadow-lg p-3" style="width: 360px; z-index: 1050;">
            <small class="text-muted text-center d-block">No results found.</small>
        </div>
    @endif
</div>
