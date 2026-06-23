@extends('layouts/contentNavbarLayout')
@section('title', 'Issuance Hub')

@php $user = Auth::user(); @endphp

@section('content')
<style>
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
@keyframes pulse  { 0%,100%{box-shadow:0 0 0 0 rgba(217,119,6,.4)} 50%{box-shadow:0 0 0 8px rgba(217,119,6,0)} }
.is-up { animation:fadeUp .35s ease both }
.is-up.d1{animation-delay:.04s}.is-up.d2{animation-delay:.08s}.is-up.d3{animation-delay:.12s}

.queue-row { display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-radius:14px;background:linear-gradient(135deg,rgba(255,255,255,0.8) 0%,rgba(248,250,252,0.7) 100%);border:1px solid rgba(51,46,158,0.06);margin-bottom:10px;transition:all .15s;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px) }
.queue-row:hover { border-color:rgba(217,119,6,.3);background:linear-gradient(135deg,rgba(255,251,235,0.9) 0%,rgba(254,243,199,0.8) 100%); }
.queue-row.in-proc { background:linear-gradient(135deg,rgba(240,253,244,0.9) 0%,rgba(220,252,231,0.8) 100%);border-color:rgba(22,163,74,.2) }
.queue-row.in-proc:hover { background:linear-gradient(135deg,rgba(220,252,231,0.95) 0%,rgba(187,247,208,0.9) 100%); }

.is-btn { padding:5px 14px;border-radius:20px;font-size:.68rem;font-weight:700;border:none;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:4px }
.is-btn:hover { opacity:.85 }
</style>

{{-- ══ GLASSMORPHISM HEADER ══ --}}
<div class="is-up d1 mb-4 d-flex align-items-center justify-content-between px-5 py-4"
  style="background:linear-gradient(135deg,rgba(15,52,96,0.9) 0%,rgba(22,33,62,0.85) 50%,rgba(26,26,46,0.92) 100%);border-radius:20px;position:relative;overflow:hidden;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.15);box-shadow:0 8px 32px rgba(15,52,96,0.3),inset 0 1px 0 rgba(255,255,255,0.1);">
  {{-- Decorative orbs --}}
  <div style="position:absolute;right:-30px;top:-30px;width:140px;height:140px;border-radius:50%;background:radial-gradient(circle,rgba(245,158,11,0.08) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;left:-25px;bottom:-25px;width:90px;height:90px;border-radius:50%;background:radial-gradient(circle,rgba(14,165,233,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;right:30%;top:-10px;width:40px;height:40px;border-radius:50%;background:radial-gradient(circle,rgba(22,163,74,0.08) 0%,transparent 70%);pointer-events:none;"></div>
  
  <div style="position:relative;z-index:1;">
    <div style="font-size:.62rem;color:rgba(255,255,255,.45);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
    <h2 style="color:#fff;font-size:1.4rem;font-weight:800;letter-spacing:-.03em;margin:0;">Issuance Hub <span style="font-size:.78rem;font-weight:500;opacity:.5;">- {{ $user->name }}</span></h2>
    <p style="color:rgba(255,255,255,.5);font-size:.8rem;margin:4px 0 0;">Manage the ticket issuance queue <span style="opacity:.4;">·</span> process & move bookings through the workflow</p>
  </div>
</div>

