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
      ['label'=>'Fresh This Month',    'val'=>$freshMarginThisMonth,   'count'=>$freshCountThisMonth,   'sub'=>now()->format('F Y'),  'color'=>'c-indigo','icon'=>'ph ph-trend-up',        'ibg'=>'rgba(51,46,158,.10)',  'ic'=>'#332E9E', 'fmt'=>'gbp'],
      ['label'=>'Issued This Month',   'val'=>$issuedMarginThisMonth,  'count'=>$issuedCountThisMonth,  'sub'=>'margin issued & fully paid', 'color'=>'c-green', 'icon'=>'ph ph-check-circle',  'ibg'=>'rgba(22,163,74,.10)',  'ic'=>'#16A34A', 'fmt'=>'gbp'],
      ['label'=>'Pending This Month',  'val'=>$pendingMarginThisMonth, 'count'=>$pendingCountThisMonth, 'sub'=>'not yet issued',       'color'=>'c-amber', 'icon'=>'ph ph-clock-countdown', 'ibg'=>'rgba(217,119,6,.10)',  'ic'=>'#D97706', 'fmt'=>'gbp'],
      ['label'=>'Outstanding Balance', 'val'=>$outstandingPayments,    'count'=>$outstandingCount,      'sub'=>'across all bookings',  'color'=>'c-rose',  'icon'=>'ph ph-warning-circle',  'ibg'=>'rgba(220,38,38,.10)',  'ic'=>'#DC2626', 'fmt'=>'gbp'],
    ];
  @endphp
  @foreach ($kpis as $si => $k)
    <div class="col-md-3 oc-up d{{ $si + 1 }}">
      <div class="oc-card {{ $k['color'] }}">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div style="width:40px;height:40px;border-radius:11px;background:{{ $k['ibg'] }};display:flex;align-items:center;justify-content:center;">
            <i class="{{ $k['icon'] }}" style="font-size:1.32rem;color:{{ $k['ic'] }};"></i>
          </div>
          <span style="font-size:0.72rem;font-weight:800;background:{{ $k['ibg'] }};color:{{ $k['ic'] }};border-radius:20px;padding:2px 9px;">{{ $k['count'] }} booking{{ $k['count'] !== 1 ? 's' : '' }}</span>
        </div>
        <div class="oc-lbl">{{ $k['label'] }}</div>
        <div class="oc-val" data-target="{{ $k['val'] }}" data-fmt="{{ $k['fmt'] }}" style="font-size:1.56rem;">
          {{ $k['fmt'] === 'gbp' ? '£'.number_format($k['val'],0) : $k['val'] }}
        </div>
        <div class="oc-sub">{{ $k['sub'] }}</div>
      </div>
    </div>
  @endforeach
</div>

