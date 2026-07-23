@extends('layouts/contentNavbarLayout')
@section('title', 'Accounts Hub')

@php $user = Auth::user(); @endphp

@section('content')
<style>
@keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
.ac-up { animation:fadeUp .35s ease both }
.ac-up.d1{animation-delay:.04s}.ac-up.d2{animation-delay:.08s}.ac-up.d3{animation-delay:.12s}.ac-up.d4{animation-delay:.16s}.ac-up.d5{animation-delay:.20s}

.inv-row { display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:11px;background:#F8FAFF;border:1px solid rgba(51,46,158,.07);margin-bottom:8px;transition:all .15s }
.inv-row:hover { background:#EEF2FF;border-color:rgba(51,46,158,.18) }
.inv-btn { padding:4px 14px;border-radius:20px;font-size:0.816rem;font-weight:700;border:none;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:4px }
.inv-btn:hover { opacity:.85 }
</style>

{{-- ══ HEADER ══ --}}
<div class="ac-up d1 mb-4 d-flex align-items-center justify-content-between px-5 py-4"
  style="background:linear-gradient(135deg,rgba(14,165,233,0.85) 0%,rgba(6,95,150,0.78) 50%,rgba(12,74,110,0.88) 100%);border-radius:20px;position:relative;overflow:hidden;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,0.2);box-shadow:0 8px 32px rgba(14,165,233,0.25),inset 0 1px 0 rgba(255,255,255,0.15);">
  <div style="position:absolute;right:-40px;top:-40px;width:160px;height:160px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.1) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;left:-30px;bottom:-30px;width:100px;height:100px;border-radius:50%;background:radial-gradient(circle,rgba(22,163,74,0.12) 0%,transparent 70%);pointer-events:none;"></div>
  <div style="position:absolute;right:20%;top:-15px;width:50px;height:50px;border-radius:50%;background:radial-gradient(circle,rgba(255,107,53,0.1) 0%,transparent 70%);pointer-events:none;"></div>

  <div style="position:relative;z-index:1;">
    <div style="font-size:0.744rem;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;">{{ now()->format('l, d F Y') }}</div>
    <h2 style="color:#fff;font-size:1.68rem;font-weight:800;letter-spacing:-.03em;margin:0;">Accounts Hub <span style="font-size:0.936rem;font-weight:500;opacity:.55;">- {{ $user->name }}</span></h2>
    <p style="color:rgba(255,255,255,.55);font-size:0.96rem;margin:4px 0 0;">Issue tickets <span style="opacity:.4;">·</span> invoice bookings</p>
  </div>
</div>

{{-- ══ SECTION 1: TICKET IN PROCESS (Issue Queue) ══ --}}
<div class="ac-up d2 mb-4" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:18px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);">
  <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <div>
      <h6 class="fw-bold mb-0" style="font-size:1.056rem;color:#0F172A;">Ticket in Process <span style="font-size:0.84rem;font-weight:500;color:#475569;">— Issue Disposition</span></h6>
      <div style="font-size:0.84rem;color:#475569;margin-top:2px;">Mark each processed ticket with its payment disposition</div>
    </div>
    <span style="font-size:0.84rem;font-weight:600;background:linear-gradient(135deg,rgba(217,119,6,0.15) 0%,rgba(217,119,6,0.08) 100%);color:#B45309;padding:4px 12px;border-radius:20px;backdrop-filter:blur(4px);">{{ $issueQueueBookings->count() }} bookings</span>
  </div>

  <div class="px-4 py-3">
    @forelse ($issueQueueBookings as $bk)
      @php
        $stColors = \App\Models\Booking::STATUS_COLORS[$bk->booking_status] ?? ['bg'=>'rgba(148,163,184,.10)','text'=>'#64748B','badge_bg'=>'rgba(148,163,184,.12)','badge_color'=>'#64748B'];
        $stLabel  = \App\Models\Booking::STATUS_LABELS[$bk->booking_status] ?? ucfirst($bk->booking_status);
      @endphp
      <div class="inv-row">
        <div style="width:34px;height:34px;border-radius:9px;background:rgba(217,119,6,.10);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="ph ph-airplane" style="font-size:1.08rem;color:#D97706;"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-semibold" style="font-size:0.936rem;color:#1E293B;">{{ $bk->booking_number }}</span>
            <span style="font-size:0.72rem;padding:1px 8px;border-radius:20px;font-weight:700;background:{{ $stColors['badge_bg'] ?? $stColors['bg'] }};color:{{ $stColors['badge_color'] ?? $stColors['text'] }};">{{ $stLabel }}</span>
          </div>
          <div style="font-size:0.84rem;color:#475569;">
            {{ $bk->booker_first_name }} {{ $bk->booker_last_name }}
            @if($bk->user) · <span style="color:#475569;">{{ $bk->user->name }}</span>@endif
          </div>
        </div>
        <div class="flex-shrink-0 d-flex gap-2 align-items-center">
          <span style="font-size:0.78rem;color:#475569;">{{ $bk->updated_at->format('d M') }}</span>
          @if(auth()->user()->hasPermission('payments.issue'))
          <button type="button" class="inv-btn issue-btn" style="background:linear-gradient(135deg,#D97706,#B45309);color:#fff;"
            data-booking-id="{{ $bk->id }}"
            data-booking-ref="{{ $bk->booking_number }}"
            data-route="{{ route('bookings.issue', $bk) }}">
            <i class="ph ph-ticket"></i> Issue
          </button>
          @endif
          <a href="{{ route('bookings.show', $bk) }}" class="inv-btn" style="background:rgba(51,46,158,.08);color:#332E9E;">View</a>
        </div>
      </div>
    @empty
      <div class="text-center py-4" style="color:#475569;font-size:0.912rem;">
        <i class="ph ph-check-circle" style="font-size:1.8rem;display:block;margin-bottom:6px;opacity:.3;"></i>
        No tickets in process.
      </div>
    @endforelse
  </div>
</div>

{{-- ══ SECTION 2: READY TO INVOICE ══ --}}
<div class="ac-up d3 mb-4" style="background:linear-gradient(135deg,rgba(255,255,255,0.92) 0%,rgba(255,255,255,0.82) 100%);border-radius:18px;border:1px solid rgba(255,255,255,0.5);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);box-shadow:0 4px 24px rgba(51,46,158,0.08);">
  <div class="px-4 pt-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <div>
      <h6 class="fw-bold mb-0" style="font-size:1.056rem;color:#0F172A;">Ready to Invoice <span style="font-size:0.84rem;font-weight:500;color:#475569;">— Issued & Fully Paid</span></h6>
      <div style="font-size:0.84rem;color:#475569;margin-top:2px;">Invoice issued bookings to finalise</div>
    </div>
    <span style="font-size:0.84rem;font-weight:600;background:linear-gradient(135deg,rgba(22,163,74,0.15) 0%,rgba(22,163,74,0.08) 100%);color:#15803D;padding:4px 12px;border-radius:20px;backdrop-filter:blur(4px);">{{ $invoiceQueueBookings->count() }} bookings</span>
  </div>

  <div class="px-4 py-3">
    @forelse ($invoiceQueueBookings as $bk)
      @php
        $stColors = \App\Models\Booking::STATUS_COLORS[$bk->booking_status] ?? ['bg'=>'rgba(148,163,184,.10)','text'=>'#64748B','badge_bg'=>'rgba(148,163,184,.12)','badge_color'=>'#64748B'];
        $stLabel  = \App\Models\Booking::STATUS_LABELS[$bk->booking_status] ?? ucfirst($bk->booking_status);
      @endphp
      <div class="inv-row">
        <div style="width:34px;height:34px;border-radius:9px;background:rgba(22,163,74,.10);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="ph ph-check-circle" style="font-size:1.08rem;color:#16A34A;"></i>
        </div>
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-semibold" style="font-size:0.936rem;color:#1E293B;">{{ $bk->booking_number }}</span>
            <span style="font-size:0.72rem;padding:1px 8px;border-radius:20px;font-weight:700;background:{{ $stColors['badge_bg'] ?? $stColors['bg'] }};color:{{ $stColors['badge_color'] ?? $stColors['text'] }};">{{ $stLabel }}</span>
          </div>
          <div style="font-size:0.84rem;color:#475569;">
            {{ $bk->booker_first_name }} {{ $bk->booker_last_name }}
            @if($bk->user) · <span style="color:#475569;">{{ $bk->user->name }}</span>@endif
          </div>
        </div>
        <div class="flex-shrink-0 d-flex gap-2 align-items-center">
          <span style="font-size:0.78rem;color:#475569;">{{ $bk->updated_at->format('d M') }}</span>
          @can('invoice', $bk)
            <form method="POST" action="{{ route('bookings.invoice', $bk) }}" style="display:inline;" onsubmit="return confirm('Invoice Booking #{{ $bk->booking_number }}?')">
              @csrf
              <button type="submit" class="inv-btn" style="background:#16A34A;color:#fff;">
                <i class="ph ph-receipt"></i> Invoice
              </button>
            </form>
          @endcan
          <a href="{{ route('bookings.show', $bk) }}" class="inv-btn" style="background:rgba(51,46,158,.08);color:#332E9E;">View</a>
        </div>
      </div>
    @empty
      <div class="text-center py-4" style="color:#475569;font-size:0.912rem;">
        <i class="ph ph-check-circle" style="font-size:1.8rem;display:block;margin-bottom:6px;opacity:.3;"></i>
        No bookings ready to invoice.
      </div>
    @endforelse
  </div>
</div>

{{-- ══ ISSUE MODAL (3 dispositions) ══ --}}
<div id="issueModal" style="display:none;position:fixed;inset:0;z-index:1055;align-items:center;justify-content:center;background:rgba(15,23,42,0.3);backdrop-filter:blur(2px);-webkit-backdrop-filter:blur(2px);">
  <div style="background:#fff;border-radius:20px;width:440px;max-width:92vw;max-height:88vh;box-shadow:0 25px 80px rgba(0,0,0,0.22),0 0 0 1px rgba(0,0,0,0.04);overflow-y:auto;overflow-x:hidden;animation:issueModalIn .25s ease;display:flex;flex-direction:column;">
    <form method="POST" action="" id="issueForm">
      @csrf
      <input type="hidden" name="payment_type" id="issuePaymentType" value="">

      <div style="background:linear-gradient(135deg,#D97706 0%,#B45309 100%);padding:20px 24px;position:relative;">
        <div style="position:absolute;right:-30px;top:-30px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.08);pointer-events:none;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;">
            <i class="ph ph-ticket" style="font-size:1.32rem;color:#fff;"></i>
          </div>
          <div>
            <div style="font-size:1.02rem;font-weight:700;color:#fff;">Issue Ticket</div>
            <div style="font-size:0.768rem;color:rgba(255,255,255,0.65);">Select payment disposition</div>
          </div>
        </div>
        <button type="button" onclick="document.getElementById('issueModal').style.display='none'" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.15);border:none;color:#fff;width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.02rem;transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
      </div>
      <div style="padding:20px 24px;">
        <div style="background:#F8FAFC;border-radius:12px;padding:14px 16px;margin-bottom:18px;">
          <div style="font-size:0.816rem;color:#475569;text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:4px;">Booking Reference</div>
          <div style="font-size:1.08rem;font-weight:700;color:#0F172A;" id="issueBookingRef"></div>
        </div>

        <div style="margin-bottom:18px;">
          <label style="font-size:0.816rem;color:#475569;text-transform:uppercase;letter-spacing:.05em;font-weight:600;display:block;margin-bottom:6px;">Issue Date</label>
          <input type="date" name="issue_date" id="issueDate" required
            style="width:100%;padding:10px 12px;border:1px solid #E2E8F0;border-radius:10px;font-size:0.984rem;color:#0F172A;box-sizing:border-box;">
        </div>

        <div style="font-size:0.864rem;font-weight:600;color:#374151;margin-bottom:12px;">Payment Disposition</div>

        <div class="d-flex flex-column gap-3">
          <button type="button" class="issue-disp-btn" data-payment="issued"
            style="width:100%;background:rgba(22,163,74,.06);border:2px solid rgba(22,163,74,.15);border-radius:12px;padding:14px 16px;text-align:left;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:12px;"
            onmouseover="this.style.background='rgba(22,163,74,.10)';this.style.borderColor='rgba(22,163,74,.3)'"
            onmouseout="this.style.background='rgba(22,163,74,.06)';this.style.borderColor='rgba(22,163,74,.15)'">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(22,163,74,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ph ph-check-circle" style="font-size:1.32rem;color:#16A34A;"></i>
            </div>
            <div>
              <div style="font-size:0.936rem;font-weight:700;color:#15803D;">Issued — Full Payment</div>
              <div style="font-size:0.78rem;color:#475569;">Full payment received. Ready to invoice.</div>
            </div>
          </button>

          <button type="button" class="issue-disp-btn" data-payment="issued_payment_plan"
            style="width:100%;background:rgba(217,119,6,.06);border:2px solid rgba(217,119,6,.15);border-radius:12px;padding:14px 16px;text-align:left;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:12px;"
            onmouseover="this.style.background='rgba(217,119,6,.10)';this.style.borderColor='rgba(217,119,6,.3)'"
            onmouseout="this.style.background='rgba(217,119,6,.06)';this.style.borderColor='rgba(217,119,6,.15)'">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(217,119,6,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ph ph-calendar-check" style="font-size:1.32rem;color:#D97706;"></i>
            </div>
            <div>
              <div style="font-size:0.936rem;font-weight:700;color:#B45309;">Issued — Payment Plan</div>
              <div style="font-size:0.78rem;color:#475569;">Issued on a scheduled payment plan. Not ready to invoice.</div>
            </div>
          </button>

          <button type="button" class="issue-disp-btn" data-payment="issued_payment_awaiting"
            style="width:100%;background:rgba(14,165,233,.06);border:2px solid rgba(14,165,233,.15);border-radius:12px;padding:14px 16px;text-align:left;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:12px;"
            onmouseover="this.style.background='rgba(14,165,233,.10)';this.style.borderColor='rgba(14,165,233,.3)'"
            onmouseout="this.style.background='rgba(14,165,233,.06)';this.style.borderColor='rgba(14,165,233,.15)'">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(14,165,233,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="ph ph-clock-countdown" style="font-size:1.32rem;color:#0EA5E9;"></i>
            </div>
            <div>
              <div style="font-size:0.936rem;font-weight:700;color:#0369A1;">Issued — Payment Awaiting</div>
              <div style="font-size:0.78rem;color:#475569;">Issued, awaiting payment. Not ready to invoice.</div>
            </div>
          </button>
        </div>

        <div id="issueLastPaymentWrap" style="display:none;margin-top:14px;">
          <label style="font-size:0.816rem;color:#475569;text-transform:uppercase;letter-spacing:.05em;font-weight:600;display:block;margin-bottom:6px;">Last Payment Date</label>
          <input type="date" name="last_payment_date" id="issueLastPaymentDate"
            style="width:100%;padding:10px 12px;border:1px solid #E2E8F0;border-radius:10px;font-size:0.984rem;color:#0F172A;box-sizing:border-box;">
        </div>

        <div id="issueConfirmWrap" style="display:none;margin-top:18px;">
          <button type="submit" id="issueConfirmBtn"
            style="width:100%;background:linear-gradient(135deg,#D97706 0%,#B45309 100%);color:#fff;border:none;border-radius:12px;padding:13px;font-size:0.984rem;font-weight:700;cursor:pointer;transition:filter .15s;"
            onmouseover="this.style.filter='brightness(1.05)'" onmouseout="this.style.filter='none'">
            Confirm &amp; Issue
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<style>
@keyframes issueModalIn {
  from { opacity:0; transform:scale(.94) translateY(8px); }
  to { opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const issueModalEl = document.getElementById('issueModal');
  const activeClass = 'issue-disp-btn-active';
  const confirmWrap = document.getElementById('issueConfirmWrap');
  const lastPaymentWrap = document.getElementById('issueLastPaymentWrap');
  const issueDateInput = document.getElementById('issueDate');
  const lastPaymentInput = document.getElementById('issueLastPaymentDate');

  function todayStr() {
    const d = new Date();
    const off = d.getTimezoneOffset();
    return new Date(d.getTime() - off * 60000).toISOString().slice(0, 10);
  }

  function resetDispositions() {
    document.querySelectorAll('.issue-disp-btn').forEach(function(b) {
      b.style.borderWidth = '2px';
      b.style.borderStyle = 'solid';
      b.classList.remove(activeClass);
    });
  }

  document.querySelectorAll('.issue-btn').forEach(function(btn) {
    btn.addEventListener('click', function(event) {
      document.getElementById('issueForm').action = btn.getAttribute('data-route');
      document.getElementById('issueBookingRef').textContent = btn.getAttribute('data-booking-ref');
      document.getElementById('issuePaymentType').value = '';
      resetDispositions();
      // reset date fields
      issueDateInput.value = todayStr();
      lastPaymentWrap.style.display = 'none';
      lastPaymentInput.value = '';
      lastPaymentInput.required = false;
      confirmWrap.style.display = 'none';
      issueModalEl.style.display = 'flex';
    });
  });

  document.querySelectorAll('.issue-disp-btn').forEach(function(dispBtn) {
    dispBtn.addEventListener('click', function() {
      resetDispositions();
      dispBtn.style.borderWidth = '3px';
      dispBtn.classList.add(activeClass);

      const paymentType = dispBtn.getAttribute('data-payment');
      document.getElementById('issuePaymentType').value = paymentType;

      // Last payment date only required for plan / awaiting dispositions
      const needsLastPayment = (paymentType === 'issued_payment_plan' || paymentType === 'issued_payment_awaiting');
      lastPaymentWrap.style.display = needsLastPayment ? 'block' : 'none';
      lastPaymentInput.required = needsLastPayment;
      if (!needsLastPayment) lastPaymentInput.value = '';

      confirmWrap.style.display = 'block';
    });
  });

  issueModalEl.addEventListener('click', function(e) {
    if (e.target === issueModalEl) issueModalEl.style.display = 'none';
  });
});
</script>
@endsection
