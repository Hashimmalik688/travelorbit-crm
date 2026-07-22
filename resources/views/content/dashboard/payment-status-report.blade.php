@extends('layouts/contentNavbarLayout')
@section('title', 'Plan Report — ' . $title)

@section('content')
<div class="to-issued-report">
<div class="to-page-header">
    <div class="to-page-header-left">
        <h1 class="mt-1">Plan Report</h1>
        <p class="mb-0" style="font-size:0.984rem;color:#334155;">{{ $subtitle }}</p>
    </div>
</div>

{{-- ══ Report / agent / date filters ══ --}}
<form method="GET" class="d-flex flex-wrap gap-3 align-items-end mb-3 p-3" style="background:#fff;border-radius:14px;border:1px solid rgba(51,46,158,.08);">
    <div>
        <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;">Report</label>
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:170px;">
            <option value="issued_payment_awaiting" {{ $status === 'issued_payment_awaiting' ? 'selected' : '' }}>Payment Awaiting</option>
            <option value="issued_payment_plan" {{ $status === 'issued_payment_plan' ? 'selected' : '' }}>Payment Plan</option>
        </select>
    </div>
    <div>
        <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;">Agent</label>
        <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:170px;">
            <option value="">All Agents</option>
            @foreach ($agents as $agent)
                <option value="{{ $agent->id }}" {{ (int) $userId === $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;">Booked From</label>
        <input type="date" name="from" value="{{ $dateFrom }}" class="form-control form-control-sm">
    </div>
    <div>
        <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;">Booked To</label>
        <input type="date" name="to" value="{{ $dateTo }}" class="form-control form-control-sm">
    </div>
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-sm" style="background:#332E9E;color:#fff;font-weight:600;">Apply</button>
        @if ($userId || $dateFrom || $dateTo)
            <a href="{{ route('reports.payment-status', ['status' => $status]) }}" class="btn btn-sm" style="background:rgba(51,46,158,.06);color:#332E9E;font-weight:600;">Clear</a>
        @endif
    </div>
</form>

{{-- ══ Booking type filter ══ --}}
@php
    $typeLabels = ['flight'=>'Flight','hotel'=>'Hotel','umrah'=>'Umrah','holiday'=>'Holiday','visa'=>'Visa','transfers'=>'Transfers','excursion'=>'Excursion'];
    $qs = array_filter(['status' => $status, 'user_id' => $userId, 'from' => $dateFrom, 'to' => $dateTo]);
@endphp
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('reports.payment-status', $qs) }}" style="font-size:0.816rem;font-weight:700;border-radius:20px;padding:5px 12px;text-decoration:none;{{ !$type ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
        All Types
    </a>
    @foreach ($typeLabels as $key => $label)
        <a href="{{ route('reports.payment-status', array_merge($qs, ['type' => $key])) }}" class="d-flex align-items-center gap-1" style="font-size:0.816rem;font-weight:700;border-radius:20px;padding:5px 12px;text-decoration:none;{{ $type === $key ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
            <span style="{{ $type === $key ? 'color:#fff;' : 'color:#475569;font-weight:400;' }}">{{ $label }}</span>
            <span>{{ $typeCounts->get($key, 0) }}</span>
        </a>
    @endforeach
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
                    <div class="to-stat-value" style="font-size:1.8rem;">£{{ number_format($tile['val'], 2) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="px-4 pt-4 pb-2 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="font-size:1.044rem;">Bookings</h6>
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
.to-issued-report .text-muted { color: #334155 !important; }
</style>
@endsection
