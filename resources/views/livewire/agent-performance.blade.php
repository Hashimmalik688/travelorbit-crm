<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>{{ $canViewAll ? 'Agent Performance' : 'My Performance' }}</h1>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card animate-in" style="margin-bottom:1rem;">
        <div class="card-body d-flex flex-wrap align-items-end gap-3">
            @if ($canViewAll)
                <div>
                    <label class="form-label mb-1" style="font-size:0.864rem;color:#5A6080;font-weight:600;">Agent</label>
                    <select wire:model.live="agentId" class="form-select form-select-sm" style="min-width:210px;border-radius:10px;">
                        <option value="">All agents</option>
                        @foreach ($agentUsers as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:0.864rem;color:#5A6080;font-weight:600;">Month</label>
                    <input type="month" wire:model.live="month" max="{{ now()->format('Y-m') }}"
                           class="form-control form-control-sm" style="min-width:170px;border-radius:10px;">
                </div>
            @else
                <div>
                    <label class="form-label d-block mb-1" style="font-size:0.864rem;color:#5A6080;font-weight:600;">Period</label>
                    <div class="btn-group" role="group">
                        <button type="button" wire:click="setMonth('current')"
                            class="btn btn-sm {{ $month === now()->format('Y-m') ? 'btn-orange' : 'btn-outline-secondary' }}">
                            This Month
                        </button>
                        <button type="button" wire:click="setMonth('last')"
                            class="btn btn-sm {{ $month === now()->subMonthNoOverflow()->format('Y-m') ? 'btn-orange' : 'btn-outline-secondary' }}">
                            Last Month
                        </button>
                    </div>
                </div>
            @endif
            <div class="ms-auto text-muted" style="font-size:0.96rem;">
                Showing <span class="fw-semibold" style="color:#332E9E;">{{ $monthLabel }}</span>
            </div>
        </div>
    </div>

    {{-- ── Booking-level performance table ── --}}
    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Route</th>
                        <th>Customer</th>
                        @if ($showAgent)<th>Agent</th>@endif
                        <th class="text-end">Cost</th>
                        <th class="text-end">Sold</th>
                        <th class="text-end">Margin (w/ CC)</th>
                        <th class="text-end">Margin (no CC)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        <tr>
                            <td style="white-space:nowrap;">{{ $r['date']->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('bookings.show', $r['booking']) }}" style="color:#332E9E;text-decoration:none;font-weight:600;white-space:nowrap;">
                                    {{ $r['route'] ?? '#'.$r['booking']->booking_number }}
                                </a>
                            </td>
                            <td>{{ $r['customer'] }}</td>
                            @if ($showAgent)<td>{{ $r['agent'] }}</td>@endif
                            <td class="text-end">£{{ number_format($r['cost'], 2) }}</td>
                            <td class="text-end">£{{ number_format($r['sold'], 2) }}</td>
                            <td class="text-end fw-semibold {{ $r['marginCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($r['marginCc'], 2) }}</td>
                            <td class="text-end fw-semibold {{ $r['marginNoCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($r['marginNoCc'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showAgent ? 8 : 7 }}">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-calendar-x"></i></div>
                                    <h5>No bookings this month</h5>
                                    <p>No bookings were issued in {{ $monthLabel }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr style="border-top:2px solid rgba(51,46,158,.15);background:rgba(51,46,158,.03);font-weight:700;">
                            <td colspan="{{ $showAgent ? 4 : 3 }}" style="color:#0F172A;">TOTAL · {{ $totals['count'] }} booking{{ $totals['count'] === 1 ? '' : 's' }}</td>
                            <td class="text-end">£{{ number_format($totals['cost'], 2) }}</td>
                            <td class="text-end">£{{ number_format($totals['sold'], 2) }}</td>
                            <td class="text-end {{ $totals['marginCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginCc'], 2) }}</td>
                            <td class="text-end {{ $totals['marginNoCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginNoCc'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- ── Summary ── --}}
    @if ($rows->isNotEmpty())
        <div class="card animate-in mt-3" style="max-width:480px;">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                    <span class="text-muted">Total Cost</span>
                    <span class="fw-semibold">£{{ number_format($totals['cost'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                    <span class="text-muted">Total Sold</span>
                    <span class="fw-semibold">£{{ number_format($totals['sold'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                    <span class="text-muted">Total Margin (no CC)</span>
                    <span class="fw-semibold {{ $totals['marginNoCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginNoCc'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                    <span class="text-muted">Total Margin (w/ CC)</span>
                    <span class="fw-semibold {{ $totals['marginCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginCc'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                    <span class="text-muted">Total Payments</span>
                    <span class="fw-semibold">£{{ number_format($totals['paid'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                    <span class="text-muted">Still Balance Payment</span>
                    <span class="fw-semibold">£{{ number_format($totals['balance'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2" title="The manager decides which bookings' margin is shared — coming soon.">
                    <span class="text-muted">Shared Amount</span>
                    <span class="fw-semibold text-muted">—</span>
                </div>
            </div>
        </div>
    @endif
</div>
