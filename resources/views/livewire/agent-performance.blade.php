<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Agent Performance</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">Reports</a> &rsaquo; Performance
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-indigo">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Agents</div>
                    <div class="to-stat-value">{{ $totalAgents ?? 0 }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-users-three"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-green">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Bookings</div>
                    <div class="to-stat-value">{{ $totalAgentBookings ?? 0 }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-book-open"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-orange">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Revenue</div>
                    <div class="to-stat-value">£{{ number_format($totalAgentRevenue ?? 0, 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-currency-gbp"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-blue">
                <div class="to-stat-body">
                    <div class="to-stat-label">Avg / Agent</div>
                    <div class="to-stat-value">{{ $avgBookingsPerAgent ?? 0 }}</div>
                    <div class="to-stat-sub">bookings</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-chart-line"></i></div>
            </div>
        </div>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Agent</th>
                        <th>Role</th>
                        <th class="text-center">Bookings</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Margin</th>
                        <th>Top Route</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agents ?? [] as $agent)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle">{{ strtoupper(substr($agent->name, 0, 2)) }}</span>
                                    </div>
                                    <span class="fw-semibold">{{ $agent->name }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-label-primary">{{ ucfirst($agent->role) }}</span></td>
                            <td class="text-center fw-semibold">{{ $agent->bookings_count ?? 0 }}</td>
                            <td class="text-end">£{{ number_format($agent->total_revenue ?? 0, 0) }}</td>
                            <td class="text-end {{ ($agent->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">£{{ number_format($agent->total_margin ?? 0, 0) }}</td>
                            <td>{{ $agent->top_route ?? '—' }}</td>
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
</div>
