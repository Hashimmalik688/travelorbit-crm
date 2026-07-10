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
.oc-lbl{ font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94A3B8;margin-bottom:4px }
.oc-sub{ font-size:.71rem;color:#64748B;margin-top:4px }
/* Workflow pipeline */
.wf-step{ flex:1;text-align:center;padding:14px 8px;border-radius:12px;border:1px solid rgba(51,46,158,.07);background:#fff;transition:transform .15s,box-shadow .15s;cursor:default }
.wf-step:hover{ transform:translateY(-2px);box-shadow:0 4px 14px rgba(51,46,158,.08) }
.wf-step-val{ font-size:1.5rem;font-weight:800;letter-spacing:-.03em;color:#0F172A;line-height:1 }
.wf-step-lbl{ font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-top:4px }
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
      <div style="font-size:.62rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
      <h2 style="color:#fff;font-size:1.5rem;font-weight:800;letter-spacing:-.03em;margin:0 0 6px;">{{ $greet }}, {{ explode(' ', $user->name)[0] }} 👋</h2>
      <p style="color:rgba(255,255,255,.55);font-size:.82rem;margin:0;">Operations Centre <span style="opacity:.5;">·</span> Full system overview</p>
    </div>
  </div>
</div>

{{-- ══ WORKFLOW PIPELINE ══ --}}
<div class="oc-up d2 mb-4" style="background:linear-gradient(135deg,rgba(255,255,255,0.7) 0%,rgba(248,250,252,0.6) 100%);border-radius:16px;padding:20px 24px;border:1px solid rgba(51,46,158,0.08);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 20px rgba(51,46,158,0.06);">
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="fw-bold" style="font-size:.84rem;color:#0F172A;">Booking Pipeline</span>
    <span style="font-size:.68rem;color:#94A3B8;">live status across all bookings</span>
  </div>
  <div class="d-flex gap-2">
    @php
      $pipeline = [
        ['label'=>'Pending',           'val'=>$pendingCount,    'color'=>'#332E9E','bg'=>'rgba(51,46,158,.10)',  'icon'=>'ph-clock'],
        ['label'=>'Issuance Queue',    'val'=>$issuanceQueue,   'color'=>'#D97706','bg'=>'rgba(217,119,6,.10)',  'icon'=>'ph-ticket'],
        ['label'=>'Ticket in Process', 'val'=>$ticketInProcess, 'color'=>'#0EA5E9','bg'=>'rgba(14,165,233,.10)', 'icon'=>'ph-airplane-takeoff'],
        ['label'=>'Invoiced',          'val'=>$invoicedCount,   'color'=>'#16A34A','bg'=>'rgba(22,163,74,.10)',  'icon'=>'ph-receipt'],
        ['label'=>'Confirmed',         'val'=>$confirmedCount,  'color'=>'#7C3AED','bg'=>'rgba(124,58,237,.10)', 'icon'=>'ph-check-circle'],
        ['label'=>'Cancelled',         'val'=>$cancelledCount,  'color'=>'#DC2626','bg'=>'rgba(220,38,38,.10)', 'icon'=>'ph-x-circle'],
      ];
    @endphp
    @foreach ($pipeline as $pi => $pw)
      <div class="wf-step oc-up" style="animation-delay:{{ 0.05 + $pi * 0.04 }}s;background:linear-gradient(135deg,rgba(255,255,255,0.9) 0%,rgba(255,255,255,0.75) 100%);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);">
        <div style="width:34px;height:34px;border-radius:10px;background:{{ $pw['bg'] }};margin:0 auto 10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
          <i class="ph {{ $pw['icon'] }}" style="font-size:1.05rem;color:{{ $pw['color'] }};"></i>
        </div>
        <div class="wf-step-val" style="color:{{ $pw['color'] }};">{{ $pw['val'] }}</div>
        <div class="wf-step-lbl" style="color:{{ $pw['color'] }};">{{ $pw['label'] }}</div>
      </div>
      @if (!$loop->last)
        <div class="d-flex align-items-center flex-shrink-0" style="color:#CBD5E1;font-size:.85rem;margin-top:14px;"><i class="ph ph-caret-right"></i></div>
      @endif
    @endforeach
  </div>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="row g-3 mb-4">
  @php
    $kpis = [
      ['label'=>'Bookings This Month', 'val'=>$totalBookings,         'sub'=>now()->format('F Y'),    'color'=>'c-indigo','icon'=>'ph ph-calendar-dots',    'ibg'=>'rgba(51,46,158,.10)',  'ic'=>'#332E9E', 'fmt'=>'num'],
      ['label'=>'Revenue This Month',  'val'=>$totalRevenue,          'sub'=>'total margin',          'color'=>'c-green', 'icon'=>'ph ph-currency-pound',    'ibg'=>'rgba(22,163,74,.10)',  'ic'=>'#16A34A', 'fmt'=>'gbp'],
      ['label'=>'Outstanding Balance', 'val'=>$outstandingPayments,   'sub'=>'across all bookings',   'color'=>'c-amber', 'icon'=>'ph ph-clock-countdown',   'ibg'=>'rgba(217,119,6,.10)',  'ic'=>'#D97706', 'fmt'=>'gbp'],
      ['label'=>'Overdue Payments',    'val'=>$overduePaymentsCount,  'sub'=>$overduePaymentsCount===0?'All clear':'past due date','color'=>'c-rose','icon'=>'ph ph-warning-circle','ibg'=>'rgba(220,38,38,.10)','ic'=>'#DC2626','fmt'=>'num'],
    ];
  @endphp
  @foreach ($kpis as $si => $k)
    <div class="col-md-3 oc-up d{{ $si + 1 }}">
      <div class="oc-card {{ $k['color'] }}">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div style="width:40px;height:40px;border-radius:11px;background:{{ $k['ibg'] }};display:flex;align-items:center;justify-content:center;">
            <i class="{{ $k['icon'] }}" style="font-size:1.1rem;color:{{ $k['ic'] }};"></i>
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

{{-- ══ MAIN 2-COL ══ --}}
<div class="row g-3">

  {{-- Agent Leaderboard --}}
  <div class="col-lg-5 oc-up d3">
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);overflow:hidden;">
      <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid rgba(51,46,158,.06);">
        <h6 class="fw-bold mb-0" style="font-size:.85rem;color:#0F172A;">Agent Leaderboard</h6>
        <span style="font-size:.7rem;color:#94A3B8;">{{ now()->format('F Y') }}</span>
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
            <div style="width:34px;height:34px;border-radius:50%;background:{{ $c }}18;color:{{ $c }};display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:800;flex-shrink:0;">{{ $initials }}</div>
            <div class="flex-grow-1 min-width-0">
              <div class="fw-semibold" style="font-size:.78rem;color:#1E293B;">{{ $ag->name }}</div>
              <div style="height:4px;background:rgba(51,46,158,.07);border-radius:20px;margin-top:4px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:{{ $c }};border-radius:20px;transition:width .8s;"></div>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <div class="fw-bold" style="font-size:.88rem;color:{{ $c }};">{{ $ag->month_bookings }}</div>
              <div style="font-size:.62rem;color:#94A3B8;">bookings</div>
            </div>
          </div>
        @empty
          <div class="text-center py-4" style="color:#C4C9D4;font-size:.75rem;">No agent data this month.</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Right: Status donut + 7-day sparkline --}}
  <div class="col-lg-7 oc-up d4">
    <div class="row g-3 h-100">

      {{-- Donut --}}
      <div class="col-md-5">
        <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);padding:20px 18px;height:100%;">
          <h6 class="fw-bold mb-3" style="font-size:.82rem;color:#0F172A;">Status Breakdown</h6>
          <div id="oc-donut" style="height:180px;"></div>
          <div class="d-flex flex-column gap-1 mt-2">
            @foreach ([['Pending',$pendingCount,'#332E9E'],['Confirmed',$confirmedCount,'#7C3AED'],['Invoiced',$invoicedCount,'#16A34A'],['Issuance Q.',$issuanceQueue,'#D97706']] as [$l,$v,$c])
              <div class="d-flex align-items-center justify-content-between" style="font-size:.72rem;">
                <span class="d-flex align-items-center gap-2"><span style="width:8px;height:8px;border-radius:50%;background:{{ $c }};display:inline-block;"></span>{{ $l }}</span>
                <span class="fw-semibold" style="color:#374151;">{{ $v }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- 7-day trend --}}
      <div class="col-md-7">
        <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);padding:20px 18px;height:100%;">
          <h6 class="fw-bold mb-1" style="font-size:.82rem;color:#0F172A;">7-Day Bookings</h6>
          <div style="font-size:.7rem;color:#94A3B8;margin-bottom:12px;">{{ now()->subDays(6)->format('d M') }} - {{ now()->format('d M') }}</div>
          <div id="oc-spark" style="height:160px;"></div>
        </div>
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
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

  // Donut
  new ApexCharts(document.getElementById('oc-donut'), {
    chart:{ type:'donut', height:180, animations:{enabled:true,speed:600} },
    series:[ {{ $pendingCount }}, {{ $confirmedCount }}, {{ $invoicedCount }}, {{ $issuanceQueue }}, {{ $ticketInProcess }} ],
    labels:['Pending','Confirmed','Invoiced','Issuance Q.','In Process'],
    colors:['#332E9E','#7C3AED','#16A34A','#D97706','#0EA5E9'],
    dataLabels:{enabled:false}, legend:{show:false}, stroke:{width:0},
    plotOptions:{ pie:{ donut:{ size:'72%', labels:{ show:true, total:{ show:true, label:'Total', fontSize:'11px', color:'#94A3B8', fontWeight:600, formatter:w=>w.globals.seriesTotals.reduce((a,b)=>a+b,0) } } } } },
    tooltip:{ y:{formatter:v=>v+' bookings'} }
  }).render();

  // 7-day bar
  @php
    $labels7 = [];
    for ($i = 6; $i >= 0; $i--) $labels7[] = now()->subDays($i)->format('D d');
  @endphp
  new ApexCharts(document.getElementById('oc-spark'), {
    chart:{ type:'bar', height:160, toolbar:{show:false}, animations:{enabled:true,speed:600} },
    series:[{ name:'Bookings', data:@json($last7DaysBookings) }],
    colors:['#332E9E'],
    plotOptions:{ bar:{ columnWidth:'55%', borderRadius:4 } },
    xaxis:{ categories:@json($labels7), labels:{ style:{fontSize:'10px',colors:'#94A3B8'} }, axisBorder:{show:false}, axisTicks:{show:false} },
    yaxis:{ labels:{show:false} },
    grid:{ show:false },
    dataLabels:{ enabled:false },
    tooltip:{ y:{formatter:v=>v+' bookings'} }
  }).render();

});
</script>
@endsection
