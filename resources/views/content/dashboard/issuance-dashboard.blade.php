@extends('layouts/contentNavbarLayout')
@section('title', 'Issuance Queue')

@php $user = Auth::user(); @endphp

@section('content')
<style>
@keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
.oc-up{ animation:fadeUp .4s ease both }
.oc-up.d1{animation-delay:.04s}.oc-up.d2{animation-delay:.08s}.oc-up.d3{animation-delay:.12s}
.oc-up.d4{animation-delay:.16s}.oc-up.d5{animation-delay:.20s}.oc-up.d6{animation-delay:.24s}
.oc-card{ border-radius:14px;padding:22px 24px;background:#FFFFFF;border:1px solid var(--to-border);box-shadow:0 1px 2px rgba(15,23,42,0.04);transition:box-shadow .12s ease;position:relative;overflow:hidden }
.oc-card:hover{ box-shadow:0 1px 3px rgba(15,23,42,.06),0 1px 2px rgba(15,23,42,.04) }
.oc-card::after{ content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:14px 14px 0 0 }
.oc-card.c-amber::after {background:linear-gradient(90deg,#D97706,#FBBF24)}
.oc-card.c-green::after{background:linear-gradient(90deg,#16A34A,#4ADE80)}
.oc-card.c-indigo::after{background:linear-gradient(90deg,#332E9E,#6366F1)}
.oc-val{ font-size:2rem;font-weight:800;letter-spacing:-.03em;color:#0F172A;line-height:1 }
.oc-lbl{ font-size:0.744rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;margin-bottom:4px }
.oc-sub{ font-size:0.852rem;color:#475569;margin-top:4px }
.is-btn { padding:5px 14px;border-radius:20px;font-size:0.816rem;font-weight:700;border:none;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:5px }
.is-btn:hover { opacity:.85 }
.is-row{ display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:12px;background:#fff;border:1px solid rgba(51,46,158,.06);margin-bottom:7px;transition:background .12s,box-shadow .12s }
.is-row:hover{ background:#FFFBEB;border-color:rgba(217,119,6,.15);box-shadow:0 2px 8px rgba(217,119,6,.06) }
.is-row.urgent{ border-left:3px solid #DC2626;background:linear-gradient(90deg,rgba(220,38,38,.03),#fff) }
.is-row.urgent:hover{ background:rgba(220,38,38,.04);border-color:rgba(220,38,38,.15) }
</style>

{{-- ══ HEADER ══ --}}
<div class="oc-up d1 mb-4" style="background:linear-gradient(135deg,#D97706 0%,#FBBF24 50%,#F59E0B 100%);border-radius:16px;padding:24px 28px;position:relative;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,0.06);">
  <div style="position:relative;z-index:1;">
    <div style="font-size:0.744rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
    <h2 style="color:#fff;font-size:1.8rem;font-weight:800;letter-spacing:-.03em;margin:0;">Issuance Queue</h2>
    <p style="color:rgba(255,255,255,.55);font-size:0.984rem;margin:4px 0 0;">Process bookings waiting for ticket issuance</p>
  </div>
</div>

{{-- ══ STATS CARDS ══ --}}
<div class="row g-3 mb-4">
  <div class="col-md-6 oc-up d1">
    <div class="oc-card c-amber">
      <div class="d-flex align-items-start justify-content-between mb-3">
        <div style="width:40px;height:40px;border-radius:11px;background:rgba(217,119,6,.10);display:flex;align-items:center;justify-content:center;">
          <i class="ph ph-ticket" style="font-size:1.32rem;color:#D97706;"></i>
        </div>
      </div>
      <div class="oc-lbl">In Issuance Queue</div>
      <div class="oc-val" style="color:#B45309;">{{ $inQueue }}</div>
      <div class="oc-sub">awaiting ticket order</div>
    </div>
  </div>
  <div class="col-md-6 oc-up d2">
    <div class="oc-card c-green">
      <div class="d-flex align-items-start justify-content-between mb-3">
        <div style="width:40px;height:40px;border-radius:11px;background:rgba(22,163,74,.10);display:flex;align-items:center;justify-content:center;">
          <i class="ph ph-check-circle" style="font-size:1.32rem;color:#16A34A;"></i>
        </div>
      </div>
      <div class="oc-lbl">Processed Today</div>
      <div class="oc-val" style="color:#15803D;">{{ $doneToday }}</div>
      <div class="oc-sub">{{ today()->format('d F Y') }}</div>
    </div>
  </div>
</div>

{{-- ══ QUEUE LIST ══ --}}
<div class="oc-up d2">
  @if($queueBookings->isEmpty())
    <div class="text-center py-5" style="color:#475569;">
      <div style="width:48px;height:48px;border-radius:12px;background:rgba(22,163,74,.08);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
        <i class="ph ph-check-circle" style="font-size:1.56rem;color:#16A34A;"></i>
      </div>
      <div style="font-size:0.984rem;font-weight:600;color:#475569;">Queue is clear</div>
      <div style="font-size:0.864rem;color:#475569;">No bookings pending issuance</div>
    </div>
  @else
    @foreach ($queueBookings as $bk)
      @php
        $queuedAt = $bk->issuance_queued_at ?? $bk->updated_at;
        $hoursAgo = $queuedAt->diffInHours(now());
        $urgent   = $hoursAgo > 4;
        $paxCount = $bk->passengers->count();
      @endphp
      <div class="is-row{{ $urgent ? ' urgent' : '' }}">
        <div style="width:34px;height:34px;border-radius:10px;background:{{ $urgent ? 'rgba(220,38,38,.10)' : 'rgba(217,119,6,.10)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="ph {{ $urgent ? 'ph-warning-circle' : 'ph-ticket' }}" style="font-size:1.2rem;color:{{ $urgent ? '#DC2626' : '#D97706' }};"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold" style="font-size:0.984rem;color:#1E293B;">{{ $bk->booking_number }}</span>
            @if($urgent)
              <span style="font-size:0.66rem;background:rgba(220,38,38,.10);color:#DC2626;padding:1px 7px;border-radius:20px;font-weight:700;">Urgent</span>
            @endif
          </div>
          <div style="font-size:0.864rem;color:#475569;margin-top:1px;">
            {{ $bk->booker_first_name }} {{ $bk->booker_last_name }}
            @if($paxCount) · {{ $paxCount }} pax @endif
            @if($bk->booking_type) · {{ ucfirst($bk->booking_type) }} @endif
          </div>
          <div style="font-size:0.768rem;color:#475569;margin-top:1px;">
            Queued {{ $queuedAt->diffForHumans() }} · {{ $bk->user?->name }}
          </div>
        </div>
        <div class="d-flex gap-1 flex-shrink-0">
          <button type="button" class="is-btn process-btn" style="background:rgba(14,165,233,.12);color:#0369A1;"
            data-booking-id="{{ $bk->id }}"
            data-booking-ref="{{ $bk->booking_number }}"
            data-route="{{ route('bookings.ticket-in-process', $bk) }}">
            <i class="ph ph-airplane-takeoff"></i> Process
          </button>
          <button type="button" class="is-btn reject-btn" style="background:rgba(220,38,38,.08);color:#DC2626;"
            data-booking-id="{{ $bk->id }}"
            data-booking-ref="{{ $bk->booking_number }}"
            data-route="{{ route('bookings.remove-issuance', $bk) }}">
            <i class="ph ph-x-circle"></i> Reject
          </button>
          <a href="{{ route('bookings.show', $bk) }}" class="is-btn" style="background:rgba(51,46,158,.06);color:#374151;">
            <i class="ph ph-eye"></i>
          </a>
        </div>
      </div>
    @endforeach
  @endif
</div>

{{-- ══ PROCESS MODAL ══ --}}
<div id="processModal" style="display:none;position:fixed;inset:0;z-index:1055;align-items:center;justify-content:center;background:rgba(15,23,42,0.3);">
  <div style="background:#fff;border-radius:20px;width:400px;max-width:92vw;box-shadow:0 25px 80px rgba(0,0,0,0.22),0 0 0 1px rgba(0,0,0,0.04);overflow:hidden;animation:processModalIn .25s ease;">
    <form method="POST" action="" id="processForm">
      @csrf
      <div style="background:linear-gradient(135deg,#0EA5E9 0%,#0284C7 100%);padding:20px 24px;position:relative;">
        <div style="position:absolute;right:-30px;top:-30px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.08);pointer-events:none;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;">
            <i class="ph ph-airplane-takeoff" style="font-size:1.32rem;color:#fff;"></i>
          </div>
          <div>
            <div style="font-size:1.02rem;font-weight:700;color:#fff;">Process Booking</div>
            <div style="font-size:0.768rem;color:rgba(255,255,255,0.65);">Move to Ticket in Process</div>
          </div>
        </div>
        <button type="button" onclick="document.getElementById('processModal').style.display='none'" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.15);border:none;color:#fff;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.02rem;transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
      </div>
      <div style="padding:20px 24px;">
        <div style="background:#F8FAFC;border-radius:12px;padding:14px 16px;margin-bottom:16px;">
          <div style="font-size:0.816rem;color:#475569;text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:4px;">Booking Reference</div>
          <div style="font-size:1.08rem;font-weight:700;color:#0F172A;" id="processBookingRef"></div>
        </div>
        <div style="margin-bottom:14px;">
          <label style="font-size:0.864rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Processed Date <span style="color:#DC2626;">*</span></label>
          <input type="date" name="processed_date" id="processDate" class="form-control" style="font-size:0.936rem;border-radius:10px;border-color:#64748B;padding:9px 14px;" required>
        </div>
        <div style="margin-bottom:4px;">
          <label style="font-size:0.864rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Reason <span style="color:#DC2626;">*</span></label>
          <textarea name="reason" class="form-control" rows="3" style="font-size:0.936rem;border-radius:10px;border-color:#64748B;resize:vertical;padding:10px 14px;" placeholder="Why is this booking being processed?" required></textarea>
        </div>
        <label style="display:flex;align-items:center;gap:8px;margin-top:14px;padding:10px 12px;background:rgba(14,165,233,.06);border:1px solid rgba(14,165,233,.2);border-radius:10px;cursor:pointer;">
          <input type="checkbox" name="send_ticket_order" value="1" style="width:16px;height:16px;accent-color:#0EA5E9;">
          <span style="font-size:0.864rem;font-weight:600;color:#0369A1;">Send Ticket Order</span>
        </label>
      </div>
      <div style="padding:14px 24px;border-top:1px solid #F1F5F9;display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="document.getElementById('processModal').style.display='none'" style="background:#F1F5F9;color:#475569;border:none;border-radius:10px;padding:8px 18px;font-size:0.888rem;font-weight:600;cursor:pointer;">Cancel</button>
        <button type="submit" style="background:linear-gradient(135deg,#0EA5E9,#0284C7);color:#fff;border:none;border-radius:10px;padding:8px 24px;font-size:0.888rem;font-weight:600;cursor:pointer;box-shadow:0 4px 14px rgba(14,165,233,0.3);">
          <i class="ph ph-check" style="margin-right:5px;"></i> Confirm Process
        </button>
      </div>
    </form>
  </div>
</div>

<style>
@keyframes processModalIn {
  from { opacity:0; transform:scale(.94) translateY(8px); }
  to { opacity:1; transform:scale(1) translateY(0); }
}
</style>

{{-- ══ REJECT MODAL ══ --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <form method="POST" action="" id="rejectForm">
      @csrf
      <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 24px 80px rgba(0,0,0,.25);">
        <div class="modal-header" style="border-bottom:1px solid rgba(51,46,158,.06);padding:18px 22px;">
          <h6 class="fw-bold mb-0" style="font-size:0.984rem;display:flex;align-items:center;gap:8px;">
            <span style="width:28px;height:28px;border-radius:8px;background:rgba(220,38,38,.10);display:flex;align-items:center;justify-content:center;">
              <i class="ph ph-x-circle" style="font-size:1.02rem;color:#DC2626;"></i>
            </span>
            Reject Booking
          </h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:18px 22px;">
          <p style="font-size:0.9rem;color:#475569;margin-bottom:12px;">
            Reject <strong id="rejectBookingRef" style="color:#1E293B;"></strong> from the issuance queue?
            <br><span style="color:#475569;">Booking will be returned to <strong>Pending</strong>.</span>
          </p>
          <div class="mb-2" style="font-size:0.816rem;font-weight:600;color:#374151;">Rejection Reason <span style="color:#DC2626;">*</span></div>
          <textarea name="reason" class="form-control" rows="3" style="font-size:0.936rem;border-radius:10px;resize:vertical;" placeholder="Why is this being rejected?" required></textarea>
        </div>
        <div class="modal-footer" style="border-top:1px solid rgba(51,46,158,.06);padding:14px 22px;">
          <button type="button" class="btn btn-sm" style="background:rgba(51,46,158,.06);color:#475569;border-radius:10px;font-size:0.864rem;font-weight:600;" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm" style="background:#DC2626;color:#fff;border-radius:10px;font-size:0.864rem;font-weight:600;border:none;">
            <i class="ph ph-check"></i> Confirm Reject
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL SCRIPT ══ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const processModalEl = document.getElementById('processModal');
  document.querySelectorAll('.process-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
      document.getElementById('processForm').action = btn.getAttribute('data-route');
      document.getElementById('processBookingRef').textContent = btn.getAttribute('data-booking-ref');
      document.getElementById('processDate').value = new Date().toISOString().split('T')[0];
      processModalEl.style.display = 'flex';
    });
  });

  processModalEl.addEventListener('click', function(e) {
    if (e.target === processModalEl) processModalEl.style.display = 'none';
  });

  const rejectModalEl = document.getElementById('rejectModal');
  let rejectModal;
  if (rejectModalEl) {
    rejectModal = new bootstrap.Modal(rejectModalEl, { backdrop: false, keyboard: false });
    rejectModalEl.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      document.getElementById('rejectForm').action = btn.getAttribute('data-route');
      document.getElementById('rejectBookingRef').textContent = btn.getAttribute('data-booking-ref');
    });
    document.querySelectorAll('.reject-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        rejectModal.show(btn);
      });
    });
  }
});
</script>
@endsection