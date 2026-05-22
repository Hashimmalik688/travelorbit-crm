@extends('layouts/contentNavbarLayout')

@section('title', 'My Dashboard')

@section('content')

<div class="to-page-header">
    <div class="to-page-header-left">
        <h1>My Dashboard</h1>
        <div class="to-breadcrumb">{{ now()->format('l, d F Y') }}</div>
    </div>
    <div class="to-page-header-right">
        <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
            <i class="ph ph-plus-circle me-1"></i> New Booking
        </a>
    </div>
</div>

{{-- Welcome card --}}
<div class="card mb-3 border-0" style="background: linear-gradient(135deg, #332E9E 0%, #231E7A 100%); color: #fff; border-radius: 14px;">
    <div class="card-body d-flex justify-content-between align-items-center py-3 px-4">
        <div>
            <h5 class="mb-1" style="color: #fff; font-size: 1.05rem;">Welcome back, {{ Auth::user()->name }}</h5>
            <p class="mb-0" style="color: rgba(255,255,255,0.65); font-size: 0.82rem;">
                You've created <strong style="color: #FF6B35;">{{ $myTotalBookings }}</strong> booking{{ $myTotalBookings !== 1 ? 's' : '' }} this month
                @if ($myRecentCount > 0)
                    &mdash; {{ $myRecentCount }} in the last 7 days
                @endif
            </p>
        </div>
        <div style="font-size: 2.4rem; opacity: 0.25;"><i class="ph ph-hand-waving"></i></div>
    </div>
</div>

{{-- Agent stat cards — 4 col --}}
<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="to-stat accent-indigo">
            <div class="to-stat-body">
                <div class="to-stat-label">My Bookings</div>
                <div class="to-stat-value">{{ $myTotalBookings }}</div>
                <div class="to-stat-sub">this month</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-book-open"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="to-stat accent-green">
            <div class="to-stat-body">
                <div class="to-stat-label">My Revenue</div>
                <div class="to-stat-value">£{{ number_format($myRevenue, 0) }}</div>
                <div class="to-stat-sub">this month</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-currency-gbp"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="to-stat accent-amber">
            <div class="to-stat-body">
                <div class="to-stat-label">My Outstanding</div>
                <div class="to-stat-value">£{{ number_format($myOutstanding, 0) }}</div>
                <div class="to-stat-sub">balance remaining</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-wallet"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 animate-in">
        <div class="to-stat accent-orange">
            <div class="to-stat-body">
                <div class="to-stat-label">Pending Issuance</div>
                <div class="to-stat-value">{{ $myPendingIssuance }}</div>
                <div class="to-stat-sub">waiting to issue</div>
            </div>
            <div class="to-stat-icon"><i class="ph ph-ticket"></i></div>
        </div>
    </div>
</div>

{{-- Performance vs. targets + recent bookings --}}
<div class="row g-3">
    {{-- Performance bar --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header py-2 d-flex align-items-center">
                <i class="ph ph-target me-2" style="color: var(--orange); font-size: 1.1rem;"></i>
                <h6 class="card-title mb-0 small">Performance vs. Monthly Target</h6>
            </div>
            <div class="card-body pt-2 pb-3">
                @php $target = $monthlyTarget ?? 20; $pct = $target > 0 ? min(100, round(($myTotalBookings / $target) * 100)) : 0; @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold">{{ $myTotalBookings }} / {{ $target }} bookings</span>
                    <span class="fw-bold" style="color: {{ $pct >= 100 ? '#15803D' : ($pct >= 50 ? '#D97706' : '#DC2626') }};">{{ $pct }}%</span>
                </div>
                <div class="progress" style="height: 8px; border-radius: 6px; background: var(--surface-hover);">
                    <div class="progress-bar" style="width: {{ $pct }}%; border-radius: 6px;
                        background: {{ $pct >= 100 ? 'linear-gradient(90deg, #15803D, #22C55E)' : ($pct >= 50 ? 'linear-gradient(90deg, #D97706, #F59E0B)' : 'linear-gradient(90deg, #DC2626, #EF4444)') }};"></div>
                </div>
                @if ($pct >= 100)
                    <div class="small text-success mt-2 fw-semibold"><i class="ph ph-check-circle me-1"></i> Target reached! Great work.</div>
                @elseif ($pct >= 75)
                    <div class="small text-warning mt-2 fw-semibold"><i class="ph ph-warning-circle me-1"></i> {{ $target - $myTotalBookings }} more to hit target.</div>
                @else
                    <div class="small text-danger mt-2 fw-semibold"><i class="ph ph-arrow-circle-up me-1"></i> {{ $target - $myTotalBookings }} bookings needed.</div>
                @endif

                {{-- Mini status breakdown --}}
                <div class="d-flex gap-3 mt-3 pt-3 border-top">
                    <div class="text-center flex-fill">
                        <div class="fw-bold small">{{ $myPendingCount }}</div>
                        <div class="small text-muted">Pending</div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="fw-bold small">{{ $myConfirmedCount }}</div>
                        <div class="small text-muted">Confirmed</div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="fw-bold small">{{ $myCancelledCount }}</div>
                        <div class="small text-muted">Cancelled</div>
                    </div>
                    <div class="text-center flex-fill">
                        <div class="fw-bold small">£{{ number_format($myAvgRevenue, 0) }}</div>
                        <div class="small text-muted">Avg. Value</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent bookings --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 small">My Recent Bookings</h6>
                <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-primary" style="font-size:0.72rem;">View All</a>
            </div>
            <div class="card-body pt-0 pb-1">
                @forelse ($myRecentBookings as $booking)
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="font-size:0.82rem;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-primary">#{{ $booking->booking_number }}</span>
                            <span class="text-muted">{{ $booking->booker_name }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-semibold">£{{ number_format($booking->total_sale_price, 0) }}</span>
                            <span class="badge bg-label-{{ $booking->booking_status === 'confirmed' ? 'success' : ($booking->booking_status === 'cancelled' ? 'danger' : 'warning') }} rounded-pill" style="font-size:0.62rem;">
                                {{ ucfirst($booking->booking_status) }}
                            </span>
                            <span class="text-muted" style="font-size:0.72rem;">{{ $booking->created_at->format('d M') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="ph ph-book-open d-block mb-2" style="font-size:1.8rem;"></i>
                        No bookings yet. <a href="{{ route('bookings.create') }}">Create your first booking</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Quick actions --}}
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card" style="background: var(--surface-hover); border-style: dashed; border-color: var(--border-medium); border-radius: 14px;">
            <div class="card-body py-2 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold" style="color: var(--text-secondary);">Quick Actions</span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('bookings.create') }}" class="btn btn-sm btn-primary"><i class="ph ph-plus-circle me-1"></i> New Booking</a>
                        <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-primary"><i class="ph ph-list me-1"></i> My Bookings</a>
                        <a href="{{ route('customers') }}" class="btn btn-sm btn-outline-secondary"><i class="ph ph-users me-1"></i> Customers</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
