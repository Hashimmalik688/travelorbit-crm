<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Sales Report</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">Reports</a> &rsaquo; Sales
            </div>
        </div>
        <div class="to-page-header-right">
            <button class="btn btn-outline-primary btn-sm" wire:click="exportCsv">
                <i class="ph ph-download-simple me-1"></i> Export CSV
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="to-filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" wire:model.live="dateFrom">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" wire:model.live="dateTo">
            </div>
            <div class="col-md-3">
                <label class="form-label">Booking Type</label>
                <select class="form-select" wire:model.live="bookingType">
                    <option value="">All Types</option>
                    <option value="flight">Flight</option>
                    <option value="flight_hotel">Flight + Hotel</option>
                    <option value="umrah">Umrah</option>
                    <option value="group">Group</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Agent</label>
                <select class="form-select" wire:model.live="agentId">
                    <option value="">All Agents</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-indigo">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Bookings</div>
                    <div class="to-stat-value">{{ $summary['totalBookings'] }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-book-open"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-green">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Revenue</div>
                    <div class="to-stat-value">£{{ number_format($summary['totalRevenue'], 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-currency-gbp"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-red">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Cost</div>
                    <div class="to-stat-value">£{{ number_format($summary['totalCost'], 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-receipt-x"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-blue">
                <div class="to-stat-body">
                    <div class="to-stat-label">Total Margin</div>
                    <div class="to-stat-value">£{{ number_format($summary['totalMargin'], 0) }}</div>
                    <div class="to-stat-sub">Avg £{{ number_format($summary['avgMargin'] ?? 0, 0) }} / booking</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-trend-up"></i></div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card animate-in mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Monthly Revenue &amp; Margin</h5>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="120"></canvas>
        </div>
    </div>

    {{-- Table --}}
    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Booker</th>
                        <th>Route</th>
                        <th>Type</th>
                        <th class="text-end">Sale Price</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">Margin</th>
                        <th>Status</th>
                        <th>Agent</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        <tr>
                            <td>
                                <a href="{{ route('bookings.show', $booking) }}" class="fw-semibold">
                                    #{{ $booking->booking_number }}
                                </a>
                            </td>
                            <td>{{ $booking->booker_name }}</td>
                            <td>{{ $booking->flightDetail ? ($booking->flightDetail->departure_airport . ' → ' . $booking->flightDetail->arrival_airport) : '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $booking->booking_type)) }}</td>
                            <td class="text-end">£{{ number_format($booking->total_sale_price, 0) }}</td>
                            <td class="text-end">£{{ number_format($booking->total_cost_price, 0) }}</td>
                            <td class="text-end {{ ($booking->total_margin ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                £{{ number_format($booking->total_margin ?? 0, 0) }}
                            </td>
                            <td>
                                <span class="badge bg-label-{{ match($booking->booking_status) {'confirmed' => 'primary', 'issued' => 'success', 'pending' => 'warning', 'cancelled' => 'danger', default => 'secondary'} }}">
                                    {{ ucfirst(str_replace('_', ' ', $booking->booking_status)) }}
                                </span>
                            </td>
                            <td>{{ $booking->user->name ?? '—' }}</td>
                            <td>{{ $booking->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-chart-bar"></i></div>
                                    <h5>No bookings found</h5>
                                    <p>Try adjusting your date range or filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Revenue (£)',
                        data: @json($chartData['revenue']),
                        backgroundColor: '#332E9E',
                        borderColor: '#332E9E',
                        borderWidth: 1,
                        borderRadius: 6,
                    }, {
                        label: 'Margin (£)',
                        data: @json($chartData['margin']),
                        backgroundColor: '#FF6B35',
                        borderColor: '#FF6B35',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } },
                    scales: {
                        y: { beginAtZero: true, ticks: { callback: v => '£' + v }, grid: { color: '#E8E2D8' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
</div>
