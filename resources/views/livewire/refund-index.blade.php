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
        </div>
    </div>

    <p style="font-size:0.84rem;color:#475569;margin-bottom:16px;">
        Tracks refunds claimed from the ticket provider and which of those have since landed in Travel Orbit's
        balance — whether and how much to pass on to the customer from there is a separate call. Confirming a
        provider refund landed happens on
        <a href="{{ route('payment-charges') }}" style="color:#332E9E;font-weight:600;">Charge Requests</a>; paying
        the customer back is approved on the
        <a href="{{ route('refund-auth-queue') }}" style="color:#332E9E;font-weight:600;">M&amp;R Auth Queue</a>.
        Nothing here moves money.
    </p>

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
                                    @if($receiptPending || $payoutPendingAtAccounts)
                                        <a href="{{ route('payment-charges') }}" style="font-size:0.756rem;color:#7C3AED;font-weight:600;text-decoration:none;">
                                            <i class="ph ph-arrow-right"></i> Review in Charge Requests
                                        </a>
                                    @elseif($payoutPendingAtManager)
                                        <a href="{{ route('refund-auth-queue') }}" style="font-size:0.756rem;color:#7C3AED;font-weight:600;text-decoration:none;">
                                            <i class="ph ph-arrow-right"></i> Review in M&amp;R Auth Queue
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
