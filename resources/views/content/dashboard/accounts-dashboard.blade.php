@extends('layouts/contentNavbarLayout')
@section('title', 'Accounts Hub')

@php $user = Auth::user(); @endphp

@section('content')
<style>
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.ac-up { animation:fadeUp .35s ease both }
.ac-up.d1{animation-delay:.04s}.ac-up.d2{animation-delay:.08s}.ac-up.d3{animation-delay:.12s}.ac-up.d4{animation-delay:.16s}

.ac-kpi { border-radius:16px;padding:22px;background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.8) 100%);border:1px solid rgba(255,255,255,0.5);box-shadow:0 4px 20px rgba(51,46,158,0.07),0 1px 3px rgba(0,0,0,0.04);position:relative;overflow:hidden;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);transition:transform .2s,box-shadow .2s }
.ac-kpi:hover { transform:translateY(-2px);box-shadow:0 8px 28px rgba(51,46,158,0.12) }
.ac-kpi::before { content:'';position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:16px 0 0 16px }
.ac-kpi.k-blue::before  { background:linear-gradient(180deg,#0EA5E9,#0284C7) }
.ac-kpi.k-green::before { background:linear-gradient(180deg,#16A34A,#15803D) }
.ac-kpi.k-amber::before { background:linear-gradient(180deg,#D97706,#B45309) }
.ac-kpi.k-rose::before  { background:linear-gradient(180deg,#DC2626,#B91C1C) }

.inv-row { display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:11px;background:#F8FAFF;border:1px solid rgba(51,46,158,.07);margin-bottom:8px;transition:all .15s }
.inv-row:hover { background:#EEF2FF;border-color:rgba(51,46,158,.18) }
.inv-btn { padding:4px 14px;border-radius:20px;font-size:.68rem;font-weight:700;border:none;cursor:pointer;transition:all .15s }
.inv-btn:hover { opacity:.85 }
</style>

{{-- ══ GLASSMORPHISM HEADER ══ --}}
<div class="ac-up d1 mb-4 d-flex align-items-center justify-content-between px-5 py-4"
  style="background:linear-gradient(135deg,rgba(14,165,233,0.85) 0%,rgba(6,95,150,0.78) 50%,rgba(12,74,110,0.88) 100%);border-radius:20px;position:relative;overflow:hidden;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.2);box-shadow:0 8px 32px rgba(14,165,233,0.25),inset 0 1px 0 rgba(255,255,255,0.15);">
  {{-- Decorative orbs --}}
  <div style="position:absolute;right:-40px;top:-40px;width:160px;height:160px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;left:-30px;bottom:-30px;width:100px;height:100px;border-radius:50%;background:radial-gradient(circle,rgba(22,163,74,0.12) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;right:20%;top:-15px;width:50px;height:50px;border-radius:50%;background:radial-gradient(circle,rgba(255,107,53,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  
  <div style="position:relative;z-index:1;">
    <div style="font-size:.62rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
    <h2 style="color:#fff;font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0;">Accounts Hub <span style="font-size:.78rem;font-weight:500;opacity:.55;">- {{ $user->name }}</span></h2>
    <p style="color:rgba(255,255,255,.55);font-size:.8rem;margin:4px 0 0;">Manage payments <span style="opacity:.4;">·</span> charge & decline <span style="opacity:.4;">·</span> invoice bookings</p>
  </div>
  <div class="d-flex gap-2 flex-shrink-0" style="position:relative;z-index:1;">
    <a href="{{ route('payments') }}" class="btn btn-sm fw-semibold d-flex align-items-center gap-1"
      style="background:linear-gradient(135deg,rgba(255,255,255,0.2) 0%,rgba(255,255,255,0.1) 100%);color:#fff;border:1px solid rgba(255,255,255,0.25);border-radius:10px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);font-size:.76rem;">
      <i class="ph ph-credit-card"></i> Payments
    </a>
  </div>
</div>

{{-- ══ KPI ROW ══ --}}
<div class="row g-3 mb-4">
  @foreach ([
    ['k-blue',  'ph ph-receipt',        'Ready to Invoice',  $readyToInvoice,  'Ticket in Process bookings'],
    ['k-green', 'ph ph-check-circle',   'Invoiced Today',    $invoicedToday,   today()->format('d F')],
    ['k-amber', 'ph ph-calendar-check', 'Invoiced (Month)',  $invoicedMonth,   now()->format('F Y')],
    ['k-rose',  'ph ph-clock-countdown','Outstanding (£)',   number_format($outstandingTotal,0), 'balance remaining'],
  ] as $si => [$k,$ic,$lb,$vl,$sb])
    <div class="col-md-3 ac-up" style="animation-delay:{{ 0.06+$si*0.06 }}s">
      <div class="ac-kpi {{ $k }}">
        <div style="width:38px;height:38px;border-radius:10px;background:rgba(14,165,233,.08);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
          <i class="{{ $ic }}" style="font-size:1.05rem;color:#0EA5E9;"></i>
        </div>
        <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94A3B8;margin-bottom:2px;">{{ $lb }}</div>
        <div style="font-size:1.8rem;font-weight:800;letter-spacing:-.03em;color:#0F172A;line-height:1;">{{ is_string($vl) ? '£'.$vl : $vl }}</div>
        <div style="font-size:.7rem;color:#64748B;margin-top:3px;">{{ $sb }}</div>
      </div>
    </div>
  @endforeach
</div>

{{-- ══ ACTION LIST ══ --}}
<div class="ac-up d3" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:18px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);">
  <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <div>
      <h6 class="fw-bold mb-0" style="font-size:.88rem;color:#0F172A;">Action Required</h6>
      <div style="font-size:.7rem;color:#94A3B8;margin-top:2px;">Bookings awaiting payment action or invoice</div>
    </div>
    <span style="font-size:.7rem;font-weight:600;background:linear-gradient(135deg,rgba(14,165,233,0.15) 0%,rgba(14,165,233,0.08) 100%);color:#0369A1;padding:4px 12px;border-radius:20px;backdrop-filter:blur(4px);">{{ $recentBookings->count() }} bookings</span>
  </div>

  <div class="px-4 py-3">
    @forelse ($recentBookings as $bk)
      @php
        $stColors = \App\Models\Booking::STATUS_COLORS[$bk->booking_status] ?? ['bg'=>'rgba(148,163,184,.10)','text'=>'#64748B','badge_bg'=>'rgba(148,163,184,.12)','badge_color'=>'#64748B'];
        $stLabel  = \App\Models\Booking::STATUS_LABELS[$bk->booking_status] ?? ucfirst($bk->booking_status);
      @endphp
      <div class="inv-row">
        <div style="width:34px;height:34px;border-radius:9px;background:rgba(14,165,233,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="ph ph-airplane" style="font-size:.9rem;color:#0EA5E9;"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-semibold" style="font-size:.78rem;color:#1E293B;">{{ $bk->booking_number }}</span>
            <span style="font-size:.6rem;padding:1px 8px;border-radius:20px;font-weight:700;background:{{ $stColors['badge_bg'] ?? $stColors['bg'] }};color:{{ $stColors['badge_color'] ?? $stColors['text'] }};">{{ $stLabel }}</span>
          </div>
          <div style="font-size:.7rem;color:#64748B;">
            {{ $bk->booker_first_name }} {{ $bk->booker_last_name }}
            @if($bk->user) · <span style="color:#94A3B8;">{{ $bk->user->name }}</span>@endif
          </div>
        </div>
        <div class="flex-shrink-0 d-flex gap-2 align-items-center">
          <span style="font-size:.65rem;color:#94A3B8;">{{ $bk->updated_at->format('d M') }}</span>
          @if ($bk->booking_status === 'ticket_in_process')
            @can('invoice', $bk)
              <form method="POST" action="{{ route('bookings.invoice', $bk) }}" style="display:inline;" onsubmit="return confirm('Invoice Booking #{{ $bk->booking_number }}?')">
                @csrf
                <button type="submit" class="inv-btn" style="background:#16A34A;color:#fff;">Invoice</button>
              </form>
            @endcan
          @endif
          <a href="{{ route('bookings.show', $bk) }}" class="inv-btn" style="background:rgba(51,46,158,.08);color:#332E9E;text-decoration:none;">View</a>
        </div>
      </div>
    @empty
      <div class="text-center py-5" style="color:#C4C9D4;font-size:.76rem;">
        <i class="ph ph-check-circle" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.3;"></i>
        No bookings require action right now.
      </div>
    @endforelse
  </div>
</div>
@endsection