{{-- ══ BIG STATUS COUNTS - Glassmorphism ══ --}}
<div class="row g-3 mb-4">
  <div class="col-md-4 is-up d1">
    <div style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.8) 100%);border-radius:16px;border:1px solid rgba(255,255,255,0.5);padding:26px;text-align:center;box-shadow:0 4px 20px rgba(217,119,6,0.1);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);transition:transform .2s,box-shadow .2s;">
      <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,rgba(217,119,6,0.15) 0%,rgba(217,119,6,0.08) 100%);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(217,119,6,0.15);{{ $inQueue>0 ? 'animation:pulse 2s infinite;' : '' }}">
        <i class="ph ph-ticket" style="font-size:1.5rem;color:#D97706;"></i>
      </div>
      <div style="font-size:3rem;font-weight:800;color:#B45309;letter-spacing:-.04em;line-height:1;">{{ $inQueue }}</div>
      <div style="font-size:.74rem;font-weight:700;color:#D97706;text-transform:uppercase;letter-spacing:.06em;margin-top:6px;">In Issuance Queue</div>
      <div style="font-size:.68rem;color:#94A3B8;margin-top:3px;">awaiting ticket order</div>
    </div>
  </div>
  <div class="col-md-4 is-up d2">
    <div style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.8) 100%);border-radius:16px;border:1px solid rgba(255,255,255,0.5);padding:26px;text-align:center;box-shadow:0 4px 20px rgba(14,165,233,0.1);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);transition:transform .2s,box-shadow .2s;">
      <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,rgba(14,165,233,0.15) 0%,rgba(14,165,233,0.08) 100%);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(14,165,233,0.15);">
        <i class="ph ph-airplane-takeoff" style="font-size:1.5rem;color:#0EA5E9;"></i>
      </div>
      <div style="font-size:3rem;font-weight:800;color:#0369A1;letter-spacing:-.04em;line-height:1;">{{ $inProcess }}</div>
      <div style="font-size:.74rem;font-weight:700;color:#0EA5E9;text-transform:uppercase;letter-spacing:.06em;margin-top:6px;">Ticket in Process</div>
      <div style="font-size:.68rem;color:#94A3B8;margin-top:3px;">order sent to airline</div>
    </div>
  </div>
  <div class="col-md-4 is-up d3">
    <div style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.8) 100%);border-radius:16px;border:1px solid rgba(255,255,255,0.5);padding:26px;text-align:center;box-shadow:0 4px 20px rgba(22,163,74,0.1);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);transition:transform .2s,box-shadow .2s;">
      <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,rgba(22,163,74,0.15) 0%,rgba(22,163,74,0.08) 100%);margin:0 auto 14px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(22,163,74,0.15);">
        <i class="ph ph-check-circle" style="font-size:1.5rem;color:#16A34A;"></i>
      </div>
      <div style="font-size:3rem;font-weight:800;color:#15803D;letter-spacing:-.04em;line-height:1;">{{ $doneToday }}</div>
      <div style="font-size:.74rem;font-weight:700;color:#16A34A;text-transform:uppercase;letter-spacing:.06em;margin-top:6px;">Processed Today</div>
      <div style="font-size:.68rem;color:#94A3B8;margin-top:3px;">{{ today()->format('d F Y') }}</div>
    </div>
  </div>
</div>

