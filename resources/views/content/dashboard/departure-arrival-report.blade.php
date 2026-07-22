@extends('layouts/contentNavbarLayout')
@section('title', 'Departure/Arrival Report')

@section('content')
<div class="to-page-header">
    <div class="to-page-header-left">
        <h1 class="mt-1">Departure/Arrival Report</h1>
        <p class="mb-0" style="font-size:0.984rem;color:#334155;">Every flight leg across all bookings — one row per departure, and per return if the segment is a round trip.</p>
    </div>
</div>

{{-- ══ Date filters ══ --}}
<form method="GET" class="d-flex flex-wrap gap-3 align-items-end mb-3 p-3" style="background:#fff;border-radius:14px;border:1px solid rgba(51,46,158,.08);">
    <div>
        <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;">From</label>
        <input type="date" name="from" value="{{ $dateFrom }}" class="form-control form-control-sm">
    </div>
    <div>
        <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;">To</label>
        <input type="date" name="to" value="{{ $dateTo }}" class="form-control form-control-sm">
    </div>
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-sm" style="background:#332E9E;color:#fff;font-weight:600;">Apply</button>
        @if ($dateFrom || $dateTo)
            <a href="{{ route('reports.departure-arrival', array_filter(['type' => $type])) }}" class="btn btn-sm" style="background:rgba(51,46,158,.06);color:#332E9E;font-weight:600;">Clear</a>
        @endif
    </div>
</form>

{{-- ══ Booking type filter ══ --}}
@php
    $typeLabels = ['flight'=>'Flight','hotel'=>'Hotel','umrah'=>'Umrah','holiday'=>'Holiday','visa'=>'Visa','transfers'=>'Transfers','excursion'=>'Excursion'];
    $qs = array_filter(['from' => $dateFrom, 'to' => $dateTo]);
@endphp
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('reports.departure-arrival', $qs) }}" style="font-size:0.816rem;font-weight:700;border-radius:20px;padding:5px 12px;text-decoration:none;{{ !$type ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
        All Types
    </a>
    @foreach ($typeLabels as $key => $label)
        <a href="{{ route('reports.departure-arrival', array_merge($qs, ['type' => $key])) }}" class="d-flex align-items-center gap-1" style="font-size:0.816rem;font-weight:700;border-radius:20px;padding:5px 12px;text-decoration:none;{{ $type === $key ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
            <span style="{{ $type === $key ? 'color:#fff;' : 'color:#475569;font-weight:400;' }}">{{ $label }}</span>
            <span>{{ $typeCounts->get($key, 0) }}</span>
        </a>
    @endforeach
</div>

<div class="card">
    <div class="px-4 pt-4 pb-2 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0" style="font-size:1.044rem;">Legs</h6>
        <span class="badge bg-label-warning">{{ $rowsPage->total() }} leg{{ $rowsPage->total() !== 1 ? 's' : '' }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.936rem;">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Date</th>
                    <th>Leg</th>
                    <th>Route</th>
                    <th>PNR</th>
                    <th>Airline</th>
                    <th class="text-end">Pax</th>
                    <th>Agent</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rowsPage as $row)
                    <tr>
                        <td><a href="{{ route('bookings.show', $row['booking']->id) }}" class="fw-semibold">{{ $row['booking']->booking_number }}</a></td>
                        <td>{{ $row['date'] ? $row['date']->format('d/m/Y') : '—' }}</td>
                        <td>
                            <span style="font-size:0.744rem;font-weight:700;padding:2px 8px;border-radius:10px;{{ $row['leg'] === 'Departure' ? 'color:#332E9E;background:rgba(51,46,158,.08);' : 'color:#0EA5E9;background:rgba(14,165,233,.08);' }}">{{ $row['leg'] }}</span>
                        </td>
                        <td>{{ $row['route'] }}</td>
                        <td>{{ $row['pnr'] ?: '—' }}</td>
                        <td>{{ $row['airline'] ?: '—' }}</td>
                        <td class="text-end">{{ $row['passengers'] }}</td>
                        <td>{{ $row['booking']->user->name ?? '—' }}</td>
                        <td>
                            <span style="font-size:0.792rem;font-weight:600;background:rgba(51,46,158,.06);border:1px solid rgba(51,46,158,.12);color:#332E9E;border-radius:8px;padding:2px 9px;">{{ ucfirst($row['booking']->booking_type ?? '—') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="to-empty">
                                <div class="to-empty-icon"><i class="ph ph-airplane-tilt"></i></div>
                                <h5>Nothing here</h5>
                                <p>No flight legs match these filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($rowsPage->hasPages())
        <div class="card-footer">
            {{ $rowsPage->links() }}
        </div>
    @endif
</div>
@endsection
