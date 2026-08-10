<div>
    {{-- Page Header --}}
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>M&amp;R Auth Queue</h1>
        </div>
        <div class="to-page-header-right">
            <span class="badge" style="background:rgba(220,38,38,.12);color:#DC2626;font-size:0.864rem;font-weight:700;padding:6px 14px;border-radius:20px;">
                <i class="ph ph-arrows-counter-clockwise me-1"></i> {{ $refundRequests->total() }} Refund{{ $refundRequests->total() === 1 ? '' : 's' }}
            </span>
            <span class="badge" style="background:rgba(22,163,74,.12);color:#16A34A;font-size:0.864rem;font-weight:700;padding:6px 14px;border-radius:20px;">
                <i class="ph ph-percent me-1"></i> {{ $marginClaims->total() }} Margin Claim{{ $marginClaims->total() === 1 ? '' : 's' }}
            </span>
        </div>
    </div>

    <p style="font-size:0.84rem;color:#475569;margin-bottom:16px;">
        Two separate decisions from the same refund request. <strong>Refund Requests</strong>: approve (you can lower
        the amount actually paid) or decline the payout — approving forwards it to accounts for the final sign-off.
        <strong>Margin Claims</strong>: release or hold the margin kept back on the difference — independent of
        whatever happens to the refund itself.
    </p>

    @if(session()->has('success'))
      <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('success') }}</div>
    @endif

    {{-- Filter bar --}}
    <div style="padding:0 0 16px;">
        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div style="font-size:0.84rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;flex-shrink:0;">Filter</div>

          <div class="d-flex align-items-center gap-2" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:4px 12px;">
            <i class="ph ph-magnifying-glass" style="font-size:0.96rem;color:#475569;"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search booking #, name, email..." style="border:none;outline:none;font-size:0.912rem;color:#374151;background:transparent;width:200px;">
          </div>

          <div class="d-flex align-items-center gap-2" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:4px 12px;">
            <i class="ph ph-calendar" style="font-size:0.96rem;color:#475569;"></i>
            <input type="date" wire:model.live="dateFrom" style="border:none;outline:none;font-size:0.912rem;color:#374151;background:transparent;width:110px;">
            <span style="color:#64748B;font-size:0.9rem;">→</span>
            <input type="date" wire:model.live="dateTo" style="border:none;outline:none;font-size:0.912rem;color:#374151;background:transparent;width:110px;">
          </div>

          @if($search || $dateFrom || $dateTo)
            <button wire:click="$set('search',''); $set('dateFrom',''); $set('dateTo','')"
              style="background:none;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:20px;padding:4px 12px;font-size:0.888rem;font-weight:600;cursor:pointer;">
              ✕ Clear
            </button>
          @endif
        </div>
    </div>

    {{-- ── Refund Requests ── --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="ph ph-arrows-counter-clockwise" style="color:#DC2626;font-size:1.08rem;"></i>
        <span class="fw-bold" style="color:#0F172A;">Refund Requests</span>
    </div>
    <div class="card animate-in mb-4">
        @if($refundRequests->isEmpty())
            <div class="text-center py-5">
                <i class="ph ph-check-circle" style="font-size:2.5rem;color:#16A34A;"></i>
                <h5 class="mt-2" style="font-weight:700;color:#16A34A;">Queue is clear</h5>
                <p style="color:#475569;font-size:0.96rem;">No pending refund requests to review.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Booking</th>
                            <th>Agent</th>
                            <th class="text-end">Received</th>
                            <th class="text-end">To Client</th>
                            <th>Mode</th>
                            <th>Requested</th>
                            <th style="width:190px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($refundRequests as $index => $r)
                            @php $details = $r->payment_details ?? []; @endphp
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span style="width:24px;height:24px;border-radius:50%;background:rgba(220,38,38,.1);display:inline-flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:800;color:#DC2626;">
                                        {{ $refundRequests->firstItem() + $index }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;">
                                    <a href="{{ route('bookings.show', $r->booking) }}" style="font-weight:700;color:#1E293B;text-decoration:none;font-size:0.936rem;">
                                        #{{ $r->booking?->booking_number }}
                                    </a>
                                    <span class="d-block" style="font-size:0.768rem;color:#475569;">{{ $r->booking?->lead_name }}</span>
                                </td>
                                <td style="vertical-align:middle;font-size:0.888rem;color:#475569;">{{ $details['agent_name'] ?? $r->user?->name }}</td>
                                <td class="text-end" style="vertical-align:middle;font-size:0.888rem;color:#475569;">
                                    &pound;{{ number_format((float) ($details['refund_received_amount'] ?? 0), 2) }}
                                </td>
                                <td class="text-end" style="vertical-align:middle;font-weight:700;font-size:0.936rem;color:#1E293B;">
                                    &pound;{{ number_format(abs($r->amount), 2) }}
                                </td>
                                <td style="vertical-align:middle;">
                                    <span style="font-size:0.768rem;font-weight:600;color:#475569;background:rgba(51,46,158,.06);padding:2px 10px;border-radius:10px;text-transform:capitalize;">
                                        {{ $details['refund_mode'] ?? '—' }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;font-size:0.816rem;color:#475569;">
                                    {{ $r->created_at->format('d M Y H:i') }}
                                </td>
                                <td style="vertical-align:middle;">
                                    <div class="d-flex gap-1">
                                        <button type="button" wire:click="confirmApproveRefund({{ $r->id }})"
                                            style="font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#16A34A;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-check me-1"></i> Approve
                                        </button>
                                        <button type="button" wire:click="confirmDeclineRefund({{ $r->id }})"
                                            style="font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#F59E0B;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-x me-1"></i> Decline
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2 px-3" style="border-top:1px solid rgba(51,46,158,.06);">
                <span style="font-size:0.816rem;color:#475569;">
                    Showing {{ $refundRequests->firstItem() }}–{{ $refundRequests->lastItem() }} of {{ $refundRequests->total() }}
                </span>
                {{ $refundRequests->links() }}
            </div>
        @endif
    </div>

    {{-- ── Margin Claims ── --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="ph ph-percent" style="color:#16A34A;font-size:1.08rem;"></i>
        <span class="fw-bold" style="color:#0F172A;">Margin Claims</span>
    </div>
    <div class="card animate-in">
        @if($marginClaims->isEmpty())
            <div class="text-center py-5">
                <i class="ph ph-check-circle" style="font-size:2.5rem;color:#16A34A;"></i>
                <h5 class="mt-2" style="font-weight:700;color:#16A34A;">Queue is clear</h5>
                <p style="color:#475569;font-size:0.96rem;">No pending margin claims to review.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Booking</th>
                            <th>Agent</th>
                            <th class="text-end">Amount</th>
                            <th>Reason</th>
                            <th>Requested</th>
                            <th style="width:190px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($marginClaims as $index => $c)
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span style="width:24px;height:24px;border-radius:50%;background:rgba(22,163,74,.1);display:inline-flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:800;color:#16A34A;">
                                        {{ $marginClaims->firstItem() + $index }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;">
                                    <a href="{{ route('bookings.show', $c->booking) }}" style="font-weight:700;color:#1E293B;text-decoration:none;font-size:0.936rem;">
                                        #{{ $c->booking?->booking_number }}
                                    </a>
                                </td>
                                <td style="vertical-align:middle;font-size:0.888rem;color:#475569;">{{ $c->user?->name }}</td>
                                <td class="text-end" style="vertical-align:middle;font-weight:700;font-size:0.936rem;color:#16A34A;">
                                    &pound;{{ number_format($c->amount, 2) }}
                                </td>
                                <td style="vertical-align:middle;font-size:0.816rem;color:#475569;max-width:280px;">
                                    {{ \Illuminate\Support\Str::limit($c->reason, 70) }}
                                </td>
                                <td style="vertical-align:middle;font-size:0.816rem;color:#475569;">
                                    {{ $c->created_at->format('d M Y H:i') }}
                                </td>
                                <td style="vertical-align:middle;">
                                    <div class="d-flex gap-1">
                                        <button type="button" wire:click="approveMargin({{ $c->id }})" wire:confirm="Release this margin claim? It will count toward this agent's performance report."
                                            style="font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#16A34A;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-check me-1"></i> Release
                                        </button>
                                        <button type="button" wire:click="holdMargin({{ $c->id }})" wire:confirm="Hold this margin claim? It will not be credited."
                                            style="font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#F59E0B;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-pause me-1"></i> Hold
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2 px-3" style="border-top:1px solid rgba(51,46,158,.06);">
                <span style="font-size:0.816rem;color:#475569;">
                    Showing {{ $marginClaims->firstItem() }}–{{ $marginClaims->lastItem() }} of {{ $marginClaims->total() }}
                </span>
                {{ $marginClaims->links() }}
            </div>
        @endif
    </div>

    {{-- Refund Request Approve / Decline Modal --}}
    @if($showRefundModal)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;width:420px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.25);padding:28px 32px 24px;">
            {{-- Icon --}}
            <div style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;
                        {{ $refundModalAction === 'approve' ? 'background:rgba(22,163,74,.12);' : 'background:rgba(245,158,11,.12);' }}">
                <i class="ph {{ $refundModalAction === 'approve' ? 'ph-check-circle' : 'ph-x-circle' }}"
                   style="font-size:1.8rem;{{ $refundModalAction === 'approve' ? 'color:#16A34A;' : 'color:#F59E0B;' }}"></i>
            </div>

            {{-- Title --}}
            <h5 style="font-weight:800;font-size:1.2rem;color:#1E293B;text-align:center;margin-bottom:4px;">
                {{ $refundModalAction === 'approve' ? 'Approve Refund' : 'Decline Refund' }}
            </h5>
            <p style="font-size:0.864rem;color:#475569;text-align:center;margin-bottom:20px;">
                {{ $refundModalAction === 'approve' ? 'Confirm — or adjust — the amount actually paid, then forward to accounts.' : 'Enter the reason for declining this refund.' }}
            </p>

            @if($refundModalAction === 'approve')
                @php
                    $ph = \App\Models\BookingPaymentHistory::find($refundModalPaymentId);
                    $received = (float) ($ph->payment_details['refund_received_amount'] ?? 0);
                @endphp

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:6px;">Refund Received</label>
                        <div style="padding:8px 10px;font-size:0.936rem;font-weight:700;color:#1E293B;background:#F8FAFF;border-radius:8px;border:1px solid rgba(51,46,158,.08);">
                            &pound;{{ number_format($received, 2) }}
                        </div>
                    </div>
                    <div class="col-6">
                        <label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:6px;">Refund to Client *</label>
                        <input type="number" wire:model="approveAmount" step="0.01" min="0.01" max="{{ $received ?: '' }}"
                            style="width:100%;padding:8px 10px;font-size:0.984rem;font-weight:700;border-radius:8px;border:1.5px solid rgba(220,38,38,.3);">
                        @error('approveAmount') <span style="font-size:0.75rem;color:#DC2626;">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            {{-- Note box --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:0.816rem;font-weight:700;color:#475569;margin-bottom:6px;">
                    {{ $refundModalAction === 'approve' ? 'Approval Note (optional)' : 'Decline Reason *' }}
                </label>
                <textarea wire:model="refundModalNote" rows="3"
                    placeholder="{{ $refundModalAction === 'approve' ? 'e.g. Confirmed with customer...' : 'e.g. Amount disputed, missing bank details...' }}"
                    class="form-control" style="font-size:0.96rem;resize:vertical;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);padding:10px 14px;width:100%;"></textarea>
                @error('refundModalNote') <span style="font-size:0.78rem;color:#DC2626;margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2">
                <button type="button" wire:click="closeRefundModal"
                    style="flex:1;padding:10px;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);background:#fff;font-size:0.912rem;font-weight:700;color:#475569;cursor:pointer;">
                    Cancel
                </button>
                <button type="button" wire:click="{{ $refundModalAction === 'approve' ? 'executeApproveRefund' : 'executeDeclineRefund' }}"
                    style="flex:1;padding:10px;border-radius:10px;border:none;font-size:0.912rem;font-weight:700;color:#fff;cursor:pointer;
                           {{ $refundModalAction === 'approve' ? 'background:#16A34A;' : 'background:#F59E0B;' }}">
                    <i class="ph {{ $refundModalAction === 'approve' ? 'ph-check' : 'ph-x' }} me-1"></i>
                    {{ $refundModalAction === 'approve' ? 'Approve & Forward' : 'Decline' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
