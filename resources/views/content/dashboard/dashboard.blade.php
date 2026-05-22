@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('vendor-style')
<style>
/* Dashboard-specific animations — everything else in app.css */
@keyframes countIn {
  from { opacity: 0; transform: translateY(8px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.chart-fade { animation: countIn 0.5s cubic-bezier(0.22,1,0.36,1) both; animation-delay: 0.2s; }
</style>
@endsection

@section('content')

<div class="to-page-header">
    <div class="to-page-header-left">
        <h1>Dashboard</h1>
        <div class="to-breadcrumb">{{ now()->format('l, d F Y') }}</div>
    </div>
</div>

<div class="row g-3">

    {{-- Total Bookings --}}
    <div class="col-lg-4 col-md-6 animate-in">
        <div class="to-stat accent-indigo">
            <div class="to-stat-body">
                <div class="to-stat-label">Total Bookings</div>
                <div class="to-stat-value" data-countup="{{ $totalBookings }}">{{ $totalBookings }}</div>
                <div class="to-stat-sub">this month</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-book-open"></i></div>
            <div id="chart-bookings" class="ps-3 pe-2" style="height:48px;"></div>
        </div>
    </div>

    {{-- Total Revenue --}}
    <div class="col-lg-4 col-md-6 animate-in">
        <div class="to-stat accent-green">
            <div class="to-stat-body">
                <div class="to-stat-label">Total Revenue</div>
                <div class="to-stat-value" data-countup="{{ $totalRevenue }}" data-prefix="£">£{{ number_format($totalRevenue, 0) }}</div>
                <div class="to-stat-sub">this month</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-currency-gbp"></i></div>
            <div id="chart-revenue" class="ps-3 pe-2" style="height:48px;"></div>
        </div>
    </div>

    {{-- Outstanding --}}
    <div class="col-lg-4 col-md-6 animate-in">
        <div class="to-stat accent-amber">
            <div class="to-stat-body">
                <div class="to-stat-label">Outstanding</div>
                <div class="to-stat-value" data-countup="{{ $outstandingPayments }}" data-prefix="£">£{{ number_format($outstandingPayments, 0) }}</div>
                <div class="to-stat-sub">balance remaining</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-wallet"></i></div>
        </div>
    </div>

    {{-- Overdue --}}
    <div class="col-lg-4 col-md-6 animate-in">
        <div class="to-stat accent-red">
            <div class="to-stat-body">
                <div class="to-stat-label">Overdue Payments</div>
                @if ($overduePaymentsCount === 0)
                    <div class="to-stat-value" style="font-size:1.1rem; color:#15803D;">All clear</div>
                    <div class="to-stat-sub">no overdue payments</div>
                @else
                    <div class="to-stat-value">{{ $overduePaymentsCount }}</div>
                    <div class="to-stat-sub">past due date</div>
                @endif
            </div>
            <div class="to-stat-icon"><i class="ph ph-warning-circle"></i></div>
        </div>
    </div>

    {{-- Top Agent --}}
    <div class="col-lg-4 col-md-6 animate-in">
        <div class="to-stat accent-blue">
            <div class="to-stat-body">
                <div class="to-stat-label">Top Agent</div>
                @if ($topAgent && $topAgent->user)
                    <div class="to-stat-value" style="font-size:1.15rem;">{{ $topAgent->user->name }}</div>
                    <div class="to-stat-sub">{{ $topAgent->total }} bookings this month</div>
                @else
                    <div class="to-stat-value" style="font-size:0.95rem; color:var(--text-tertiary); font-weight:600;">No data yet</div>
                @endif
            </div>
            <div class="to-stat-icon"><i class="ph ph-trophy"></i></div>
        </div>
    </div>

    {{-- Bookings by Status --}}
    <div class="col-lg-4 col-md-6 animate-in">
        <div class="to-stat accent-magenta">
            <div class="to-stat-body pb-0">
                <div class="to-stat-label">Bookings by Status</div>
            </div>
            @php $totalStatus = $pendingCount + $confirmedCount + $issuedCount; @endphp
            @if ($totalStatus === 0)
                <div class="to-empty py-3">
                    <div class="to-empty-icon" style="font-size:1.6rem;"><i class="ph ph-chart-pie"></i></div>
                    <p style="font-size:0.78rem;">No bookings yet</p>
                </div>
            @else
                <div id="chart-status" style="height:150px;" class="px-3"></div>
                <div class="d-flex justify-content-around px-3 pb-3">
                    <div class="text-center">
                        <div class="fw-bold small">{{ $pendingCount }}</div>
                        <div class="small text-muted"><span style="color:#D97706;font-size:10px;">●</span> Pending</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold small">{{ $confirmedCount }}</div>
                        <div class="small text-muted"><span style="color:#332E9E;font-size:10px;">●</span> Confirmed</div>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold small">{{ $issuedCount }}</div>
                        <div class="small text-muted"><span style="color:#15803D;font-size:10px;">●</span> Issued</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* Count-up */
  document.querySelectorAll('[data-countup]').forEach(el => {
    const target   = parseFloat(el.dataset.countup) || 0;
    const prefix   = el.dataset.prefix || '';
    const duration = 900;
    const steps    = 50;
    const interval = duration / steps;
    let current    = 0;
    const inc      = target / steps;
    const fmt = v => prefix + Math.round(v).toLocaleString('en-GB');
    el.textContent = fmt(0);
    const t = setInterval(() => {
      current = Math.min(current + inc, target);
      el.textContent = fmt(current);
      if (current >= target) clearInterval(t);
    }, interval);
  });

  /* Sparklines */
  function spark(el, data, color) {
    if (!el) return;
    new ApexCharts(el, {
      chart: {
        type: 'bar', height: 48, sparkline: { enabled: true },
        animations: { enabled: true, speed: 500 }
      },
      series: [{ data }],
      colors: [color],
      plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
      tooltip: {
        fixed: { enabled: false }, x: { show: false },
        y: { formatter: v => v.toLocaleString('en-GB') },
        marker: { show: false }
      }
    }).render();
  }

  spark(document.getElementById('chart-bookings'), @json($last7DaysBookings), '#332E9E');
  spark(document.getElementById('chart-revenue'), @json($last7DaysRevenue), '#15803D');

  /* Donut */
  const donutEl = document.getElementById('chart-status');
  if (donutEl) {
    new ApexCharts(donutEl, {
      chart: { type: 'donut', height: 150, animations: { enabled: true, speed: 600 } },
      series: [{{ $pendingCount }}, {{ $confirmedCount }}, {{ $issuedCount }}],
      labels: ['Pending', 'Confirmed', 'Issued'],
      colors: ['#D97706', '#332E9E', '#15803D'],
      dataLabels: { enabled: false },
      legend: { show: false },
      stroke: { width: 0 },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              total: {
                show: true, label: 'Total', fontSize: '11px',
                color: '#9A9588', fontWeight: 600,
                formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
              }
            }
          }
        }
      },
      tooltip: { y: { formatter: v => v + ' bookings' } }
    }).render();
  }

});
</script>
@endsection
