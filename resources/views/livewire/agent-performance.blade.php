<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>{{ $canViewAll ? 'Agent Performance' : 'My Performance' }}</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; Reports &rsaquo; Performance
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card animate-in" style="margin-bottom:1rem;">
        <div class="card-body d-flex flex-wrap align-items-end gap-3">
            @if ($canViewAll)
                <div>
                    <label class="form-label mb-1" style="font-size:.72rem;color:#5A6080;font-weight:600;">Agent</label>
                    <select wire:model.live="agentId" class="form-select form-select-sm" style="min-width:210px;border-radius:10px;">
                        <option value="">All agents</option>
                        @foreach ($agentUsers as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:.72rem;color:#5A6080;font-weight:600;">Month</label>
                    <input type="month" wire:model.live="month" max="{{ now()->format('Y-m') }}"
                           class="form-control form-control-sm" style="min-width:170px;border-radius:10px;">
                </div>
            @else
                <div>
                    <label class="form-label d-block mb-1" style="font-size:.72rem;color:#5A6080;font-weight:600;">Period</label>
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
            <div class="ms-auto text-muted" style="font-size:.8rem;">
                Showing <span class="fw-semibold" style="color:#332E9E;">{{ $monthLabel }}</span>
            </div>
        </div>
    </div>

    {{-- ── Summary cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-indigo">
                <div class="to-stat-body">
                    <div class="to-stat-label">{{ $singleAgent ? 'Bookings' : 'Total Bookings' }}</div>
                    <div class="to-stat-value">{{ number_format($summary['totalBookings']) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-book-open"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-orange">
                <div class="to-stat-body">
                    <div class="to-stat-label">Revenue</div>
                    <div class="to-stat-value">£{{ number_format($summary['totalRevenue'], 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-currency-gbp"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-green">
                <div class="to-stat-body">
                    <div class="to-stat-label">Margin</div>
                    <div class="to-stat-value">£{{ number_format($summary['totalMargin'], 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-trend-up"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-blue">
                <div class="to-stat-body">
                    <div class="to-stat-label">Avg Margin / Booking</div>
                    <div class="to-stat-value">£{{ number_format($summary['avgMargin'], 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-chart-line"></i></div>
            </div>
        </div>
    </div>

    @if ($singleAgent)
        {{-- ── Detailed bookings for one agent ── --}}
        <div class="card animate-in">
            <div class="card-header d-flex align-items-center gap-2">
                @if ($singleAgent->profile_photo_path)
                    <img src="{{ asset('storage/' . $singleAgent->profile_photo_path) }}"
                         alt="{{ $singleAgent->name }}" class="rounded-circle"
                         style="width:34px;height:34px;object-fit:cover;">
                @else
                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                          style="width:34px;height:34px;background:rgba(51,46,158,.08);font-size:.72rem;font-weight:700;color:#332E9E;">
                        {{ strtoupper(substr($singleAgent->name, 0, 2)) }}
                    </span>
                @endif
                <div>
                    <div class="fw-semibold">{{ $singleAgent->name }}</div>
                    <div class="text-muted" style="font-size:.7rem;">{{ \App\Models\User::ROLE_LABELS[$singleAgent->role] ?? ucfirst($singleAgent->role) }} · {{ $monthLabel }}</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Booking #</th>
                            <th>Route</th>
                            <th class="text-end">Sale</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Margin</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $b)
                            @php
                                $route = ($b->flightDetail && $b->flightDetail->departure_airport && $b->flightDetail->arrival_airport)
                                    ? $b->flightDetail->departure_airport . ' → ' . $b->flightDetail->arrival_airport
                                    : '—';
                            @endphp
                            <tr>
                                <td class="fw-semibold">
                                    <a href="{{ route('bookings.show', $b) }}" style="color:#332E9E;text-decoration:none;">#{{ $b->booking_number }}</a>
                                </td>
                                <td>{{ $route }}</td>
                                <td class="text-end">£{{ number_format($b->total_sale_price, 0) }}</td>
                                <td class="text-end">£{{ number_format($b->total_cost_price, 0) }}</td>
                                <td class="text-end fw-semibold {{ $b->total_margin >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($b->total_margin, 0) }}</td>
                                <td>{!! $b->statusBadgeHtml() !!}</td>
                                <td>{{ $b->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="to-empty">
                                        <div class="to-empty-icon"><i class="ph ph-calendar-x"></i></div>
                                        <h5>No bookings this month</h5>
                                        <p>No bookings were created in {{ $monthLabel }}.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        {{-- ── Agent leaderboard (managers, all agents) ── --}}
        <div class="card animate-in">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Role</th>
                            <th class="text-center">Bookings</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Margin</th>
                            <th class="text-end">Avg / Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaderboard as $row)
                            <tr wire:click="$set('agentId', '{{ $row['id'] }}')" style="cursor:pointer;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($row['photo'])
                                            <img src="{{ asset('storage/' . $row['photo']) }}" alt="{{ $row['name'] }}"
                                                 class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
                                        @else
                                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                                  style="width:32px;height:32px;background:rgba(51,46,158,.08);font-size:.7rem;font-weight:700;color:#332E9E;">
                                                {{ strtoupper(substr($row['name'], 0, 2)) }}
                                            </span>
                                        @endif
                                        <span class="fw-semibold">{{ $row['name'] }}</span>
                                    </div>
                                </td>
                                <td><span class="badge bg-label-primary">{{ \App\Models\User::ROLE_LABELS[$row['role']] ?? ucfirst($row['role']) }}</span></td>
                                <td class="text-center fw-semibold">{{ $row['totalBookings'] }}</td>
                                <td class="text-end">£{{ number_format($row['totalRevenue'], 0) }}</td>
                                <td class="text-end fw-semibold {{ $row['totalMargin'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($row['totalMargin'], 0) }}</td>
                                <td class="text-end">£{{ number_format($row['avgMargin'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="to-empty">
                                        <div class="to-empty-icon"><i class="ph ph-medal"></i></div>
                                        <h5>No agent data yet</h5>
                                        <p>Performance metrics appear once agents start creating bookings.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
