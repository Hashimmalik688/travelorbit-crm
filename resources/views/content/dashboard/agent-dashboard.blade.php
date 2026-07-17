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
@keyframes heroFlow { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }
@keyframes orbFloat { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-10px,10px) scale(1.08)} }
@keyframes orbFloat2{ 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(10px,-8px) scale(1.1)} }
@keyframes planeDrift{ 0%,100%{transform:translateY(0) rotate(8deg)} 50%{transform:translateY(-10px) rotate(8deg)} }
@keyframes iconPulse{ 0%,100%{box-shadow:0 0 0 0 var(--pulse-c,transparent)} 70%{box-shadow:0 0 0 8px transparent} }

.ad-up      { animation: fadeUp .4s ease both; }
.ad-up.d1   { animation-delay:.04s }
.ad-up.d2   { animation-delay:.09s }
.ad-up.d3   { animation-delay:.14s }
.ad-up.d4   { animation-delay:.19s }
.ad-up.d5   { animation-delay:.24s }
.ad-count   { animation: countUp .6s cubic-bezier(.22,1,.36,1) both; animation-delay:.35s }

/* Hero */
.ad-hero {
  background:linear-gradient(120deg,rgba(51,46,158,0.90) 0%,rgba(124,58,237,0.74) 45%,rgba(99,102,241,0.80) 100%);
  background-size:200% 200%;
  animation: fadeUp .4s ease both, heroFlow 10s ease-in-out infinite;
  border-radius:20px;padding:22px 30px 52px;position:relative;overflow:hidden;
  backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
  border:1px solid rgba(255,255,255,0.2);
  box-shadow:0 8px 32px rgba(51,46,158,0.28),inset 0 1px 0 rgba(255,255,255,0.18);
}
.ad-orb { position:absolute;border-radius:50%;pointer-events:none; }
.ad-orb.o1 { right:-50px;top:-50px;width:180px;height:180px;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);animation:orbFloat 7s ease-in-out infinite; }
.ad-orb.o2 { left:-30px;bottom:-30px;width:100px;height:100px;background:radial-gradient(circle,rgba(255,107,53,0.12) 0%,transparent 70%);animation:orbFloat2 8s ease-in-out infinite; }
.ad-orb.o3 { right:25%;top:-15px;width:60px;height:60px;background:radial-gradient(circle,rgba(22,163,74,0.1) 0%,transparent 70%);animation:orbFloat 6s ease-in-out infinite; }
.ad-hero-plane { position:absolute;right:34px;top:50%;transform:translateY(-50%) rotate(8deg);font-size:5rem;color:rgba(255,255,255,.14);animation:planeDrift 5s ease-in-out infinite;pointer-events:none; }

/* Money stats - Glassmorphism, floated up over the hero */
.ad-money-row { margin-top:-32px;position:relative;z-index:2; }
.ad-money { border-radius:18px;padding:22px 24px;background:linear-gradient(135deg,rgba(255,255,255,0.95) 0%,rgba(255,255,255,0.85) 100%);border:1px solid rgba(255,255,255,0.6);box-shadow:0 10px 30px rgba(51,46,158,0.16),0 1px 3px rgba(0,0,0,0.04);position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px) }
.ad-money:hover { transform:translateY(-4px);box-shadow:0 16px 36px rgba(51,46,158,0.18) }
.ad-money::after { content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:18px 18px 0 0 }
.ad-money.fresh::after  { background:linear-gradient(90deg,#332E9E,#6366F1) }
.ad-money.issued::after { background:linear-gradient(90deg,#16A34A,#4ADE80) }
.ad-money.pending::after{ background:linear-gradient(90deg,#D97706,#FBBF24) }
.ad-money.alltime::after{ background:linear-gradient(90deg,#64748B,#94A3B8) }
.ad-money { height:100%;display:flex;flex-direction:column; }
.ad-icon-badge { animation: iconPulse 2.6s ease-in-out infinite; }
.ad-money.fresh   .ad-icon-badge { --pulse-c: rgba(51,46,158,.20); }
.ad-money.issued  .ad-icon-badge { --pulse-c: rgba(22,163,74,.20); }
.ad-money.pending .ad-icon-badge { --pulse-c: rgba(217,119,6,.20); }
.ad-money.alltime .ad-icon-badge { --pulse-c: rgba(100,116,139,.20); }

/* Calendar styles */
.ad-cal-day {
  aspect-ratio:1;display:flex;align-items:center;justify-content:center;
  border-radius:6px;font-size:0.72rem;font-weight:500;color:#475569;cursor:default;
  transition:all .15s;position:relative;
}
.ad-cal-day.sale { background:rgba(22,163,74,.12);color:#16A34A;font-weight:700 }
.ad-cal-day.sale::after { content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:4px;height:4px;border-radius:50%;background:#16A34A;animation:pulse 2s infinite }
.ad-cal-day.today { background:linear-gradient(135deg,#332E9E,#4A45B5)!important;color:#fff!important;font-weight:800!important;box-shadow:0 3px 10px rgba(51,46,158,.35) }
.ad-cal-day.today::after { background:#fff!important;animation:none!important }
.ad-cal-day.empty { opacity:0;pointer-events:none }
.ad-cal-day:not(.empty):not(.today):hover { background:rgba(51,46,158,.06);color:#332E9E }

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

{{-- ══ GLASSMORPHISM HERO ══ --}}
<div class="ad-hero">
  <div class="ad-orb o1"></div>
  <div class="ad-orb o2"></div>
  <div class="ad-orb o3"></div>
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

{{-- ══ MAIN 2-COL: Agent Wall + Calendar — moved up, right under the KPIs ══ --}}
<div class="row g-3 mb-3">

  {{-- ── Left column: Agent Wall ── --}}
  <div class="col-lg-8">

    {{-- Agent Wall --}}
    @if ($allAgents->isNotEmpty())
      @include('content.dashboard.partials._agents-today', ['agents' => $allAgents])
    @endif
  </div>

  {{-- ── Right column: Calendar ── --}}
  <div class="col-lg-4">
    @php $currentKey = now()->format('Y-m'); @endphp
    <div class="ad-up d3" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:20px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);"
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

{{-- ══ Pending Bookings — full width, most urgent payment date first ══ --}}
<div class="ad-up d4" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:20px;border:1px solid rgba(255,255,255,0.5);overflow:hidden;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);">
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