{{-- ══ AGENT LEADERBOARD (live) + AGENTS PERFORMANCE ══ --}}
<style>
.neo-ap-wrap { background:#EAEEF3;border-radius:20px;box-shadow:8px 8px 16px #C4CBD6,-8px -8px 16px #FFFFFF;overflow:hidden;height:100%; }
.neo-ap-hdr { padding:16px 20px 12px;display:flex;align-items:center;justify-content:space-between; }
.neo-ap-grid { display:grid;grid-template-columns:repeat(auto-fill, 108px);justify-content:start;gap:12px;padding:4px 18px 18px; }
.neo-ap-tile { border-radius:14px;background:#EAEEF3;box-shadow:4px 4px 8px #C4CBD6,-4px -4px 8px #FFFFFF;overflow:hidden; }
.neo-ap-photo { aspect-ratio:3/4;position:relative;background:linear-gradient(135deg,#4F46E5 0%,#6366F1 50%,#8B5CF6 100%);display:flex;align-items:center;justify-content:center;overflow:hidden;border:2px solid transparent; }
.neo-ap-dot { position:absolute;top:5px;right:5px;width:10px;height:10px;border-radius:50%;border:2px solid #EAEEF3; }
.neo-ap-name { font-size:0.792rem;font-weight:700;color:#1E293B;text-align:center;padding:5px 4px 1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.neo-ap-margin { font-size:0.744rem;font-weight:800;text-align:center; }
.neo-ap-count { font-size:0.648rem;font-weight:700;color:#64748B;text-align:center;padding-bottom:6px; }
</style>
<div class="row g-3">
  <div class="col-lg-7 oc-up d3">
    @livewire('selling-board')
  </div>
  <div class="col-lg-5 oc-up d3">
    <div class="neo-ap-wrap">
      <div class="neo-ap-hdr">
        <h6 class="fw-bold mb-0" style="font-size:0.912rem;color:#0F172A;">Agents Performance</h6>
        <span style="font-size:0.696rem;font-weight:800;color:#7C3AED;background:rgba(124,58,237,.10);border-radius:20px;padding:2px 10px;">{{ $performanceLabel }}</span>
      </div>
      <div class="neo-ap-grid">
        @forelse ($agentsPerformance as $ap)
          @php
            $apParts = explode(' ', $ap->name);
            $apFirstName = $apParts[0];
            $apInitials = strtoupper(mb_substr($apParts[0], 0, 1) . (count($apParts) > 1 ? mb_substr(end($apParts), 0, 1) : ''));
            $apPhotoUrl = $ap->profile_photo_path ? asset('storage/' . $ap->profile_photo_path) : null;
            $apActive = $ap->made_booking_today;
            $apColor = $apActive ? '#10B981' : '#F43F5E';
          @endphp
          <div class="neo-ap-tile" title="{{ $ap->name }} — {{ $apActive ? 'made a booking today' : 'no bookings today' }}">
            <div class="neo-ap-photo" style="border-color:{{ $apColor }};box-shadow:0 3px 10px {{ $apColor }}33;">
              @if ($apPhotoUrl)
                <img src="{{ $apPhotoUrl }}" alt="{{ $apFirstName }}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">
              @else
                <div style="font-size:1.32rem;font-weight:800;color:rgba(255,255,255,.9);letter-spacing:.04em;">{{ $apInitials }}</div>
              @endif
              <span class="neo-ap-dot" style="background:{{ $apColor }};"></span>
            </div>
            <div class="neo-ap-name">{{ $apFirstName }}</div>
            <div class="neo-ap-margin" style="color:{{ $ap->margin >= 0 ? '#16A34A' : '#DC2626' }};">£{{ number_format($ap->margin, 0) }}</div>
            <div class="neo-ap-count">{{ $ap->count }} bkg{{ $ap->count !== 1 ? 's' : '' }}</div>
          </div>
        @empty
          <div style="font-size:0.816rem;color:#94A3B8;">No agents yet.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

{{-- ══ RECENT BOOKINGS ══ --}}
<style>
.neo-rb-wrap { background:#EAEEF3;border-radius:20px;box-shadow:8px 8px 16px #C4CBD6,-8px -8px 16px #FFFFFF;overflow:hidden; }
.neo-rb-hdr { padding:18px 22px 14px;display:flex;align-items:center;justify-content:between; }
.neo-rb-table thead th { background:transparent;border:none;color:#64748B;text-transform:uppercase;font-size:0.648rem;letter-spacing:.06em;font-weight:800;padding:6px 14px 12px;white-space:nowrap; }
.neo-rb-table tbody td { border:none;border-top:1px solid rgba(255,255,255,.7);padding:11px 14px;background:transparent; }
.neo-rb-table tbody tr:hover td { background:rgba(255,255,255,.45); }
.neo-rb-badge { display:inline-block;font-size:0.684rem;font-weight:800;border-radius:8px;padding:3px 9px;box-shadow:inset 2px 2px 4px #C4CBD6,inset -2px -2px 4px #FFFFFF;white-space:nowrap; }
.neo-rb-num { border:none;border-radius:8px;padding:3px 10px;font-weight:800;box-shadow:3px 3px 6px #C4CBD6,-3px -3px 6px #FFFFFF;background:#EAEEF3;color:#332E9E;text-decoration:none;display:inline-block; }
</style>
<div class="row g-3 mt-4">
  <div class="col-12 oc-up d5">
    <div class="neo-rb-wrap">
      <div class="neo-rb-hdr">
        <h6 class="fw-bold mb-0" style="font-size:1.02rem;color:#0F172A;">Recent Bookings</h6>
        <span class="ms-auto" style="font-size:0.84rem;color:#475569;">5 most recent</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 neo-rb-table" style="font-size:0.9rem;">
          <thead>
            <tr>
              <th>Booking #</th>
              <th>Date</th>
              <th>Customer</th>
              <th>Agent</th>
              <th>Route</th>
              <th class="text-end">Margin</th>
              <th>Type</th>
              <th>Plan Type</th>
              <th>Airline</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentBookings as $rb)
              @php
                $rbRoute = ($rb->flightDetail && $rb->flightDetail->departure_airport && $rb->flightDetail->arrival_airport)
                  ? strtoupper($rb->flightDetail->departure_airport) . ' - ' . strtoupper($rb->flightDetail->arrival_airport)
                  : '—';
                $rbMargin = $rb->netMargin();
                $rbPlanLabels = ['full' => 'Full Payment', 'awaiting' => 'Payment Awaiting', 'payment_plan' => 'Payment Plan', 'dnpl' => 'DNPL'];
                $rbPlanKey = $rb->payment->booking_plan ?? '';
                $rbPlan = $rbPlanLabels[$rbPlanKey] ?? '—';
                $rbPlanColors = ['full' => '#16A34A', 'awaiting' => '#D97706', 'payment_plan' => '#0EA5E9', 'dnpl' => '#7C3AED'];
                $rbPlanColor = $rbPlanColors[$rbPlanKey] ?? '#94A3B8';
              @endphp
              <tr>
                <td><a href="{{ route('bookings.show', $rb->id) }}" class="neo-rb-num">{{ $rb->booking_number }}</a></td>
                <td style="color:#475569;">{{ $rb->created_at->format('d/m/Y') }}</td>
                <td style="color:#1E293B;font-weight:600;">{{ $rb->booker_name ?: '—' }}</td>
                <td style="color:#334155;">{{ $rb->user->name ?? '—' }}</td>
                <td style="color:#334155;">{{ $rbRoute }}</td>
                <td class="text-end fw-semibold {{ $rbMargin >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($rbMargin, 2) }}</td>
                <td>
                  <span class="neo-rb-badge" style="color:#332E9E;">{{ ucfirst($rb->booking_type ?? '—') }}</span>
                </td>
                <td>
                  <span class="neo-rb-badge" style="color:{{ $rbPlanColor }};">{{ $rbPlan }}</span>
                </td>
                <td style="color:#334155;font-weight:600;">{{ $rb->flightDetail && $rb->flightDetail->airline ? strtoupper($rb->flightDetail->airline) : '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center py-4" style="color:#475569;">No bookings yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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

// Selling Board FLIP animation — whenever the poll refreshes the component
// and an agent's card ends up somewhere else on screen (moved from Chill
// Squad to the Leaderboard, or just reordered), it glides there smoothly
// instead of just popping into place. Keyed by data-agent-id, not DOM node
// identity, so it works whether Livewire reuses or recreates the element.
//
// The single "morph" hook fires exactly once per component update, right
// before morphdom's synchronous DOM mutation pass — so positions captured
// here are a true "before" snapshot, and by the time our own
// requestAnimationFrame callback runs (scheduled from inside this same
// synchronous call), morphdom has already finished mutating the DOM,
// giving us a reliable "after" snapshot too.
document.addEventListener('livewire:init', () => {
  Livewire.hook('morph', ({ component }) => {
    const before = new Map();
    component.el.querySelectorAll('[data-agent-id]').forEach(node => {
      before.set(node.dataset.agentId, node.getBoundingClientRect());
    });

    requestAnimationFrame(() => {
      component.el.querySelectorAll('[data-agent-id]').forEach(node => {
        const old = before.get(node.dataset.agentId);
        if (!old) return;
        const now = node.getBoundingClientRect();
        const dx = old.left - now.left;
        const dy = old.top - now.top;
        if (Math.abs(dx) < 1 && Math.abs(dy) < 1) return;

        node.style.transition = 'none';
        node.style.transform = `translate(${dx}px, ${dy}px)`;
        node.style.boxShadow = '0 0 0 3px rgba(22,163,74,.35), 3px 3px 6px #C4CBD6, -3px -3px 6px #FFFFFF';
        requestAnimationFrame(() => {
          node.style.transition = 'transform 550ms cubic-bezier(.34,1.56,.64,1), box-shadow 550ms ease';
          node.style.transform = 'translate(0,0)';
          setTimeout(() => { node.style.boxShadow = ''; }, 620);
        });
      });
    });
  });
});
</script>
@endsection
