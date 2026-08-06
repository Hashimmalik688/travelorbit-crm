<div>
    {{-- Page Header --}}
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Refunds</h1>
        </div>
        <div class="to-page-header-right">
            <span class="badge" style="background:rgba(220,38,38,.12);color:#DC2626;font-size:0.864rem;font-weight:700;padding:6px 14px;border-radius:20px;">
                <i class="ph ph-arrows-counter-clockwise me-1"></i> {{ $this->pendingReviewCount }} Awaiting Payout Request
            </span>
        </div>
    </div>

    @if(session()->has('success'))
      <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('success') }}</div>
    @endif

    <p style="font-size:0.84rem;color:#475569;margin-bottom:16px;">
        This lists which bookings still owe a refund. The actual payout is queued and approved from
        <a href="{{ route('payment-charges') }}" style="color:#332E9E;font-weight:600;">Charge Requests</a> once
        someone requests it from the booking page — nothing here pays money out.
    </p>

    {{-- Totals strip --}}
    <div class="d-flex gap-3 flex-wrap" style="margin-bottom:16px;">
        <div style="flex:1;min-width:180px;background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:12px;padding:14px 18px;">
            <div style="font-size:0.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Outstanding</div>
            <div style="font-size:1.32rem;font-weight:800;color:#B45309;">&pound;{{ number_format($this->totalOutstanding, 2) }}</div>
        </div>
        <div style="flex:1;min-width:180px;background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:12px;padding:14px 18px;">
            <div style="font-size:0.75rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;">Paid Out</div>
            <div style="font-size:1.32rem;font-weight:800;color:#16A34A;">&pound;{{ number_format($this->totalProcessed, 2) }}</div>
        </div>
    </div>

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
            <option value="requested">Requested</option>
            <option value="payout_pending">Payout Requested (at Accounts)</option>
            <option value="processed">Paid Out</option>
            <option value="rejected">Declined</option>
          </select>

          @if($search || $statusFilter)
            <button wire:click="$set('search',''); $set('statusFilter','')"
              style="background:none;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:20px;padding:4px 12px;font-size:0.888rem;font-weight:600;cursor:pointer;">
              ✕ Clear
            </button>
          @endif
        </div>
    </div>

    @php
      $canManage = auth()->user()?->hasPermission('refunds.manage');
    @endphp

    {{-- List --}}
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
                              $payoutPending = in_array($refund->id, $pendingPayoutIds);
                              $sb = match(true) {
                                  $refund->status === 'processed' => ['bg' => 'rgba(22,163,74,.12)', 'color' => '#16A34A', 'label' => 'Paid Out'],
                                  $refund->status === 'rejected'  => ['bg' => 'rgba(220,38,38,.10)', 'color' => '#DC2626', 'label' => 'Declined'],
                                  $payoutPending                  => ['bg' => 'rgba(124,58,237,.12)', 'color' => '#7C3AED', 'label' => 'Payout Requested — at Accounts'],
                                  default                          => ['bg' => 'rgba(245,158,11,.12)', 'color' => '#B45309', 'label' => 'Requested'],
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
                                    @if($canManage && $refund->status === 'requested' && !$payoutPending)
                                        <button type="button" wire:click="rejectRefund({{ $refund->id }})"
                                            wire:confirm="Decline this refund request outright? No payout will ever be queued for it."
                                            style="font-size:0.72rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#DC2626;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-x me-1"></i> Decline
                                        </button>
                                    @elseif($payoutPending)
                                        <a href="{{ route('payment-charges') }}" style="font-size:0.756rem;color:#7C3AED;font-weight:600;text-decoration:none;">
                                            <i class="ph ph-arrow-right"></i> Review in Charge Requests
                                        </a>
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
</div>
