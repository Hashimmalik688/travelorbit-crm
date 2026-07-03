@extends('layouts/contentNavbarLayout')
@section('title', 'My Dashboard')

@php
  $user      = Auth::user();
  $hour      = now()->hour;
  $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
  $monthName = now()->format('F Y');
  $daysInMonth = now()->daysInMonth;
  $startDow  = (int) now()->startOfMonth()->dayOfWeek;
  $startDow  = $startDow === 0 ? 6 : $startDow - 1;
  $today     = now()->day;
@endphp

@section('content')
<style>
@keyframes fadeUp  { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
@keyframes countUp { from{opacity:0;transform:scale(0.88)} to{opacity:1;transform:scale(1)} }
@keyframes barGrow { from{width:0} to{width:var(--bw)} }
@keyframes pulse   { 0%,100%{box-shadow:0 0 0 0 rgba(22,163,74,.4)} 50%{box-shadow:0 0 0 5px rgba(22,163,74,0)} }

.ad-up      { animation: fadeUp .4s ease both; }
.ad-up.d1   { animation-delay:.04s }
.ad-up.d2   { animation-delay:.09s }
.ad-up.d3   { animation-delay:.14s }
.ad-up.d4   { animation-delay:.19s }
.ad-up.d5   { animation-delay:.24s }
.ad-count   { animation: countUp .6s cubic-bezier(.22,1,.36,1) both; animation-delay:.35s }

/* Money stats - Glassmorphism */
.ad-money { border-radius:18px;padding:24px 26px;background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.78) 100%);border:1px solid rgba(255,255,255,0.5);box-shadow:0 4px 24px rgba(51,46,158,0.08),0 1px 3px rgba(0,0,0,0.04);position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px) }
.ad-money:hover { transform:translateY(-3px);box-shadow:0 12px 32px rgba(51,46,158,0.14) }
.ad-money::after { content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:18px 18px 0 0 }
.ad-money.fresh::after  { background:linear-gradient(90deg,#332E9E,#6366F1) }
.ad-money.issued::after { background:linear-gradient(90deg,#16A34A,#4ADE80) }
.ad-money.pending::after{ background:linear-gradient(90deg,#D97706,#FBBF24) }

/* Count chips - Glassmorphism */
.ad-chip { border-radius:16px;padding:18px 22px;background:linear-gradient(135deg,rgba(255,255,255,0.9) 0%,rgba(255,255,255,0.75) 100%);border:1px solid rgba(255,255,255,0.5);display:flex;align-items:center;gap:16px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);box-shadow:0 4px 16px rgba(51,46,158,0.06) }

/* Calendar styles */
.ad-cal-day {
  aspect-ratio:1;display:flex;align-items:center;justify-content:center;
  border-radius:7px;font-size:.68rem;font-weight:500;color:#64748B;cursor:default;
  transition:all .15s;position:relative;
}
.ad-cal-day.sale { background:rgba(22,163,74,.12);color:#16A34A;font-weight:700 }
.ad-cal-day.sale::after { content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:4px;height:4px;border-radius:50%;background:#16A34A;animation:pulse 2s infinite }
.ad-cal-day.today { background:linear-gradient(135deg,#332E9E,#4A45B5)!important;color:#fff!important;font-weight:800!important;box-shadow:0 3px 10px rgba(51,46,158,.35) }
.ad-cal-day.today::after { background:#fff!important;animation:none!important }
.ad-cal-day.empty { opacity:0;pointer-events:none }
.ad-cal-day:not(.empty):not(.today):hover { background:rgba(51,46,158,.06);color:#332E9E }

/* Mini past calendar */
.ad-mini-cal { border-radius:14px;background:#fff;border:1px solid rgba(51,46,158,.08);padding:16px;flex:1;min-width:0 }
.ad-mini-day { aspect-ratio:1;display:flex;align-items:center;justify-content:center;border-radius:5px;font-size:.58rem;color:#CBD5E1 }
.ad-mini-day.ms { background:rgba(22,163,74,.15);color:#16A34A;font-weight:700 }
.ad-mini-day.empty-mini { opacity:0 }

.ad-brow { display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:12px;background:#fff;border:1px solid rgba(51,46,158,.07);margin-bottom:8px;transition:all .15s }
.ad-brow:hover { background:#F8FAFF;border-color:rgba(51,46,158,.14);transform:translateX(2px) }
.ad-tab { padding:5px 14px;border-radius:20px;font-size:.72rem;font-weight:600;border:none;cursor:pointer;transition:all .15s;background:transparent;color:#64748B }
.ad-tab.on { background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;box-shadow:0 3px 10px rgba(51,46,158,.25) }

@media (max-width: 767.98px) {
  .ad-money { padding:16px 18px; }
  .ad-money .d-flex { margin-bottom:8px; }
  .ad-chip { padding:14px 16px; gap:12px; }
  .ad-brow { gap:8px; padding:8px 10px; }
  .ad-tab { font-size:.64rem; padding:4px 10px; }
}
@media (max-width: 575.98px) {
  .ad-tab { font-size:.6rem; padding:3px 8px; }
}
</style>

{{-- ══ GLASSMORPHISM HERO ══ --}}
<div class="ad-up d1 mb-4" style="background:linear-gradient(135deg,rgba(51,46,158,0.88) 0%,rgba(124,58,237,0.72) 50%,rgba(99,102,241,0.78) 100%);border-radius:20px;padding:24px 30px;position:relative;overflow:hidden;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.2);box-shadow:0 8px 32px rgba(51,46,158,0.28),inset 0 1px 0 rgba(255,255,255,0.18);">
  {{-- Decorative orbs --}}
  <div style="position:absolute;right:-50px;top:-50px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;left:-30px;bottom:-30px;width:100px;height:100px;border-radius:50%;background:radial-gradient(circle,rgba(255,107,53,0.12) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;right:25%;top:-15px;width:60px;height:60px;border-radius:50%;background:radial-gradient(circle,rgba(22,163,74,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  
  <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap" style="position:relative;z-index:1;">
    <div>
      <div style="font-size:.62rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
      <h2 style="color:#fff;font-size:1.45rem;font-weight:800;letter-spacing:-.03em;margin:0 0 6px;">{{ $greeting }}, {{ explode(' ', $user->name)[0] }} 👋</h2>
      <p style="color:rgba(255,255,255,.55);font-size:.8rem;margin:0;">
        <span style="color:#4ADE80;font-weight:700;">{{ $myTotalBookings }}</span> booking{{ $myTotalBookings !== 1 ? 's' : '' }} in {{ now()->format('F') }}
        <span style="opacity:.4;">·</span>
        @if ($myTodayBookings > 0)<span style="color:#FBBF24;font-weight:700;">{{ $myTodayBookings }}</span> today
        @else<span style="color:rgba(255,255,255,.3);">None today yet</span>@endif
      </p>
    </div>
    <a href="{{ route('bookings.create') }}" class="btn fw-bold d-flex align-items-center gap-2 flex-shrink-0"
      style="background:linear-gradient(135deg,rgba(255,255,255,0.22) 0%,rgba(255,255,255,0.1) 100%);color:#fff;border:1px solid rgba(255,255,255,0.28);border-radius:12px;padding:10px 22px;font-size:.8rem;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 16px rgba(0,0,0,0.18);transition:all .2s;">
      <i class="ph ph-plus-circle"></i> New Booking
    </a>
  </div>
</div>

{{-- ══ MONEY STATS: Fresh | Issued | Pending ══ --}}
<div class="row g-3 mb-4 ad-up d2">
  @php
    $moneyStats = [
      ['key'=>'fresh',  'label'=>'Fresh',   'sub'=>'Margin not yet issued',       'val'=>$myFresh,   'icon'=>'ph ph-trend-up',      'ic'=>'#332E9E','ibg'=>'rgba(51,46,158,.10)','hint'=>'Margin (sale price minus cost) for non-issued bookings created this month'],
      ['key'=>'issued', 'label'=>'Issued',  'sub'=>'Margin issued & fully paid',   'val'=>$myIssued,  'icon'=>'ph ph-check-circle',  'ic'=>'#16A34A','ibg'=>'rgba(22,163,74,.10)','hint'=>'Margin for issued bookings whose balance was fully paid this month'],
      ['key'=>'pending','label'=>'Pending', 'sub'=>'Margin awaiting full payment', 'val'=>$myPending, 'icon'=>'ph ph-clock-countdown','ic'=>'#D97706','ibg'=>'rgba(217,119,6,.10)','hint'=>'Margin for issued bookings still on a payment plan or awaiting payment'],
    ];
  @endphp
  @foreach ($moneyStats as $ms)
    <div class="col-md-4">
      <div class="ad-money {{ $ms['key'] }}">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div style="width:42px;height:42px;border-radius:12px;background:{{ $ms['ibg'] }};display:flex;align-items:center;justify-content:center;">
            <i class="{{ $ms['icon'] }}" style="font-size:1.15rem;color:{{ $ms['ic'] }};"></i>
          </div>
          <span style="font-size:.62rem;color:#94A3B8;text-align:right;max-width:100px;line-height:1.3;">{{ $ms['hint'] }}</span>
        </div>
        <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94A3B8;margin-bottom:4px;">{{ $ms['label'] }}</div>
        <div class="ad-count" data-target="{{ $ms['val'] }}" style="font-size:1.75rem;font-weight:800;letter-spacing:-.03em;color:#0F172A;line-height:1;">
          £{{ number_format($ms['val'], 2) }}
        </div>
        <div style="font-size:.71rem;color:#64748B;margin-top:4px;">{{ $ms['sub'] }}</div>
      </div>
    </div>
  @endforeach
</div>

{{-- ══ COUNT CHIPS: This Month + Today ══ --}}
<div class="row g-3 mb-4 ad-up d3">
  <div class="col-md-3">
    <div class="ad-chip">
      <div style="width:38px;height:38px;border-radius:10px;background:rgba(51,46,158,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="ph ph-calendar-dots" style="font-size:1.1rem;color:#332E9E;"></i>
      </div>
      <div>
        <div style="font-size:.62rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">This Month</div>
        <div class="ad-count" data-target="{{ $myTotalBookings }}" style="font-size:1.4rem;font-weight:800;color:#0F172A;letter-spacing:-.02em;line-height:1.1;">{{ $myTotalBookings }}</div>
        <div style="font-size:.68rem;color:#64748B;">bookings in {{ now()->format('F') }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="ad-chip">
      <div style="width:38px;height:38px;border-radius:10px;background:rgba(245,158,11,.10);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="ph ph-sun-horizon" style="font-size:1.1rem;color:#D97706;"></i>
      </div>
      <div>
        <div style="font-size:.62rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">Today</div>
        <div class="ad-count" data-target="{{ $myTodayBookings }}" style="font-size:1.4rem;font-weight:800;color:#0F172A;letter-spacing:-.02em;line-height:1.1;">{{ $myTodayBookings }}</div>
        <div style="font-size:.68rem;color:#64748B;">{{ now()->format('d F') }}</div>
      </div>
    </div>
  </div>
</div>

{{-- ══ MAIN 2-COL ══ --}}
<div class="row g-3">

  {{-- ── Bookings list ── --}}
  <div class="col-lg-8 ad-up d3">
    <div style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:20px;border:1px solid rgba(255,255,255,0.5);overflow:hidden;backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);">
      <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-bottom:1px solid rgba(51,46,158,.06);">
        <h6 class="fw-bold mb-0" style="font-size:.87rem;color:#0F172A;">Recent Bookings</h6>
        <div class="d-flex gap-1 p-1" style="background:#F1F5F9;border-radius:24px;" id="ad-tabs">
          <button class="ad-tab on"  data-filter="all"       onclick="adFilter('all',this)">All</button>
          <button class="ad-tab"     data-filter="confirmed" onclick="adFilter('confirmed',this)">Confirmed</button>
          <button class="ad-tab"     data-filter="pending"   onclick="adFilter('pending',this)">Pending</button>
          <button class="ad-tab"     data-filter="cancelled" onclick="adFilter('cancelled',this)">Cancelled</button>
        </div>
      </div>
      <div class="px-4 py-3" id="ad-booking-list">
        @forelse ($myRecentBookings as $bk)
          @php
            $sc = ['confirmed'=>'#16A34A','pending'=>'#D97706','cancelled'=>'#DC2626'][$bk->booking_status] ?? '#94A3B8';
            $sb = ['confirmed'=>'rgba(22,163,74,.10)','pending'=>'rgba(217,119,6,.10)','cancelled'=>'rgba(220,38,38,.10)'][$bk->booking_status] ?? 'rgba(148,163,184,.10)';
            $ti = ['flight'=>'ph-airplane','hotel'=>'ph-buildings','holiday'=>'ph-island','umrah'=>'ph-mosque','visa'=>'ph-identification-card','transfers'=>'ph-van','excursion'=>'ph-binoculars'][$bk->booking_type ?? ''] ?? 'ph-ticket';
          @endphp
          <div class="ad-brow" data-status="{{ $bk->booking_status }}">
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(51,46,158,.07);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ph {{ $ti }}" style="font-size:.95rem;color:#332E9E;"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold" style="font-size:.79rem;color:#1E293B;">{{ $bk->booking_number }}</span>
                <span style="font-size:.6rem;background:{{ $sb }};color:{{ $sc }};padding:1px 8px;border-radius:20px;font-weight:700;text-transform:capitalize;">{{ $bk->booking_status }}</span>
              </div>
              <div style="font-size:.7rem;color:#64748B;margin-top:1px;">{{ $bk->booker_first_name }} {{ $bk->booker_last_name }}@if($bk->booking_type) · {{ ucfirst($bk->booking_type) }}@endif</div>
            </div>
            <div class="text-end flex-shrink-0">
              <div style="font-size:.69rem;color:#94A3B8;">{{ $bk->created_at->format('d M') }}</div>
              <div style="font-size:.67rem;color:#64748B;">{{ $bk->created_at->format('H:i') }}</div>
            </div>
            <a href="{{ route('bookings.show', $bk->id) }}" style="color:#332E9E;opacity:.35;font-size:.84rem;flex-shrink:0;margin-left:4px;"><i class="ph ph-arrow-right"></i></a>
          </div>
        @empty
          <div class="text-center py-5" style="color:#C4C9D4;">
            <i class="ph ph-calendar-blank" style="font-size:2.2rem;display:block;margin-bottom:8px;opacity:.4;"></i>
            <div style="font-size:.76rem;">No bookings yet.</div>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- ── Right column: Agent Wall + Calendar ── --}}
  <div class="col-lg-4">

    {{-- Agent Wall --}}
    @if ($allAgents->isNotEmpty())
    <div class="ad-up d4 mb-3" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:20px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);overflow:hidden;">
      <div class="px-4 pt-4 pb-2" style="border-bottom:1px solid rgba(51,46,158,.06);">
        <h6 class="fw-bold mb-0" style="font-size:.87rem;color:#0F172A;">Agents Today</h6>
        <div style="font-size:.65rem;color:#94A3B8;">{{ now()->format('d F Y') }}</div>
      </div>
      <div class="p-3">
        <div style="display:flex;flex-direction:column;gap:10px;">
          @foreach ($allAgents as $agent)
            @php
              $parts = explode(' ', $agent->name);
              $firstName = $parts[0];
              $initials = strtoupper(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));
              $madeBooking = $agent->bookings_count > 0;
              $photoUrl = $agent->profile_photo_path ? asset('storage/' . $agent->profile_photo_path) : null;
            @endphp
            <div style="position:relative;border-radius:14px;overflow:hidden;transition:all .25s ease;border:2px solid {{ $madeBooking ? 'rgba(16,185,129,0.6)' : 'rgba(244,63,94,0.45)' }};box-shadow:0 6px 28px {{ $madeBooking ? 'rgba(16,185,129,0.25)' : 'rgba(244,63,94,0.18)' }};">
              <div style="height:140px;background:linear-gradient(135deg,#4F46E5 0%,#6366F1 50%,#8B5CF6 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
                {{-- Background decorative shapes --}}
                <div style="position:absolute;top:-30px;right:-20px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,0.08);pointer-events:none;"></div>
                <div style="position:absolute;bottom:-25px;left:-15px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;"></div>
                @if ($photoUrl)
                  <img src="{{ $photoUrl }}" alt="{{ $firstName }}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">
                  <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,rgba(15,23,42,0.6) 100%);z-index:1;"></div>
                @else
                  <div style="font-size:3rem;font-weight:800;color:rgba(255,255,255,0.9);z-index:1;text-shadow:0 4px 16px rgba(0,0,0,0.25);letter-spacing:.08em;">{{ $initials }}</div>
                @endif
                {{-- Status ribbon --}}
                <div style="position:absolute;top:0;right:0;z-index:2;background:{{ $madeBooking ? '#10B981' : '#F43F5E' }};color:#fff;padding:4px 14px 6px 16px;border-radius:0 12px 0 14px;font-size:.66rem;font-weight:700;letter-spacing:.04em;box-shadow:0 4px 12px {{ $madeBooking ? 'rgba(16,185,129,0.4)' : 'rgba(244,63,94,0.35)' }};">
                  @if ($madeBooking)
                    {{ $agent->bookings_count }} today
                  @else
                    No bookings
                  @endif
                </div>
              </div>
              <div style="padding:12px 14px;background:linear-gradient(180deg,{{ $madeBooking ? 'rgba(16,185,129,0.06)' : 'rgba(244,63,94,0.04)' }} 0%,rgba(255,255,255,0.5) 100%);">
                <div style="font-size:.82rem;font-weight:700;color:#1E293B;line-height:1.2;">{{ $firstName }}</div>
                <div style="font-size:.66rem;color:#64748B;margin-top:1px;">{{ $agent->name }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    {{-- Calendar --}}
    @php $currentKey = now()->format('Y-m'); @endphp
    <div class="ad-up d4" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:20px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);"
      x-data="{
        cur: @js($allMonthData[$currentKey])
      }">

      {{-- Header --}}
      <div class="px-4 pt-4 pb-2 text-center">
        <div class="fw-bold" style="font-size:.88rem;color:#0F172A;" x-text="cur.label"></div>
        <div style="font-size:.65rem;margin-top:1px;" x-text="cur.total + ' booking' + (cur.total!==1?'s':'')"
          :style="cur.total>0?'color:#16A34A;font-weight:600;':'color:#CBD5E1;'"></div>
      </div>

      {{-- Legend --}}
      <div class="px-4 pb-2 d-flex gap-3 justify-content-center" style="font-size:.6rem;color:#94A3B8;">
        <span class="d-flex align-items-center gap-1"><span style="width:7px;height:7px;border-radius:50%;background:#332E9E;display:inline-block;"></span>Today</span>
        <span class="d-flex align-items-center gap-1"><span style="width:7px;height:7px;border-radius:50%;background:#16A34A;display:inline-block;"></span>Booking</span>
      </div>

      {{-- Day headers --}}
      <div class="px-4" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;margin-bottom:3px;">
        @foreach (['Mo','Tu','We','Th','Fr','Sa','Su'] as $dh)
          <div style="text-align:center;font-size:.56rem;font-weight:700;color:#CBD5E1;padding:2px 0;">{{ $dh }}</div>
        @endforeach
      </div>

      {{-- Calendar grid - Alpine renders from JS data --}}
      <div class="px-4 pb-3" style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;">
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

      {{-- Footer --}}
      <div class="px-4 pb-3 d-flex justify-content-between" style="font-size:.65rem;color:#64748B;border-top:1px solid rgba(51,46,158,.05);padding-top:8px;">
        <span x-text="Object.values(cur.days).reduce((a,b)=>a+b,0) + ' booking' + (Object.values(cur.days).reduce((a,b)=>a+b,0)!==1?'s':'')"></span>
        <span x-text="Object.keys(cur.days).length + ' active day' + (Object.keys(cur.days).length!==1?'s':'')"></span>
      </div>
    </div>
  </div>
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
function adFilter(s,btn){
  document.querySelectorAll('.ad-tab').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  document.querySelectorAll('.ad-brow').forEach(r=>r.style.display=(s==='all'||r.dataset.status===s)?'':'none');
}
</script>
@endsection
