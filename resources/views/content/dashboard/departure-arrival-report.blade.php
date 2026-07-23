@extends('layouts/contentNavbarLayout')
@section('title', 'Departure/Arrival Report')

@section('content')
<style>
/* Class names are historical (neo-*); every surface here is now FLAT, matching
   the single white card language in layouts/sections/design-v5.blade.php.
   Geometry (padding, radius, layout) is preserved — only fill, border and
   shadow changed, so nothing reflows. */
.neo-page { background: transparent; padding: 0; }
.neo-raised {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid var(--to-border);
  box-shadow: 0 1px 2px rgba(15,23,42,0.04);
}
.neo-inset {
  background: var(--to-page);
  border-radius: 10px;
  border: 1px solid var(--to-border);
  box-shadow: none;
}
/* #CBD5E1 keeps the field boundary at 3:1 (WCAG 1.4.11); #E2E8F0 on white
   is only 1.23:1 and these inputs sit on a white card. */
.neo-input {
  background: #FFFFFF !important;
  border: 1px solid #CBD5E1 !important;
  border-radius: 10px !important;
  box-shadow: none !important;
  padding: 8px 12px !important;
}
.neo-input:focus { border-color: var(--to-indigo) !important; box-shadow: 0 0 0 3px rgba(79,70,229,.12) !important; }
.neo-btn {
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 8px 18px;
  font-weight: 700;
  font-size: 0.876rem;
  cursor: pointer;
  transition: background-color .12s, border-color .12s;
  background: var(--to-indigo);
  color: #fff;
  box-shadow: none;
}
.neo-btn:hover { background: #4338CA; color: #fff; }
.neo-btn.neo-primary { background: var(--to-indigo); color: #fff; }
.neo-btn.neo-ghost { background: var(--to-subtle); border-color: var(--to-border); color: var(--to-slate); }
.neo-btn.neo-ghost:hover { background: #E8EDF3; color: var(--to-slate); }
.neo-chip {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.816rem; font-weight: 700; border-radius: 20px; padding: 6px 14px;
  background: var(--to-subtle); border: 1px solid var(--to-border); box-shadow: none;
  text-decoration: none; color: #475569; transition: background-color .12s, border-color .12s;
}
.neo-chip:hover { background: #E8EDF3; color: var(--to-slate); }
.neo-chip.active { background: var(--to-indigo); border-color: var(--to-indigo); color: #fff; box-shadow: none; }
.neo-table-wrap { background: #FFFFFF; border: 1px solid var(--to-border); border-radius: 14px; box-shadow: 0 1px 2px rgba(15,23,42,0.04); overflow: hidden; }
.neo-table thead th {
  background: transparent; border: none; border-bottom: 2px solid #CBD5E1; color: #64748B; text-transform: uppercase;
  font-size: 0.696rem; letter-spacing: .07em; font-weight: 800; padding: 14px 14px 10px;
}
.neo-table tbody td { border: none; border-bottom: 1px solid var(--to-border); padding: 12px 14px; background: transparent; }
.neo-table tbody tr:last-child td { border-bottom: none; }
.neo-table tbody tr:hover td { background: rgba(79,70,229,0.045); }
.neo-badge {
  display: inline-block; font-size: 0.72rem; font-weight: 800; border-radius: 8px; padding: 3px 10px;
  background: var(--to-subtle); border: 1px solid var(--to-border); box-shadow: none;
}
.neo-badge.neo-inset-badge { background: var(--to-subtle); border: 1px solid var(--to-border); box-shadow: none; }
.neo-pagination .pagination { justify-content: center; }
.neo-pagination .page-link {
  background: #FFFFFF !important; border: 1px solid var(--to-border) !important; color: var(--to-slate) !important;
  box-shadow: none; border-radius: 10px !important; margin: 0 3px;
}
.neo-pagination .page-item.active .page-link {
  background: var(--to-indigo) !important; border-color: var(--to-indigo) !important; color: #fff !important; box-shadow: none !important;
}
</style>

<div class="neo-page">

    <div class="mb-4">
        <h1 style="font-size:1.55rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;">Departure/Arrival Report</h1>
        <p class="mb-0" style="font-size:0.936rem;color:#475569;">Every flight leg across all bookings — one row per departure, and per return if the segment is a round trip. Defaults to the last 5 days plus the next 7, soonest first.</p>
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
