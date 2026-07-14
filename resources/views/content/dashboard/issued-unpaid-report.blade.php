@extends('layouts/contentNavbarLayout')
@section('title', $title)

@section('content')
<div class="to-issued-report">
<div class="to-page-header">
    <div class="to-page-header-left">
        <h1 class="mt-1">{{ $title }}</h1>
        <p class="mb-0" style="font-size:.82rem;color:#334155;">{{ $subtitle }}</p>
    </div>
</div>

{{-- ══ Prominent totals — visible the moment the page opens ══ --}}
<div class="row g-3 mb-4">
    @php
        $statTiles = [
            ['label' => 'Total Cost',      'val' => $totals['cost'],      'accent' => 'accent-indigo', 'icon' => 'ph-tag'],
            ['label' => 'Total Sold',      'val' => $totals['sold'],      'accent' => 'accent-blue',   'icon' => 'ph-currency-gbp'],
            ['label' => 'Total Margin',    'val' => $totals['margin'],    'accent' => 'accent-green',  'icon' => 'ph-trend-up'],
            ['label' => 'Total Payments',        'val' => $totals['received'],  'accent' => 'accent-green', 'icon' => 'ph-check-circle'],
            ['label' => 'Still Balance Payment',  'val' => $totals['remaining'], 'accent' => 'accent-amber','icon' => 'ph-hourglass'],
        ];
    @endphp
    @foreach ($statTiles as $tile)
        <div class="col-6 col-lg">
            <div class="to-stat {{ $tile['accent'] }}">
                <div class="to-stat-body">
                    <div class="to-stat-label">{{ $tile['label'] }}</div>
                    <div class="to-stat-value" style="font-size:1.5rem;">£{{ number_format($tile['val'], 2) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="px-4 pt-4 pb-2 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="font-size:.87rem;">Bookings</h6>
        <span class="badge bg-label-warning">{{ $bookings->total() }} booking{{ $bookings->total() !== 1 ? 's' : '' }}</span>
    </div>
    @include('content.dashboard.partials._bookings-table', ['bookings' => $bookings, 'showFooter' => false])
    @if ($bookings->hasPages())
        <div class="card-footer">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
</div>

<style>
/* Darker, more legible text for this report — the shared bookings-table
   partial's .text-muted is too light against this page's white cards. */
.to-issued-report .text-muted { color: #334155 !important; }
</style>
@endsection
