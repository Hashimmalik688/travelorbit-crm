<div>
    {{-- Page Header --}}
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Refunds</h1>
        </div>
        <div class="to-page-header-right">
            <span class="badge" style="background:rgba(220,38,38,.12);color:#DC2626;font-size:0.864rem;font-weight:700;padding:6px 14px;border-radius:20px;">
                <i class="ph ph-arrows-counter-clockwise me-1"></i> {{ $this->pendingReviewCount }} In Flight
            </span>
            @if ($awaitingPayouts->isNotEmpty())
                <span class="badge" style="background:rgba(124,58,237,.12);color:#7C3AED;font-size:0.864rem;font-weight:700;padding:6px 14px;border-radius:20px;">
                    <i class="ph ph-hourglass-medium me-1"></i> {{ $awaitingPayouts->count() }} Awaiting Your Approval
                </span>
            @endif
        </div>
    </div>

    <p style="font-size:0.84rem;color:#475569;margin-bottom:16px;">
        Tracks refunds claimed from the ticket provider and which of those have since landed in Travel Orbit's
        balance — whether and how much to pass on to the customer from there is a separate call. Confirming a
        provider refund landed happens on
        <a href="{{ route('payment-charges') }}" style="color:#332E9E;font-weight:600;">Charge Requests</a>; a
        manager approves paying the customer back on the
        <a href="{{ route('refund-auth-queue') }}" style="color:#332E9E;font-weight:600;">M&amp;R Auth Queue</a>
        first, then you sign off on it below.
    </p>

    @if(session()->has('success'))
      <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('success') }}</div>
    @endif

    {{-- Totals strip --}}
    <div class="d-flex gap-3 flex-wrap" style="margin-bottom:16px;">
        <div style="flex:1;min-width:180px;background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:12px;padding:14px 18px;">
            <div style="font-size:0.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Received — Not Yet Refunded</div>
            <div style="font-size:1.32rem;font-weight:800;color:#7C3AED;">&pound;{{ number_format($this->totalReceived, 2) }}</div>
        </div>
        <div style="flex:1;min-width:180px;background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:12px;padding:14px 18px;">
            <div style="font-size:0.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Paid Out to Customer</div>
            <div style="font-size:1.32rem;font-weight:800;color:#16A34A;">&pound;{{ number_format($this->totalProcessed, 2) }}</div>
        </div>
    </div>

    {{-- ── Awaiting Your Approval — manager-approved payouts, final accounts sign-off ── --}}
    @if ($awaitingPayouts->isNotEmpty())
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="ph ph-hourglass-medium" style="color:#7C3AED;font-size:1.08rem;"></i>
            <span class="fw-bold" style="color:#0F172A;">Awaiting Your Approval</span>
        </div>
        <div class="card animate-in mb-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Booking</th>
                            <th>Agent</th>
                            <th>Approved By</th>
                            <th class="text-end">&pound; Amount</th>
                            <th>Mode</th>
                            <th style="width:190px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($awaitingPayouts as $index => $p)
                            @php $details = $p->payment_details ?? []; @endphp
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span style="width:24px;height:24px;border-radius:50%;background:rgba(124,58,237,.1);display:inline-flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:800;color:#7C3AED;">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;">
                                    <a href="{{ route('bookings.show', $p->booking) }}" style="font-weight:700;color:#1E293B;text-decoration:none;font-size:0.936rem;">
                                        #{{ $p->booking?->booking_number }}
                                    </a>
                                    <span class="d-block" style="font-size:0.768rem;color:#475569;">{{ $p->booking?->lead_name }}</span>
                                </td>
                                <td style="vertical-align:middle;font-size:0.888rem;color:#475569;">{{ $details['agent_name'] ?? $p->user?->name }}</td>
                                <td style="vertical-align:middle;font-size:0.888rem;color:#475569;">
                                    {{ \App\Models\User::find($details['manager_approved_by'] ?? null)?->name ?? '—' }}
                                </td>
                                <td class="text-end" style="vertical-align:middle;font-weight:700;font-size:0.936rem;color:#1E293B;">
                                    &pound;{{ number_format(abs($p->amount), 2) }}
                                </td>
                                <td style="vertical-align:middle;">
                                    <span style="font-size:0.768rem;font-weight:600;color:#475569;background:rgba(51,46,158,.06);padding:2px 10px;border-radius:10px;text-transform:capitalize;">
                                        {{ $details['refund_mode'] ?? '—' }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;">
                                    <div class="d-flex gap-1">
                                        <button type="button" wire:click="confirmApprovePayout({{ $p->id }})"
                                            style="font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#16A34A;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-check me-1"></i> Approve
                                        </button>
                                        <button type="button" wire:click="confirmDeclinePayout({{ $p->id }})"
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
        </div>
    @endif

    {{-- Filter bar --}}
    <div style="padding:0 0 16px;">
        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div style="font-size:0.84rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;flex-shrink:0;">Filter</div>

          <div class="d-flex align-items-center gap-2" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:4px 12px;">
            <i class="ph ph-magnifying-glass" style="font-size:0.96rem;color:#475569;"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search booking #, name..." style="border:none;outline:none;font-size:0.912rem;color:#374151;background:transparent;width:200px;">
          </div>

          <select wire:model.live="statusFilter" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:6px 14px;font-size:0.9rem;color:#374151;">
            <option value="">All Statuses</option>
            <option value="requested">Requested from Provider</option>
            <option value="received">Received — Not Yet Refunded</option>
            <option value="processed">Paid Out</option>
            <option value="rejected">Rejected</option>
          </select>

          @if($search || $statusFilter)
            <button wire:click="$set('search',''); $set('statusFilter','')"
              style="background:none;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:20px;padding:4px 12px;font-size:0.888rem;font-weight:600;cursor:pointer;">
              ✕ Clear
            </button>
          @endif
        </div>
    </div>

    {{-- All Refunds — read-only overview, links out to the right queue for whichever stage each one is at --}}
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="ph ph-list" style="color:#475569;font-size:1.08rem;"></i>
        <span class="fw-bold" style="color:#0F172A;">All Refunds</span>
    </div>
    <div class="card animate-in">
        @if($refunds->isEmpty())
            <div class="text-center py-5">
                <i class="ph ph-check-circle" style="font-size:2.5rem;color:#16A34A;"></i>
                <h5 class="mt-2" style="font-weight:700;color:#16A34A;">Nothing here</h5>
                <p style="color:#475569;font-size:0.96rem;">No refund requests match this filter.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Booker</th>
                            <th>Reason</th>
                            <th class="text-end">&pound; Amount</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th style="width:220px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($refunds as $refund)
                            @php
                              $receiptPending = in_array($refund->id, $receiptPendingIds);
                              $payoutPendingAtManager  = in_array($refund->id, $payoutPendingAtManagerIds);
                              $payoutPendingAtAccounts = in_array($refund->id, $payoutPendingAtAccountsIds);
                              $sb = match(true) {
                                  $refund->status === 'processed' => ['bg' => 'rgba(22,163,74,.12)',  'color' => '#16A34A', 'label' => 'Paid Out to Customer'],
                                  $refund->status === 'rejected'  => ['bg' => 'rgba(220,38,38,.10)',  'color' => '#DC2626', 'label' => 'Rejected'],
                                  $payoutPendingAtManager          => ['bg' => 'rgba(124,58,237,.12)', 'color' => '#7C3AED', 'label' => 'Payout to Customer — at M&R Auth'],
                                  $payoutPendingAtAccounts         => ['bg' => 'rgba(124,58,237,.12)', 'color' => '#7C3AED', 'label' => 'Payout to Customer — at Accounts'],
                                  $refund->status === 'received'  => ['bg' => 'rgba(124,58,237,.12)', 'color' => '#7C3AED', 'label' => 'Received — Not Yet Refunded'],
                                  $receiptPending                 => ['bg' => 'rgba(245,158,11,.12)', 'color' => '#B45309', 'label' => 'Requested — at Accounts'],
                                  default                          => ['bg' => 'rgba(245,158,11,.12)', 'color' => '#B45309', 'label' => 'Requested from Provider'],
                              };
                            @endphp
                            <tr>
                                <td style="vertical-align:middle;">
                                    <a href="{{ route('bookings.show', $refund->booking) }}" style="font-weight:700;color:#1E293B;text-decoration:none;font-size:0.936rem;">
                                        #{{ $refund->booking?->booking_number }}
                                    </a>
                                </td>
                                <td style="vertical-align:middle;font-size:0.888rem;color:#475569;">
                                    {{ $refund->booking?->booker_name }}
                                    <span class="d-block" style="font-size:0.756rem;color:#94A3B8;">by {{ $refund->requestedBy?->name ?? 'System' }}</span>
                                </td>
                                <td style="vertical-align:middle;font-size:0.816rem;color:#475569;max-width:260px;">
                                    {{ \Illuminate\Support\Str::limit($refund->reason, 80) }}
                                </td>
                                <td class="text-end" style="vertical-align:middle;font-weight:700;font-size:0.936rem;color:#1E293B;">
                                    &pound;{{ number_format($refund->refund_amount, 2) }}
                                </td>
                                <td style="vertical-align:middle;font-size:0.816rem;color:#475569;">
                                    {{ $refund->requested_at?->format('d M Y H:i') ?? $refund->created_at->format('d M Y H:i') }}
                                </td>
                                <td style="vertical-align:middle;">
                                    <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;padding:4px 10px;border-radius:20px;background:{{ $sb['bg'] }};color:{{ $sb['color'] }};white-space:nowrap;">
                                        {{ $sb['label'] }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;">
                                    @if($receiptPending)
                                        <a href="{{ route('payment-charges') }}" style="font-size:0.756rem;color:#7C3AED;font-weight:600;text-decoration:none;">
                                            <i class="ph ph-arrow-right"></i> Review in Charge Requests
                                        </a>
                                    @elseif($payoutPendingAtManager)
                                        <a href="{{ route('refund-auth-queue') }}" style="font-size:0.756rem;color:#7C3AED;font-weight:600;text-decoration:none;">
                                            <i class="ph ph-arrow-right"></i> Review in M&amp;R Auth Queue
                                        </a>
                                    @elseif($payoutPendingAtAccounts)
                                        <span style="font-size:0.756rem;color:#7C3AED;font-weight:600;">
                                            <i class="ph ph-arrow-up"></i> See "Awaiting Your Approval" above
                                        </span>
                                    @else
                                        <span style="font-size:0.78rem;color:#94A3B8;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2 px-3" style="border-top:1px solid rgba(51,46,158,.06);">
                <span style="font-size:0.816rem;color:#475569;">
                    Showing {{ $refunds->firstItem() }}–{{ $refunds->lastItem() }} of {{ $refunds->total() }}
                </span>
                {{ $refunds->links() }}
            </div>
        @endif
    </div>

    {{-- Approve / Decline Modal (payout, awaiting accounts) --}}
    @if($showModal)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;width:420px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.25);padding:28px 32px 24px;">
            {{-- Icon --}}
            <div style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;
                        {{ $modalAction === 'approve' ? 'background:rgba(22,163,74,.12);' : 'background:rgba(245,158,11,.12);' }}">
                <i class="ph {{ $modalAction === 'approve' ? 'ph-check-circle' : 'ph-x-circle' }}"
                   style="font-size:1.8rem;{{ $modalAction === 'approve' ? 'color:#16A34A;' : 'color:#F59E0B;' }}"></i>
            </div>

            {{-- Title --}}
            <h5 style="font-weight:800;font-size:1.2rem;color:#1E293B;text-align:center;margin-bottom:4px;">
                {{ $modalAction === 'approve' ? 'Approve Refund Payout' : 'Decline Refund Payout' }}
            </h5>
            <p style="font-size:0.864rem;color:#475569;text-align:center;margin-bottom:20px;">
                {{ $modalAction === 'approve' ? 'This completes the refund — money leaves Travel Orbit\'s balance.' : 'Enter the reason for declining this payout.' }}
            </p>

            {{-- Note box --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:0.816rem;font-weight:700;color:#475569;margin-bottom:6px;">
                    {{ $modalAction === 'approve' ? 'Note (optional)' : 'Decline Reason *' }}
                </label>
                <textarea wire:model="modalNote" rows="3"
                    placeholder="{{ $modalAction === 'approve' ? 'e.g. Bank transfer confirmed...' : 'e.g. Bank details invalid...' }}"
                    class="form-control" style="font-size:0.96rem;resize:vertical;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);padding:10px 14px;width:100%;"></textarea>
                @error('modalNote') <span style="font-size:0.78rem;color:#DC2626;margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2">
                <button type="button" wire:click="closeModal"
                    style="flex:1;padding:10px;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);background:#fff;font-size:0.912rem;font-weight:700;color:#475569;cursor:pointer;">
                    Cancel
                </button>
                <button type="button" wire:click="{{ $modalAction === 'approve' ? 'executeApprovePayout' : 'executeDeclinePayout' }}"
                    style="flex:1;padding:10px;border-radius:10px;border:none;font-size:0.912rem;font-weight:700;color:#fff;cursor:pointer;
                           {{ $modalAction === 'approve' ? 'background:#16A34A;' : 'background:#F59E0B;' }}">
                    <i class="ph {{ $modalAction === 'approve' ? 'ph-check' : 'ph-x' }} me-1"></i>
                    {{ $modalAction === 'approve' ? 'Approve' : 'Decline' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