{{-- ══ QUEUE WORKLIST - Glassmorphism ══ --}}
<div class="is-up d2" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:18px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);">
  <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <div>
      <h6 class="fw-bold mb-0" style="font-size:.88rem;color:#0F172A;">Active Queue</h6>
      <div style="font-size:.7rem;color:#94A3B8;margin-top:2px;">Work through these in order - oldest first</div>
    </div>
    <div class="d-flex gap-2 align-items-center" style="font-size:.65rem;">
      <span class="d-flex align-items-center gap-1" style="color:#D97706;font-weight:600;"><span style="width:8px;height:8px;border-radius:50%;background:#FBBF24;display:inline-block;"></span>Queue</span>
      <span class="d-flex align-items-center gap-1" style="color:#0EA5E9;font-weight:600;"><span style="width:8px;height:8px;border-radius:50%;background:#38BDF8;display:inline-block;"></span>In Process</span>
    </div>
  </div>

  <div class="px-4 py-3">
    @forelse ($queueBookings as $bk)
      @php
        $isProc   = $bk->booking_status === 'ticket_in_process';
        $queuedAt = $bk->issuance_queued_at ?? $bk->updated_at;
        $hoursAgo = $queuedAt->diffInHours(now());
        $urgent   = !$isProc && $hoursAgo > 4;
        $paxCount = $bk->passengers->count();
      @endphp
      <div class="queue-row {{ $isProc ? 'in-proc' : '' }}">
        {{-- Status dot --}}
        <div style="width:36px;height:36px;border-radius:10px;background:{{ $isProc ? 'rgba(22,163,74,.10)' : ($urgent ? 'rgba(220,38,38,.10)' : 'rgba(217,119,6,.10)') }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="ph {{ $isProc ? 'ph-airplane-takeoff' : ($urgent ? 'ph-warning' : 'ph-ticket') }}" style="font-size:1rem;color:{{ $isProc ? '#16A34A' : ($urgent ? '#DC2626' : '#D97706') }};"></i>
        </div>

        {{-- Booking info --}}
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold" style="font-size:.8rem;color:#1E293B;">{{ $bk->booking_number }}</span>
            @if ($urgent)
              <span style="font-size:.6rem;background:rgba(220,38,38,.10);color:#DC2626;padding:1px 8px;border-radius:20px;font-weight:700;">Urgent</span>
            @endif
            <span style="font-size:.6rem;padding:1px 8px;border-radius:20px;font-weight:700;background:{{ $isProc ? 'rgba(22,163,74,.10)' : 'rgba(217,119,6,.10)' }};color:{{ $isProc ? '#16A34A' : '#D97706' }};">
              {{ $isProc ? 'Ticket in Process' : 'Issuance Queue' }}
            </span>
          </div>
          <div style="font-size:.7rem;color:#64748B;margin-top:2px;">
            {{ $bk->booker_first_name }} {{ $bk->booker_last_name }}
            @if($paxCount) · {{ $paxCount }} pax @endif
            @if($bk->booking_type) · {{ ucfirst($bk->booking_type) }} @endif
            <span style="color:#9CA3AF;"> · {{ $bk->user?->name }}</span>
          </div>
          <div style="font-size:.65rem;color:#94A3B8;margin-top:2px;">
            Queued {{ $queuedAt->diffForHumans() }}
            @if($hoursAgo > 0) ({{ $hoursAgo }}h ago) @endif
          </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-column gap-1 flex-shrink-0">
          @if (!$isProc)
            @can('markTicketInProcess', $bk)
              <form method="POST" action="{{ route('bookings.ticket-in-process', $bk) }}" onsubmit="return confirm('Mark as Ticket in Process?')">
                @csrf
                <button type="submit" class="is-btn" style="background:#0EA5E9;color:#fff;">
                  <i class="ph ph-airplane-takeoff"></i> Process
                </button>
              </form>
            @endcan
            @can('removeFromIssuanceQueue', $bk)
              <form method="POST" action="{{ route('bookings.remove-issuance', $bk) }}" onsubmit="return confirm('Remove from queue and restore to Pending?')">
                @csrf
                <button type="submit" class="is-btn" style="background:rgba(220,38,38,.08);color:#DC2626;">
                  <i class="ph ph-arrow-u-up-left"></i> Return
                </button>
              </form>
            @endcan
          @else
            @can('restoreToPending', $bk)
              <form method="POST" action="{{ route('bookings.restore-pending', $bk) }}" onsubmit="return confirm('Restore this booking to Pending?')">
                @csrf
                <button type="submit" class="is-btn" style="background:rgba(51,46,158,.08);color:#332E9E;">
                  <i class="ph ph-arrow-counter-clockwise"></i> Restore
                </button>
              </form>
            @endcan
          @endif
          <a href="{{ route('bookings.show', $bk) }}" class="is-btn" style="background:rgba(51,46,158,.06);color:#374151;text-align:center;">
            <i class="ph ph-eye"></i> View
          </a>
        </div>
      </div>
    @empty
      <div class="text-center py-6" style="color:#C4C9D4;padding:40px 0;">
        <i class="ph ph-check-circle" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3;color:#16A34A;"></i>
        <div style="font-size:.82rem;font-weight:600;color:#64748B;">Queue is clear</div>
        <div style="font-size:.72rem;color:#94A3B8;">No bookings pending issuance</div>
      </div>
    @endforelse
  </div>
</div>
@endsection
