<div>
<style>
.sc-wrap   { background:#FFFFFF;border:1px solid var(--to-border);border-radius:14px;box-shadow:0 1px 2px rgba(15,23,42,0.04);overflow:hidden; }
.sc-hdr    { padding:14px 18px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;border-bottom:1px solid var(--to-border); }
.sc-row    { display:flex;align-items:center;gap:12px;padding:11px 18px;border-bottom:1px solid var(--to-border);flex-wrap:wrap; }
.sc-row:last-child { border-bottom:none; }
.sc-badge  { font-size:0.72rem;font-weight:700;padding:2px 10px;border-radius:20px;white-space:nowrap; }
.sc-panel  { background:var(--to-page);border-top:1px solid var(--to-border);padding:14px 18px; }
.sc-label  { font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#475569;margin-bottom:4px;display:block; }
.sc-empty  { padding:26px 18px;color:#64748B;font-size:0.87rem;text-align:center; }
</style>

  @if (session('success'))
    <div class="mb-3" style="background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.28);color:#15803D;border-radius:12px;padding:10px 14px;font-size:0.87rem;font-weight:600;">
      <i class="ph ph-check-circle me-1"></i>{{ session('success') }}
    </div>
  @endif

  <div class="sc-wrap">
    <div class="sc-hdr">
      <div>
        <h5 class="fw-bold mb-0" style="font-size:1.02rem;color:#0F172A;">Status Change</h5>
        <div style="font-size:0.78rem;color:#64748B;">Every booking — set any status.</div>
      </div>
      <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
        <input type="text" wire:model.live.debounce.400ms="search" class="form-control form-control-sm"
          placeholder="Search booking no. or customer…" style="width:230px;">
        <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:190px;">
          <option value="">All statuses</option>
          @foreach ($filterStatuses as $s)
            <option value="{{ $s }}">{{ $statuses[$s] ?? $s }}</option>
          @endforeach
        </select>
      </div>
    </div>

    @forelse ($bookings as $b)
      @php $col = \App\Models\Booking::STATUS_COLORS[$b->booking_status] ?? ['badge_bg'=>'rgba(148,163,184,.12)','badge_color'=>'#64748B']; @endphp
      <div class="sc-row">
        <div style="min-width:120px;">
          <a href="{{ route('bookings.show', $b) }}" style="font-weight:800;font-size:0.9rem;color:#332E9E;text-decoration:none;">#{{ $b->booking_number }}</a>
          <div style="font-size:0.72rem;color:#64748B;">{{ $b->updated_at?->format('d M Y, H:i') }}</div>
        </div>
        <div class="flex-grow-1 min-width-0">
          <div style="font-size:0.87rem;font-weight:600;color:#1E293B;">{{ trim($b->booker_first_name . ' ' . $b->booker_last_name) ?: '—' }}</div>
          <div style="font-size:0.75rem;color:#64748B;">{{ $b->booker_email ?: '—' }}</div>
        </div>
        <span class="sc-badge" style="background:{{ $col['badge_bg'] }};color:{{ $col['badge_color'] }};">
          {{ $statuses[$b->booking_status] ?? $b->booking_status }}
        </span>
        <button type="button" wire:click="startEdit({{ $b->id }})" class="btn btn-sm"
          style="background:#332E9E;color:#fff;border:none;border-radius:20px;padding:4px 14px;font-size:0.8rem;font-weight:600;">
          Change
        </button>
      </div>

      @if ($editingId === $b->id)
        <div class="sc-panel">
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label class="sc-label">New status</label>
              <select wire:model.live="newStatus" class="form-select form-select-sm">
                @foreach (\App\Services\BookingStatusService::selectableStatuses() as $s)
                  <option value="{{ $s }}">{{ $statuses[$s] ?? $s }}</option>
                @endforeach
              </select>
              @error('newStatus') <div style="font-size:0.75rem;color:#DC2626;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            {{-- Only the two still-owing dispositions need a balance due date. --}}
            @if (in_array($newStatus, \App\Services\BookingStatusService::NEEDS_LAST_PAYMENT_DATE, true))
              <div class="col-md-3">
                <label class="sc-label">Last payment date</label>
                <input type="date" wire:model="lastPaymentDate" class="form-control form-control-sm">
                @error('lastPaymentDate') <div style="font-size:0.75rem;color:#DC2626;margin-top:4px;">{{ $message }}</div> @enderror
              </div>
            @endif

            <div class="col-md">
              <label class="sc-label">Reason <span style="font-weight:500;text-transform:none;letter-spacing:0;">(optional — shown in the booking's feed)</span></label>
              <input type="text" wire:model="reason" class="form-control form-control-sm" maxlength="500" placeholder="Why is this being changed?">
              @error('reason') <div style="font-size:0.75rem;color:#DC2626;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-auto d-flex gap-2">
              <button type="button" wire:click="cancelEdit" class="btn btn-sm"
                style="background:none;border:1px solid var(--to-border);color:#475569;border-radius:20px;padding:4px 14px;font-size:0.8rem;font-weight:600;">Cancel</button>
              <button type="button" wire:click="save" wire:loading.attr="disabled" class="btn btn-sm"
                style="background:#16A34A;color:#fff;border:none;border-radius:20px;padding:4px 16px;font-size:0.8rem;font-weight:600;">
                <span wire:loading.remove wire:target="save">Update status</span>
                <span wire:loading wire:target="save">Saving…</span>
              </button>
            </div>
          </div>
          <div style="font-size:0.72rem;color:#64748B;margin-top:9px;">
            <i class="ph ph-info me-1"></i>The matching date is stamped automatically and the change is written to the booking's activity feed and the audit log.
          </div>
        </div>
      @endif
    @empty
      <div class="sc-empty">No bookings found.</div>
    @endforelse
  </div>

  @if ($bookings->hasPages())
    <div class="mt-3">{{ $bookings->links() }}</div>
  @endif
</div>
