@extends('layouts/contentNavbarLayout')
@section('title', 'My Dashboard')

@php
  $user      = Auth::user();
  $hour      = now()->hour;
  $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
  $today     = now()->day;
@endphp

@section('content')
<style>
@keyframes fadeUp   { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes countUp  { from{opacity:0;transform:scale(0.88)} to{opacity:1;transform:scale(1)} }
@keyframes pulse    { 0%,100%{box-shadow:0 0 0 0 rgba(22,163,74,.4)} 50%{box-shadow:0 0 0 5px rgba(22,163,74,0)} }

.ad-up      { animation: fadeUp .4s ease both; }
.ad-up.d1   { animation-delay:.04s }
.ad-up.d2   { animation-delay:.09s }
.ad-up.d3   { animation-delay:.14s }
.ad-up.d4   { animation-delay:.19s }
.ad-up.d5   { animation-delay:.24s }
.ad-count   { animation: countUp .6s cubic-bezier(.22,1,.36,1) both; animation-delay:.35s }

/* Hero — a solid gradient banner. Flattened to match the rest of the CRM:
   no backdrop blur, no translucency, no floating orbs, no animated flow. */
.ad-hero {
  background:linear-gradient(120deg,#332E9E 0%,#6D46C7 55%,#5B5BE6 100%);
  animation: fadeUp .4s ease both;
  border-radius:16px;padding:22px 30px 52px;position:relative;overflow:hidden;
  box-shadow:0 1px 2px rgba(15,23,42,0.06);
}
.ad-hero-plane { position:absolute;right:34px;top:50%;transform:translateY(-50%) rotate(8deg);font-size:5rem;color:rgba(255,255,255,.12);pointer-events:none; }

/* Money stats — flat white cards, floated up over the hero */
.ad-money-row { margin-top:-32px;position:relative;z-index:2; }
.ad-money { border-radius:14px;padding:22px 24px;background:#FFFFFF;border:1px solid var(--to-border);box-shadow:0 1px 2px rgba(15,23,42,0.04);position:relative;overflow:hidden;transition:box-shadow .12s ease;height:100%;display:flex;flex-direction:column; }
.ad-money:hover { box-shadow:0 2px 6px rgba(15,23,42,.07) }
.ad-money::after { content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:14px 14px 0 0 }
.ad-money.fresh::after  { background:#332E9E }
.ad-money.issued::after { background:#16A34A }
.ad-money.pending::after{ background:#D97706 }
.ad-money.alltime::after{ background:#64748B }

/* Calendar styles */
.ad-cal-day {
  aspect-ratio:1;display:flex;align-items:center;justify-content:center;
  border-radius:5px;font-size:0.66rem;font-weight:500;color:#475569;cursor:default;
  transition:all .15s;position:relative;
}
/* A sale that day — soft green tint + pulsing dot. */
.ad-cal-day.sale { background:rgba(22,163,74,.14);color:#16A34A;font-weight:700 }
.ad-cal-day.sale::after { content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:4px;height:4px;border-radius:50%;background:#16A34A;animation:pulse 2s infinite }
/* Today — a BLUE RING, never a fill. A solid fill used to sit on top of the
   sale styling and hide whether today had a sale; a ring marks the date
   while letting the green show through. */
.ad-cal-day.today { box-shadow:inset 0 0 0 2px #2563EB;color:#1D4ED8;font-weight:800 }
/* Sold TODAY — the ring turns green and fills, so "today + a sale" reads as a
   clear win rather than a plain blue box. */
.ad-cal-day.today.sale { background:#16A34A;color:#fff;box-shadow:inset 0 0 0 2px #15803D }
.ad-cal-day.today.sale::after { background:#fff!important;animation:none!important }
.ad-cal-day.empty { opacity:0;pointer-events:none }
.ad-cal-day:not(.empty):not(.today):not(.sale):hover { background:rgba(51,46,158,.06);color:#332E9E }

.ad-brow { display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:12px;background:#fff;border:1px solid rgba(51,46,158,.07);margin-bottom:8px;transition:all .15s }
.ad-brow:hover { background:#F8FAFF;border-color:rgba(51,46,158,.14);transform:translateX(2px) }

@media (max-width: 767.98px) {
  .ad-hero { padding:16px 18px 40px; }
  .ad-hero-plane { display:none; }
  .ad-money-row { margin-top:-22px; }
  .ad-money { padding:16px 18px; }
  .ad-money .d-flex { margin-bottom:8px; }
  .ad-count { font-size:1.9rem!important; }
  .ad-brow { gap:8px; padding:8px 10px; }
}
</style>

{{-- ══ HERO — solid gradient welcome banner ══ --}}
<div class="ad-hero">
  <i class="ph ph-paper-plane-tilt ad-hero-plane"></i>

  <div style="position:relative;z-index:1;">
    <div style="font-size:0.744rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
    <h2 style="color:#fff;font-size:1.74rem;font-weight:800;letter-spacing:-.03em;margin:0 0 6px;">{{ $greeting }}, {{ explode(' ', $user->name)[0] }} 👋</h2>
    <p style="color:rgba(255,255,255,.55);font-size:0.96rem;margin:0;">
      <span style="color:#4ADE80;font-weight:700;">{{ $myTotalBookings }}</span> booking{{ $myTotalBookings !== 1 ? 's' : '' }} in {{ now()->format('F') }}
      <span style="opacity:.4;">·</span>
      @if ($myTodayBookings > 0)<span style="color:#FBBF24;font-weight:700;">{{ $myTodayBookings }}</span> today
      @else<span style="color:rgba(255,255,255,.3);">None today yet</span>@endif
    </p>
  </div>
</div>

{{-- ══ MONEY STATS: Fresh | Issued | Pending | All-Time Pending — floated up onto the hero ══ --}}
<div class="row g-3 mb-4 ad-money-row ad-up d2">
  @php
    $moneyStats = [
      ['key'=>'fresh',   'label'=>'Fresh',            'sub'=>'All margin, this month',      'val'=>$myFresh,          'count'=>$myFreshCount,          'icon'=>'ph ph-trend-up',       'ic'=>'#332E9E','ibg'=>'rgba(51,46,158,.10)'],
      ['key'=>'issued',  'label'=>'Issued',           'sub'=>'Margin issued & fully paid',  'val'=>$myIssued,         'count'=>$myIssuedCount,         'icon'=>'ph ph-check-circle',   'ic'=>'#16A34A','ibg'=>'rgba(22,163,74,.10)'],
      ['key'=>'pending', 'label'=>'Pending',          'sub'=>'Not yet issued, this month',  'val'=>$myPending,        'count'=>$myPendingCount,        'icon'=>'ph ph-clock-countdown','ic'=>'#D97706','ibg'=>'rgba(217,119,6,.10)'],
      ['key'=>'alltime', 'label'=>'All-Time Pending', 'sub'=>'Not yet issued, all time',    'val'=>$myPendingAllTime, 'count'=>$myPendingAllTimeCount, 'icon'=>'ph ph-hourglass',      'ic'=>'#64748B','ibg'=>'rgba(100,116,139,.10)'],
    ];
  @endphp
  @foreach ($moneyStats as $ms)
    <div class="col-md-3">
      <div class="ad-money {{ $ms['key'] }}">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div class="ad-icon-badge" style="width:42px;height:42px;border-radius:12px;background:{{ $ms['ibg'] }};display:flex;align-items:center;justify-content:center;">
            <i class="{{ $ms['icon'] }}" style="font-size:1.38rem;color:{{ $ms['ic'] }};"></i>
          </div>
          <span style="font-size:1.104rem;font-weight:800;color:{{ $ms['ic'] }};background:{{ $ms['ibg'] }};padding:4px 13px;border-radius:20px;">{{ $ms['count'] }}</span>
        </div>
        <div style="font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;margin-bottom:4px;">{{ $ms['label'] }}</div>
        <div class="ad-count" data-target="{{ $ms['val'] }}" style="font-size:2.35rem;font-weight:800;letter-spacing:-.03em;color:#0F172A;line-height:1;">
          £{{ number_format($ms['val'], 2) }}
        </div>
        <div style="font-size:0.852rem;color:#475569;margin-top:4px;">{{ $ms['sub'] }}</div>
      </div>
    </div>
  @endforeach
</div>

{{-- ══ AGENT LEADERBOARD (live) + CALENDAR — side by side, one glance ══ --}}
{{-- Leaderboard carries counts only (no margin), safe for agents; the month
     sits beside it so the top of the dashboard reads in a single look. --}}
@php $currentKey = now()->format('Y-m'); @endphp
<div class="row g-3 mb-3">
  <div class="col-lg-8 ad-up d3">
    @livewire('selling-board')
  </div>
  <div class="col-lg-4">
    <div class="ad-up d3" style="max-width:300px;margin-left:auto;background:#FFFFFF;border-radius:14px;border:1px solid var(--to-border);box-shadow:0 1px 2px rgba(15,23,42,0.04);"
      x-data="{
        cur: @js($allMonthData[$currentKey])
      }">

      {{-- Header --}}
      <div class="px-3 pt-3 pb-1 d-flex align-items-center justify-content-between">
        <div class="fw-bold" style="font-size:0.912rem;color:#0F172A;" x-text="cur.label"></div>
        <div style="font-size:0.72rem;" x-text="cur.total + ' sale' + (cur.total!==1?'s':'')"
          :style="cur.total>0?'color:#16A34A;font-weight:700;':'color:#64748B;'"></div>
      </div>

      {{-- Day headers --}}
      <div class="px-3" style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;margin-bottom:2px;">
        @foreach (['M','T','W','T','F','S','S'] as $dh)
          <div style="text-align:center;font-size:0.624rem;font-weight:700;color:#64748B;padding:1px 0;">{{ $dh }}</div>
        @endforeach
      </div>

      {{-- Calendar grid - Alpine renders from JS data. Green = a sale was made that day. --}}
      <div class="px-3 pb-3" style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;">
        <template x-for="cell in (() => {
          const pads = Array(cur.startDow).fill(null);
          const days = Array.from({length:cur.daysInMonth},(_,i)=>i+1);
          return [...pads, ...days];
        })()" :key="cell ?? ('p'+Math.random())">
          <div x-show="cell !== null"
            :class="{
              'ad-cal-day':    true,
              'sale':          cell && cur.days[cur.year+'-'+(String(cur.month).padStart(2,'0'))+'-'+(String(cell).padStart(2,'0'))],
              'today':         cur.isCurrent && cell === {{ $today }}
            }"
            :title="cell && cur.days[cur.year+'-'+(String(cur.month).padStart(2,'0'))+'-'+(String(cell).padStart(2,'0'))] ? cur.days[cur.year+'-'+(String(cur.month).padStart(2,'0'))+'-'+(String(cell).padStart(2,'0'))]+' booking(s)' : ''"
            x-text="cell">
          </div>
          <div x-show="cell === null" class="ad-cal-day empty"></div>
        </template>
      </div>
    </div>
  </div>
</div>

{{-- ══ AGENTS PERFORMANCE — full width for the agent wall ══ --}}
<div class="row g-3 mb-3">
  <div class="col-12 ad-up d4">
    @include('content.dashboard.partials._agents-performance', [
      'agentsPerformance' => $agentsPerformance,
      'performanceLabel'  => $performanceLabel,
      'showMargin'        => $showPerformanceMargin,
    ])
  </div>
</div>

{{-- ══ Pending Bookings — full width, most urgent payment date first ══ --}}
<div class="ad-up d4" style="background:#FFFFFF;border-radius:14px;border:1px solid var(--to-border);overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,0.04);">
  <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <h6 class="fw-bold mb-0" style="font-size:1.044rem;color:#0F172A;">Pending Bookings</h6>
    <span style="font-size:0.744rem;font-weight:700;color:#D97706;background:rgba(217,119,6,.10);padding:2px 9px;border-radius:20px;">{{ $pendingTabBookings->count() }} booking{{ $pendingTabBookings->count() !== 1 ? 's' : '' }}</span>
  </div>
  @php
      $pendingTypeLabels = ['flight'=>'Flight','hotel'=>'Hotel','umrah'=>'Umrah','holiday'=>'Holiday','visa'=>'Visa','transfers'=>'Transfers','excursion'=>'Excursion'];
  @endphp
  <div class="px-4 pt-3 pb-2 d-flex align-items-center flex-wrap gap-2" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <a href="{{ request()->url() }}" style="font-size:0.792rem;font-weight:700;border-radius:20px;padding:5px 12px;text-decoration:none;{{ !$pendingTypeFilter ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
        All Types
    </a>
    @foreach ($pendingTypeLabels as $key => $label)
        <a href="{{ request()->url() }}?type={{ $key }}" class="d-flex align-items-center gap-1" style="font-size:0.792rem;font-weight:700;border-radius:20px;padding:5px 12px;text-decoration:none;{{ $pendingTypeFilter === $key ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
            <span style="{{ $pendingTypeFilter === $key ? 'color:#fff;' : 'color:#475569;font-weight:400;' }}">{{ $label }}</span>
            <span>{{ $pendingTypeCounts->get($key, 0) }}</span>
        </a>
    @endforeach
  </div>
  @include('content.dashboard.partials._bookings-table', ['bookings' => $pendingTabBookings])
</div>

<script>
document.querySelectorAll('.ad-count').forEach(el => {
  const t = parseFloat(el.getAttribute('data-target')) || 0;
  if (t === 0 && el.getAttribute('data-target') === '0') el.textContent = (el.getAttribute('data-prefix')||'') + '0.00';
  if (!t) return;
  const isMoney = el.getAttribute('data-prefix') === '£';
  let s = 0, dur = 900;
  const run = ts => { if (!s) s = ts; const p = Math.min((ts-s)/dur,1), e = 1-Math.pow(1-p,3); const v = e * t; el.textContent = (isMoney ? '£' : '') + (isMoney ? v.toFixed(2) : Math.floor(v).toLocaleString()); if (p<1) requestAnimationFrame(run); else el.textContent = (el.getAttribute('data-prefix')||'') + (isMoney ? t.toFixed(2) : t.toLocaleString()); };
  requestAnimationFrame(run);
});
document.querySelectorAll('[data-target]').forEach(el => {
  const t = parseFloat(el.getAttribute('data-target')) || 0;
  const isGbp = el.closest('.ad-money') !== null;
  el.setAttribute('data-prefix', isGbp ? '£' : '');
});
</script>
@endsection
