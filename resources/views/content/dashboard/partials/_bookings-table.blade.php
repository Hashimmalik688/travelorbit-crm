{{--
    Shared reference-style bookings table: used by the dashboard's Pending
    Bookings widget and the Payment Plan / Payment Awaiting report pages.
    Expects $bookings (a Collection or LengthAwarePaginator of Booking models,
    each with payment/paymentHistory/flightDetail/hotels/visas/passengers
    eager loaded) and optional $showFooter (bool, default true).
--}}
@php
    $showFooter = $showFooter ?? true;
@endphp
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="font-size:0.936rem;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Agent</th>
                <th>Caller</th>
                <th>Type</th>
                <th>Booking Date</th>
                <th>Payment Date</th>
                <th class="text-end">Cost Price</th>
                <th class="text-end">Sold Price</th>
                <th class="text-end">Margin</th>
                <th class="text-end">Received</th>
                <th class="text-end">Remaining</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bookings as $bk)
                @php
                    // Received/remaining come from the approved-payments ledger — the
                    // same source BookingShow uses for "Balance Due" — not
                    // booking_payments.amount_paid/balance_remaining, which are a
                    // stale snapshot taken at booking creation/edit time.
                    //
                    // Three distinct states, matching BookingShow's Balance Due /
                    // Fully Settled / Overpaid box — clamping a negative balance to
                    // £0.00 and labelling it "Settled" would silently hide a real
                    // overpayment (e.g. sold price reduced after payment was taken).
                    $received = $bk->totalReceived();
                    $balance = (float) $bk->total_sale_price - $received;
                    $state = abs($balance) < 0.005 ? 'settled' : ($balance > 0 ? 'due' : 'over');
                    $settled = $state === 'settled';
                    $nextDue = $settled ? null : $bk->payment?->nextDueDate();
                    $route = $bk->flightDetail
                        ? strtoupper($bk->flightDetail->departure_airport) . ' - ' . strtoupper($bk->flightDetail->arrival_airport)
                        : null;
                @endphp
                <tr wire:key="bk-row-{{ $bk->id }}" style="{{ $settled ? 'background:rgba(14,165,233,.07);' : '' }}">
                    <td>
                        <a href="{{ route('bookings.show', $bk->id) }}" class="fw-semibold">{{ $bk->booking_number }}</a>
                    </td>
                    <td>{{ $bk->user->name ?? '—' }}</td>
                    <td>
                        <div class="fw-medium">{{ $bk->booker_name }}</div>
                        @if($route)<div class="text-muted" style="font-size:0.816rem;">{{ $route }}</div>@endif
                    </td>
                    <td>
                        <span style="font-size:0.792rem;font-weight:600;background:rgba(51,46,158,.06);border:1px solid rgba(51,46,158,.12);color:#332E9E;border-radius:8px;padding:2px 9px;">{{ ucfirst($bk->booking_type ?? '—') }}</span>
                    </td>
                    <td class="text-muted">{{ $bk->created_at->format('d/m/Y') }}</td>
                    <td>
                        <x-due-date-badge :date="$nextDue" />
                    </td>
                    <td class="text-end">£{{ number_format($bk->total_cost_price, 2) }}</td>
                    <td class="text-end">£{{ number_format($bk->total_sale_price, 2) }}</td>
                    @php $bkMargin = $bk->netMargin(); @endphp
                    <td class="text-end fw-semibold {{ $bkMargin >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($bkMargin, 2) }}</td>
                    <td class="text-end">£{{ number_format($received, 2) }}</td>
                    <td class="text-end fw-semibold {{ $state === 'due' ? 'text-warning' : ($state === 'settled' ? 'text-success' : '') }}" style="{{ $state === 'over' ? 'color:#B45309;' : '' }}">£{{ number_format(abs($balance), 2) }}</td>
                    <td class="text-end">
                        @if($state === 'settled')
                            <span class="badge bg-label-info">Settled</span>
                        @elseif($state === 'over')
                            <span class="badge bg-label-warning">Overpaid</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">
                        <div class="to-empty">
                            <div class="to-empty-icon"><i class="ph ph-calendar-blank"></i></div>
                            <h5>Nothing here</h5>
                            <p>No bookings to show.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($showFooter && $bookings->isNotEmpty())
            @php
                $totalCost = $bookings->sum('total_cost_price');
                $totalSold = $bookings->sum('total_sale_price');
                $totalMargin = $bookings->sum(fn ($bk) => $bk->netMargin());
                $totalReceived = $bookings->sum(fn ($bk) => $bk->totalReceived());
                $totalRemaining = $bookings->sum(fn ($bk) => (float) $bk->total_sale_price - $bk->totalReceived());
            @endphp
            <tfoot>
                <tr class="fw-semibold" style="background:#F8FAFC;">
                    <td colspan="6" class="text-end">Totals</td>
                    <td class="text-end">£{{ number_format($totalCost, 2) }}</td>
                    <td class="text-end">£{{ number_format($totalSold, 2) }}</td>
                    <td class="text-end">£{{ number_format($totalMargin, 2) }}</td>
                    <td class="text-end">£{{ number_format($totalReceived, 2) }}</td>
                    <td class="text-end">£{{ number_format($totalRemaining, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
