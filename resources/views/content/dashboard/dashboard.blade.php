@extends('layouts/contentNavbarLayout')
@section('title', 'Operations Centre')

@php
  $user    = Auth::user();
  $hour    = now()->hour;
  $greet   = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

@section('content')
<style>
@keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes countUp{ from{opacity:0;transform:scale(.88)} to{opacity:1;transform:scale(1)} }
.oc-up{ animation:fadeUp .4s ease both }
.oc-up.d1{animation-delay:.04s}.oc-up.d2{animation-delay:.08s}.oc-up.d3{animation-delay:.12s}
.oc-up.d4{animation-delay:.16s}.oc-up.d5{animation-delay:.20s}.oc-up.d6{animation-delay:.24s}
.oc-card{ border-radius:16px;padding:22px 24px;background:linear-gradient(135deg,rgba(255,255,255,0.95) 0%,rgba(255,255,255,0.85) 100%);border:1px solid rgba(255,255,255,0.5);box-shadow:0 4px 24px rgba(51,46,158,0.08),0 1px 3px rgba(0,0,0,0.04);transition:transform .2s,box-shadow .2s;position:relative;overflow:hidden;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px) }
.oc-card:hover{ transform:translateY(-2px);box-shadow:0 8px 24px rgba(51,46,158,.10) }
.oc-card::after{ content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:14px 14px 0 0 }
.oc-card.c-indigo::after{background:linear-gradient(90deg,#332E9E,#6366F1)}
.oc-card.c-green::after {background:linear-gradient(90deg,#16A34A,#4ADE80)}
.oc-card.c-amber::after {background:linear-gradient(90deg,#D97706,#FBBF24)}
.oc-card.c-rose::after  {background:linear-gradient(90deg,#DC2626,#F87171)}
.oc-card.c-sky::after   {background:linear-gradient(90deg,#0EA5E9,#38BDF8)}
.oc-card.c-violet::after{background:linear-gradient(90deg,#7C3AED,#A78BFA)}
.oc-val{ font-size:2rem;font-weight:800;letter-spacing:-.03em;color:#0F172A;line-height:1 }
.oc-lbl{ font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;margin-bottom:4px }
.oc-sub{ font-size:0.852rem;color:#475569;margin-top:4px }
/* Agent table */
.ag-row{ display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;background:#fff;border:1px solid rgba(51,46,158,.06);margin-bottom:7px;transition:background .12s }
.ag-row:hover{ background:#F8FAFF;border-color:rgba(51,46,158,.12) }
</style>

{{-- ══ GLASSMORPHISM HERO ══ --}}
<div class="oc-up d1 mb-4" style="background:linear-gradient(135deg,rgba(51,46,158,0.85) 0%,rgba(124,58,237,0.75) 50%,rgba(99,102,241,0.8) 100%);border-radius:20px;padding:28px 32px;position:relative;overflow:hidden;backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.18);box-shadow:0 8px 32px rgba(51,46,158,0.25),inset 0 1px 0 rgba(255,255,255,0.15);">
  {{-- Decorative orbs --}}
  <div style="position:absolute;right:-60px;top:-60px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.12) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;left:-40px;bottom:-40px;width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,rgba(255,107,53,0.15) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;right:30%;bottom:-20px;width:80px;height:80px;border-radius:50%;background:radial-gradient(circle,rgba(22,163,74,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
    <div>
      <div style="font-size:0.744rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
      <h2 style="color:#fff;font-size:1.8rem;font-weight:800;letter-spacing:-.03em;margin:0 0 6px;">{{ $greet }}, {{ explode(' ', $user->name)[0] }} 👋</h2>
      <p style="color:rgba(255,255,255,.55);font-size:0.984rem;margin:0;">Operations Centre <span style="opacity:.5;">·</span> Full system overview</p>
    </div>
  </div>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="row g-3 mb-4">
  @php
    $kpis = [
      ['label'=>'Fresh Margin This Month', 'val'=>$freshMarginThisMonth, 'sub'=>now()->format('F Y'),  'color'=>'c-indigo','icon'=>'ph ph-trend-up',        'ibg'=>'rgba(51,46,158,.10)',  'ic'=>'#332E9E', 'fmt'=>'gbp'],
      ['label'=>'Outstanding Balance', 'val'=>$outstandingPayments,   'sub'=>'across all bookings',   'color'=>'c-amber', 'icon'=>'ph ph-clock-countdown',   'ibg'=>'rgba(217,119,6,.10)',  'ic'=>'#D97706', 'fmt'=>'gbp'],
    ];
  @endphp
  @foreach ($kpis as $si => $k)
    <div class="col-md-6 oc-up d{{ $si + 1 }}">
      <div class="oc-card {{ $k['color'] }}">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div style="width:40px;height:40px;border-radius:11px;background:{{ $k['ibg'] }};display:flex;align-items:center;justify-content:center;">
            <i class="{{ $k['icon'] }}" style="font-size:1.32rem;color:{{ $k['ic'] }};"></i>
          </div>
        </div>
        <div class="oc-lbl">{{ $k['label'] }}</div>
        <div class="oc-val" data-target="{{ $k['val'] }}" data-fmt="{{ $k['fmt'] }}">
          {{ $k['fmt'] === 'gbp' ? '£'.number_format($k['val'],0) : $k['val'] }}
        </div>
        <div class="oc-sub">{{ $k['sub'] }}</div>
      </div>
    </div>
  @endforeach
</div>

{{-- ══ AGENT LEADERBOARD ══ --}}
<div class="row g-3">

  <div class="col-12 oc-up d3">
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);overflow:hidden;">
      <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid rgba(51,46,158,.06);">
        <h6 class="fw-bold mb-0" style="font-size:1.02rem;color:#0F172A;">Agent Leaderboard</h6>
        <span style="font-size:0.84rem;color:#475569;">{{ now()->format('F Y') }}</span>
      </div>
      <div class="px-4 py-3">
        @forelse ($allAgents->sortByDesc('month_bookings')->take(8) as $idx => $ag)
          @php
            $initials = strtoupper(substr($ag->name,0,1).(strpos($ag->name,' ')!==false?substr($ag->name,strpos($ag->name,' ')+1,1):''));
            $pct = $allAgents->max('month_bookings') > 0 ? round(($ag->month_bookings/$allAgents->max('month_bookings'))*100) : 0;
            $colors = ['#332E9E','#D83F87','#D97706','#16A34A','#0EA5E9','#7C3AED','#DC2626','#F59E0B'];
            $c = $colors[$idx % count($colors)];
          @endphp
          <div class="ag-row">
            <div style="width:34px;height:34px;border-radius:50%;background:{{ $c }}18;color:{{ $c }};display:flex;align-items:center;justify-content:center;font-size:0.816rem;font-weight:800;flex-shrink:0;">{{ $initials }}</div>
            <div class="flex-grow-1 min-width-0">
              <div class="fw-semibold" style="font-size:0.936rem;color:#1E293B;">{{ $ag->name }}</div>
              <div style="height:4px;background:rgba(51,46,158,.07);border-radius:20px;margin-top:4px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:{{ $c }};border-radius:20px;transition:width .8s;"></div>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold" style="font-size:1.056rem;color:{{ $c }};">{{ $ag->month_bookings }}</div>
              <div style="font-size:0.744rem;color:#475569;">bookings</div>
            </div>
          </div>
        @empty
          <div class="text-center py-4" style="color:#475569;font-size:0.9rem;">No agent data this month.</div>
        @endforelse
      </div>
    </div>
  </div>

</div>

{{-- ══ RECENT BOOKINGS ══ --}}
<div class="row g-3 mt-1">
  <div class="col-12 oc-up d4">
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);overflow:hidden;">
      <div class="px-4 pt-4 pb-3" style="border-bottom:1px solid rgba(51,46,158,.06);">
        <h6 class="fw-bold mb-0" style="font-size:1.02rem;color:#0F172A;">Recent Bookings</h6>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.912rem;">
          <thead>
            <tr>
              <th>Booking #</th>
              <th>Agent</th>
              <th>Route</th>
              <th class="text-end">Margin</th>
              <th>Type</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentBookings as $rb)
              @php
                $rbRoute = ($rb->flightDetail && $rb->flightDetail->departure_airport && $rb->flightDetail->arrival_airport)
                  ? strtoupper($rb->flightDetail->departure_airport) . ' - ' . strtoupper($rb->flightDetail->arrival_airport)
                  : '—';
                $rbMargin = $rb->netMargin();
              @endphp
              <tr>
                <td><a href="{{ route('bookings.show', $rb->id) }}" class="fw-semibold">{{ $rb->booking_number }}</a></td>
                <td>{{ $rb->user->name ?? '—' }}</td>
                <td>{{ $rbRoute }}</td>
                <td class="text-end fw-semibold {{ $rbMargin >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($rbMargin, 2) }}</td>
                <td>
                  <span style="font-size:0.792rem;font-weight:600;background:rgba(51,46,158,.06);border:1px solid rgba(51,46,158,.12);color:#332E9E;border-radius:8px;padding:2px 9px;">{{ ucfirst($rb->booking_type ?? '—') }}</span>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center py-4" style="color:#475569;">No bookings yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ══ AGENTS TODAY ══ --}}
@if ($agentsToday->isNotEmpty())
  <div class="oc-up d5 mt-4">
    @include('content.dashboard.partials._agents-today', ['agents' => $agentsToday])
  </div>
@endif

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // Count-up
  document.querySelectorAll('[data-target]').forEach(el => {
    const t = parseFloat(el.dataset.target) || 0;
    const gbp = el.dataset.fmt === 'gbp';
    if (!t) return;
    let s = 0, dur = 900;
    const run = ts => {
      if (!s) s = ts;
      const p = Math.min((ts-s)/dur, 1), e = 1 - Math.pow(1-p, 3);
      el.textContent = gbp ? '£' + Math.floor(e*t).toLocaleString('en-GB') : Math.floor(e*t).toLocaleString('en-GB');
      if (p < 1) requestAnimationFrame(run); else el.textContent = gbp ? '£'+t.toLocaleString('en-GB') : t.toLocaleString('en-GB');
    };
    requestAnimationFrame(run);
  });

});
</script>
@endsection
