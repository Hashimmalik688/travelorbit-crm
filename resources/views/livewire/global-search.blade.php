<div class="position-relative" x-data="{ open: @entangle('isOpen'), showFilter: false }" @click.away="open = false; $wire.closeDropdown()">
    <div class="d-flex align-items-center gap-1">
        {{-- Filter button --}}
        <button type="button" @click="showFilter = !showFilter"
            class="btn btn-sm d-flex align-items-center gap-1"
            style="background:rgba(51,46,158,0.08);color:#332E9E;border:1px solid rgba(51,46,158,0.15);border-radius:8px;font-size:0.68rem;font-weight:600;padding:4px 8px;white-space:nowrap;flex-shrink:0;">
            <i class="ph ph-funnel" style="font-size:0.75rem;"></i>
            <span class="d-none d-md-inline">
                @switch($filter)
                    @case('booking_reference') Ref @break
                    @case('name') Name @break
                    @case('locator') Locator @break
                    @case('email') Email @break
                    @case('phone') Phone @break
                    @case('airline_reference') Airline Ref @break
                    @default Filter
                @endswitch
            </span>
        </button>

        {{-- Filter dropdown --}}
        <div x-show="showFilter" x-cloak @click.away="showFilter = false"
            class="position-absolute top-100 start-0 mt-1 bg-white rounded-3 shadow-lg py-1" style="z-index:1060;min-width:160px;">
            @foreach ([
                ['value'=>'booking_reference','label'=>'Booking Reference','icon'=>'ph-hash'],
                ['value'=>'name','label'=>'Name','icon'=>'ph-user'],
                ['value'=>'locator','label'=>'Locator','icon'=>'ph-identification-card'],
                ['value'=>'email','label'=>'Email','icon'=>'ph-envelope'],
                ['value'=>'phone','label'=>'Phone Number','icon'=>'ph-phone'],
                ['value'=>'airline_reference','label'=>'Airline Reference','icon'=>'ph-airplane'],
            ] as $opt)
                <button type="button" wire:click="$set('filter','{{ $opt['value'] }}')" @click="showFilter = false"
                    class="d-flex align-items-center gap-2 w-100 text-start px-3 py-2 border-0 bg-transparent"
                    style="font-size:0.72rem;{{ $filter === $opt['value'] ? 'background:rgba(51,46,158,0.08);color:#332E9E;font-weight:600;' : 'color:#374151;' }}">
                    <i class="ph {{ $opt['icon'] }}" style="font-size:0.8rem;width:18px;text-align:center;"></i>
                    <span>{{ $opt['label'] }}</span>
                    @if($filter === $opt['value'])
                        <i class="ph ph-check ms-auto" style="font-size:0.75rem;"></i>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Search input --}}
        <div class="d-flex align-items-center flex-grow-1">
            <i class="ph ph-magnifying-glass icon-md" style="color:#94A3B8;font-size:1rem;" wire:click="$set('isOpen', true)"></i>
            <input
                type="text"
                class="form-control border-0 shadow-none ps-1 ps-sm-2"
                placeholder="Search bookings..."
                wire:model.live.debounce.250ms="query"
                @focus="$wire.set('isOpen', true)"
            >
        </div>
    </div>

    @if ($isOpen && count($bookings) > 0)
        <div class="position-absolute top-100 start-0 mt-2 bg-white rounded-3 shadow-lg p-3" style="width: 420px; z-index: 1050; max-height: 400px; overflow-y: auto;">
            <small class="text-muted text-uppercase fw-semibold mb-2 d-block">Bookings</small>
            @foreach ($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}" class="d-block text-decoration-none py-2 px-2 rounded-2 hover-bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold text-dark">#{{ $booking->booking_number }}</span>
                            <small class="text-muted ms-2">{{ $booking->booker_name }}</small>
                        </div>
                        <span class="badge bg-label-{{ match($booking->booking_status) {'confirmed' => 'primary', 'issued' => 'success', 'pending' => 'warning', 'cancelled' => 'danger', default => 'secondary'} }}">
                            {{ ucfirst($booking->booking_status) }}
                        </span>
                    </div>
                    @if(in_array($filter, ['locator','airline_reference']) && $booking->flightDetail)
                        <small class="text-muted" style="font-size:0.68rem;">
                            {{ $filter === 'locator' ? 'Locator: '.$booking->flightDetail->locator : 'AL: '.$booking->flightDetail->airline_locator }}
                        </small>
                    @endif
                </a>
            @endforeach
        </div>
    @elseif ($isOpen && strlen($query) >= 1)
        <div class="position-absolute top-100 start-0 mt-2 bg-white rounded-3 shadow-lg p-3" style="width: 420px; z-index: 1050;">
            <small class="text-muted text-center d-block">No results found.</small>
        </div>
    @endif
</div>
