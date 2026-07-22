@extends('layouts/contentNavbarLayout')
@section('title', 'Departure/Arrival Report')

@section('content')
<style>
:root {
  --neo-bg: #EAEEF3;
  --neo-light: #FFFFFF;
  --neo-dark: #C4CBD6;
}
.neo-page { background: var(--neo-bg); border-radius: 28px; padding: 28px; }
.neo-raised {
  background: var(--neo-bg);
  border-radius: 20px;
  border: none;
  box-shadow: 8px 8px 16px var(--neo-dark), -8px -8px 16px var(--neo-light);
}
.neo-inset {
  background: var(--neo-bg);
  border-radius: 12px;
  border: none;
  box-shadow: inset 4px 4px 8px var(--neo-dark), inset -4px -4px 8px var(--neo-light);
}
.neo-input {
  background: var(--neo-bg) !important;
  border: none !important;
  border-radius: 12px !important;
  box-shadow: inset 3px 3px 6px var(--neo-dark), inset -3px -3px 6px var(--neo-light) !important;
  padding: 8px 12px !important;
}
.neo-input:focus { box-shadow: inset 4px 4px 8px var(--neo-dark), inset -4px -4px 8px var(--neo-light), 0 0 0 3px rgba(51,46,158,.12) !important; }
.neo-btn {
  border: none;
  border-radius: 12px;
  padding: 8px 18px;
  font-weight: 700;
  font-size: 0.876rem;
  cursor: pointer;
  transition: box-shadow .12s, transform .12s;
  background: var(--neo-bg);
  box-shadow: 5px 5px 10px var(--neo-dark), -5px -5px 10px var(--neo-light);
}
.neo-btn:active { box-shadow: inset 3px 3px 6px var(--neo-dark), inset -3px -3px 6px var(--neo-light); transform: translateY(1px); }
.neo-btn.neo-primary { color: #332E9E; }
.neo-btn.neo-ghost { color: #475569; }
.neo-chip {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.816rem; font-weight: 700; border-radius: 20px; padding: 6px 14px;
  background: var(--neo-bg); box-shadow: 4px 4px 8px var(--neo-dark), -4px -4px 8px var(--neo-light);
  text-decoration: none; color: #475569; transition: box-shadow .12s;
}
.neo-chip.active { box-shadow: inset 3px 3px 6px var(--neo-dark), inset -3px -3px 6px var(--neo-light); color: #332E9E; }
.neo-table-wrap { background: var(--neo-bg); border-radius: 20px; box-shadow: 8px 8px 16px var(--neo-dark), -8px -8px 16px var(--neo-light); overflow: hidden; }
.neo-table thead th {
  background: transparent; border: none; color: #64748B; text-transform: uppercase;
  font-size: 0.696rem; letter-spacing: .07em; font-weight: 800; padding: 16px 14px 10px;
}
.neo-table tbody td { border: none; border-top: 1px solid rgba(255,255,255,.6); padding: 12px 14px; background: transparent; }
.neo-table tbody tr:hover td { background: rgba(255,255,255,.35); }
.neo-badge {
  display: inline-block; font-size: 0.72rem; font-weight: 800; border-radius: 10px; padding: 3px 10px;
  box-shadow: 3px 3px 6px var(--neo-dark), -3px -3px 6px var(--neo-light);
}
.neo-badge.neo-inset-badge { box-shadow: inset 2px 2px 4px var(--neo-dark), inset -2px -2px 4px var(--neo-light); }
.neo-pagination .pagination { justify-content: center; }
.neo-pagination .page-link {
  background: var(--neo-bg) !important; border: none !important; color: #475569 !important;
  box-shadow: 3px 3px 6px var(--neo-dark), -3px -3px 6px var(--neo-light); border-radius: 10px !important; margin: 0 3px;
}
.neo-pagination .page-item.active .page-link {
  color: #332E9E !important; box-shadow: inset 2px 2px 4px var(--neo-dark), inset -2px -2px 4px var(--neo-light) !important;
}
</style>

<div class="neo-page">

    <div class="mb-4">
        <h1 style="font-size:1.55rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;">Departure/Arrival Report</h1>
        <p class="mb-0" style="font-size:0.936rem;color:#475569;">Every flight leg across all bookings — one row per departure, and per return if the segment is a round trip. Defaults to the last 5 days plus everything upcoming, soonest first.</p>
    </div>

    {{-- ══ Date filters ══ --}}
    <form method="GET" class="neo-raised d-flex flex-wrap gap-3 align-items-end mb-4 p-4">
        <div>
            <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:6px;">From</label>
            <input type="date" name="from" value="{{ $dateFrom }}" class="form-control form-control-sm neo-input">
        </div>
        <div>
            <label class="d-block" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:6px;">To</label>
            <input type="date" name="to" value="{{ $dateTo }}" class="form-control form-control-sm neo-input">
        </div>
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="d-flex gap-2">
            <button type="submit" class="neo-btn neo-primary">Apply</button>
            @if ($dateFrom || $dateTo)
                <a href="{{ route('reports.departure-arrival', array_filter(['type' => $type])) }}" class="neo-btn neo-ghost text-decoration-none d-inline-flex align-items-center">Clear</a>
            @endif
        </div>
    </form>

    {{-- ══ Booking type filter ══ --}}
    @php
        $typeLabels = ['flight'=>'Flight','hotel'=>'Hotel','umrah'=>'Umrah','holiday'=>'Holiday','visa'=>'Visa','transfers'=>'Transfers','excursion'=>'Excursion'];
        $qs = array_filter(['from' => $dateFrom, 'to' => $dateTo]);
        $urgencyStyle = [
            'past'     => ['bg' => 'rgba(148,163,184,.18)', 'color' => '#64748B'],
            'today'    => ['bg' => 'rgba(220,38,38,.14)',    'color' => '#DC2626'],
            'tomorrow' => ['bg' => 'rgba(234,88,12,.14)',    'color' => '#EA580C'],
            'soon'     => ['bg' => 'rgba(217,119,6,.14)',    'color' => '#D97706'],
            'week'     => ['bg' => 'rgba(14,165,233,.14)',   'color' => '#0EA5E9'],
            'later'    => ['bg' => 'rgba(22,163,74,.14)',    'color' => '#16A34A'],
            'none'     => ['bg' => 'rgba(148,163,184,.10)',  'color' => '#94A3B8'],
        ];
    @endphp
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('reports.departure-arrival', $qs) }}" class="neo-chip {{ !$type ? 'active' : '' }}">
            All Types
        </a>
        @foreach ($typeLabels as $key => $label)
            <a href="{{ route('reports.departure-arrival', array_merge($qs, ['type' => $key])) }}" class="neo-chip {{ $type === $key ? 'active' : '' }}">
                {{ $label }} <span style="opacity:.65;">{{ $typeCounts->get($key, 0) }}</span>
            </a>
        @endforeach
    </div>

    <div class="neo-table-wrap">
        <div class="px-4 pt-4 pb-2 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="font-size:1.044rem;color:#0F172A;">Legs</h6>
            <span class="neo-badge neo-inset-badge" style="color:#D97706;">{{ $rowsPage->total() }} leg{{ $rowsPage->total() !== 1 ? 's' : '' }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 neo-table" style="font-size:0.912rem;">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Date</th>
                        <th>Urgency</th>
                        <th>Leg</th>
                        <th>Route</th>
                        <th>Passenger</th>
                        <th>Airline</th>
                        <th>Agent</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rowsPage as $row)
                        @php $us = $urgencyStyle[$row['urgency']['tier']]; @endphp
                        <tr>
                            <td><a href="{{ route('bookings.show', $row['booking']->id) }}" class="fw-semibold" style="color:#332E9E;">{{ $row['booking']->booking_number }}</a></td>
                            <td style="color:#334155;">{{ $row['date'] ? $row['date']->format('d/m/Y') : '—' }}</td>
                            <td>
                                <span class="neo-badge" style="background:{{ $us['bg'] }};color:{{ $us['color'] }};box-shadow:none;">{{ $row['urgency']['label'] }}</span>
                            </td>
                            <td>
                                <span class="neo-badge neo-inset-badge" style="{{ $row['leg'] === 'Departure' ? 'color:#332E9E;' : 'color:#0EA5E9;' }}">{{ $row['leg'] }}</span>
                            </td>
                            <td style="color:#1E293B;font-weight:600;">{{ $row['route'] }}</td>
                            <td>
                                <span class="neo-badge neo-inset-badge" style="color:#7C3AED;">{{ $row['passenger_tag'] }}</span>
                                @if($row['passenger_name'])
                                    <div style="font-size:0.816rem;color:#1E293B;margin-top:4px;">{{ $row['passenger_name'] }}</div>
                                @endif
                            </td>
                            <td style="color:#334155;">{{ $row['airline'] ?: '—' }}</td>
                            <td style="color:#334155;">{{ $row['booking']->user->name ?? '—' }}</td>
                            <td>
                                <span class="neo-badge neo-inset-badge" style="color:#332E9E;">{{ ucfirst($row['booking']->booking_type ?? '—') }}</span>
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
            <div class="px-4 py-3 neo-pagination">
                {{ $rowsPage->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
