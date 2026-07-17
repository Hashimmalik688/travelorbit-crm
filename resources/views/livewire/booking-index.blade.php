<div>
    {{-- Page Header --}}
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>{{ $context === 'mine' ? 'My Bookings' : 'All Bookings' }}</h1>
        </div>
        <div class="to-page-header-right">
            @can('create', \App\Models\Booking::class)
              <a href="{{ route('bookings.create') }}" class="btn btn-orange btn-sm">
                  <i class="ph ph-plus me-1"></i> New Booking
              </a>
            @endcan
        </div>
    </div>

    @if(session()->has('success'))
      <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('success') }}</div>
    @endif

    {{-- Filter bar --}}
    <div style="padding:0 0 16px;">
        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div style="font-size:0.84rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;flex-shrink:0;">Filter</div>

          {{-- Date range - only for My Bookings --}}
          @if($context === 'mine')
            <div class="d-flex align-items-center gap-2" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:4px 12px;">
              <i class="ph ph-calendar" style="font-size:0.96rem;color:#475569;"></i>
              <input type="date" wire:model.live="dateFrom" style="border:none;outline:none;font-size:0.912rem;color:#374151;background:transparent;width:110px;">
              <span style="color:#64748B;font-size:0.9rem;">→</span>
              <input type="date" wire:model.live="dateTo"   style="border:none;outline:none;font-size:0.912rem;color:#374151;background:transparent;width:110px;">
            </div>
          @endif

          <div>
            @php $statusOpts = array_merge([['value'=>'','label'=>'All Statuses']], collect(\App\Models\Booking::STATUS_LABELS)->map(fn($lbl,$val)=>['value'=>$val,'label'=>$lbl])->values()->toArray()); @endphp
            <x-styled-select-sm modelName="statusFilter" :options="$statusOpts" placeholder="All Statuses" :live="true" />
          </div>
          <div>
            <x-styled-select-sm modelName="typeFilter" :options="[['value'=>'','label'=>'All Types'],['value'=>'flight','label'=>'Flight'],['value'=>'hotel','label'=>'Hotel'],['value'=>'umrah','label'=>'Umrah'],['value'=>'holiday','label'=>'Holiday'],['value'=>'visa','label'=>'Visa'],['value'=>'transfers','label'=>'Transfers'],['value'=>'excursion','label'=>'Excursion']]" placeholder="All Types" :live="true" />
          </div>
          @if($statusFilter || $typeFilter || $search)
            <button wire:click="$set('search',''); $set('statusFilter',''); $set('typeFilter','')"
              style="background:none;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:20px;padding:4px 12px;font-size:0.888rem;font-weight:600;cursor:pointer;">
              ✕ Clear
            </button>
          @endif
        </div>
    </div>

    {{-- Table - unified card --}}
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
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $booking->flightDetail?->departure_date ? \Carbon\Carbon::parse($booking->flightDetail->departure_date)->format('d M Y') : '-' }}</td>
                            <td class="text-end fw-semibold">£{{ number_format($booking->total_sale_price, 2) }}</td>
                            <td class="text-end">
                                <span class="fw-semibold {{ $booking->total_margin >= 0 ? 'text-success' : 'text-danger' }}">
                                    £{{ number_format($booking->total_margin, 2) }}
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
                                    $paymentType = $booking->payment?->booking_plan;
                                    $paymentMap = ['full' => 'success', 'awaiting' => 'warning', 'payment_plan' => 'info', 'dnpl' => 'danger'];
                                    $paymentLabel = ['full' => 'Full', 'awaiting' => 'Awaiting', 'payment_plan' => 'Plan', 'dnpl' => 'DNPL'];
                                @endphp
                                @if ($paymentType)
                                    <span class="badge bg-label-{{ $paymentMap[$paymentType] ?? 'secondary' }}">
                                        {{ $paymentLabel[$paymentType] ?? $paymentType }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 align-items-center">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View / Edit">
                                        <i class="ph ph-eye"></i>
                                    </a>
                                    @if(Auth::user()->hasPermission('bookings.delete'))
                                    <form method="POST" action="{{ route('bookings.destroy', $booking->id) }}" style="display:inline;"
                                        onsubmit="return confirm('Delete Booking #{{ $booking->booking_number }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </form>
                                    @endif
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
                Showing {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} of {{ $bookings->total() }} bookings
            </small>
            {{ $bookings->links() }}
        </div>
    </div>
</div>
