<div>
    {{-- Page Header --}}
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Payment Charge Requests</h1>
        </div>
        <div class="to-page-header-right">
            <span class="badge" style="background:rgba(216,63,135,.12);color:#D83F87;font-size:.72rem;font-weight:700;padding:6px 14px;border-radius:20px;">
                <i class="ph ph-queue me-1"></i> {{ $chargeRequests->total() }} Pending
            </span>
        </div>
    </div>

    @if(session()->has('success'))
      <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:.82rem;">{{ session('success') }}</div>
    @endif

    {{-- Filter bar --}}
    <div style="padding:0 0 16px;">
        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div style="font-size:.7rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.06em;flex-shrink:0;">Filter</div>

          <div class="d-flex align-items-center gap-2" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:4px 12px;">
            <i class="ph ph-magnifying-glass" style="font-size:.8rem;color:#94A3B8;"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search booking #, name, email..." style="border:none;outline:none;font-size:.76rem;color:#374151;background:transparent;width:200px;">
          </div>

          <div class="d-flex align-items-center gap-2" style="background:#fff;border:1.5px solid rgba(51,46,158,.15);border-radius:20px;padding:4px 12px;">
            <i class="ph ph-calendar" style="font-size:.8rem;color:#94A3B8;"></i>
            <input type="date" wire:model.live="dateFrom" style="border:none;outline:none;font-size:.76rem;color:#374151;background:transparent;width:110px;">
            <span style="color:#CBD5E1;font-size:.75rem;">→</span>
            <input type="date" wire:model.live="dateTo" style="border:none;outline:none;font-size:.76rem;color:#374151;background:transparent;width:110px;">
          </div>

          @if($search || $dateFrom || $dateTo)
            <button wire:click="$set('search',''); $set('dateFrom',''); $set('dateTo','')"
              style="background:none;border:1.5px solid rgba(51,46,158,.15);color:#64748B;border-radius:20px;padding:4px 12px;font-size:.74rem;font-weight:600;cursor:pointer;">
              ✕ Clear
            </button>
          @endif
        </div>
    </div>

    {{-- Queue List --}}
    <div class="card animate-in">
        @if($chargeRequests->isEmpty())
            <div class="text-center py-5">
                <i class="ph ph-check-circle" style="font-size:2.5rem;color:#16A34A;"></i>
                <h5 class="mt-2" style="font-weight:700;color:#16A34A;">Queue is clear</h5>
                <p style="color:#94A3B8;font-size:.8rem;">No pending charge requests to review.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Booking</th>
                            <th>Booker</th>
                            <th>Method</th>
                            <th class="text-end">&pound; Amount</th>
                            <th>Requested</th>
                            <th>Card Details</th>
                            <th style="width:180px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chargeRequests as $index => $cr)
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span style="width:24px;height:24px;border-radius:50%;background:rgba(216,63,135,.1);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;color:#D83F87;">
                                        {{ $chargeRequests->firstItem() + $index }}
                                    </span>
                                </td>
                                <td style="vertical-align:middle;">
                                    <a href="{{ route('bookings.show', $cr->booking) }}" style="font-weight:700;color:#1E293B;text-decoration:none;font-size:.78rem;">
                                        #{{ $cr->booking?->booking_number }}
                                    </a>
                                    <span class="d-block" style="font-size:.64rem;color:#94A3B8;">{{ $cr->booking?->lead_name }}</span>
                                </td>
                                <td style="vertical-align:middle;font-size:.74rem;color:#475569;">{{ $cr->user?->name }}</td>
                                <td style="vertical-align:middle;">
                                    <span style="font-size:.68rem;font-weight:600;color:#475569;background:rgba(51,46,158,.06);padding:2px 10px;border-radius:10px;">
                                        {{ ucfirst(str_replace('_', ' ', $cr->payment_method ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="text-end" style="vertical-align:middle;font-weight:700;font-size:.78rem;color:#1E293B;">
                                    &pound;{{ number_format($cr->amount, 2) }}
                                </td>
                                <td style="vertical-align:middle;font-size:.68rem;color:#94A3B8;">
                                    {{ $cr->created_at->format('d M Y H:i') }}
                                </td>
                                <td style="vertical-align:middle;font-size:.64rem;color:#64748B;">
                                    @php $details = $cr->payment_details ?? []; @endphp
                                    @if(!empty($details['card_holder_name']))
                                        {{ $details['card_holder_name'] }} · ••••{{ $details['card_number'] ?? '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="vertical-align:middle;">
                                    <div class="d-flex gap-1">
                                        <button type="button" wire:click="confirmApprove({{ $cr->id }})"
                                            style="font-size:.6rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#16A34A;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-check me-1"></i> Approve
                                        </button>
                                        <button type="button" wire:click="confirmReject({{ $cr->id }})"
                                            style="font-size:.6rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#F59E0B;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-x me-1"></i> Reject
                                        </button>
                                        <button type="button" wire:click="deletePayment({{ $cr->id }})" wire:confirm="Delete this payment charge? This cannot be undone."
                                            style="font-size:.6rem;font-weight:700;padding:4px 10px;border-radius:8px;background:#DC2626;color:#fff;border:none;cursor:pointer;white-space:nowrap;">
                                            <i class="ph ph-trash me-1"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-2 px-3" style="border-top:1px solid rgba(51,46,158,.06);">
                <span style="font-size:.68rem;color:#94A3B8;">
                    Showing {{ $chargeRequests->firstItem() }}–{{ $chargeRequests->lastItem() }} of {{ $chargeRequests->total() }}
                </span>
                {{ $chargeRequests->links() }}
            </div>
        @endif
    </div>

    {{-- Approve / Reject Modal --}}
    @if($showModal)
    <div style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:9999;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;width:420px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,.25);padding:28px 32px 24px;">
            {{-- Icon --}}
            <div style="width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;
                        {{ $modalAction === 'approve' ? 'background:rgba(22,163,74,.12);' : 'background:rgba(245,158,11,.12);' }}">
                <i class="ph {{ $modalAction === 'approve' ? 'ph-check-circle' : 'ph-x-circle' }}" 
                   style="font-size:1.5rem;{{ $modalAction === 'approve' ? 'color:#16A34A;' : 'color:#F59E0B;' }}"></i>
            </div>

            {{-- Title --}}
            <h5 style="font-weight:800;font-size:1rem;color:#1E293B;text-align:center;margin-bottom:4px;">
                {{ $modalAction === 'approve' ? 'Approve Payment Charge' : 'Reject Payment Charge' }}
            </h5>
            <p style="font-size:.72rem;color:#94A3B8;text-align:center;margin-bottom:20px;">
                {{ $modalAction === 'approve' ? 'Enter customer name or any details for this approval.' : 'Enter the reason for rejecting this payment charge.' }}
            </p>

            {{-- Note box --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:.68rem;font-weight:700;color:#475569;margin-bottom:6px;">
                    {{ $modalAction === 'approve' ? 'Customer / Approval Details' : 'Decline Reason *' }}
                </label>
                <textarea wire:model="modalNote" rows="3" 
                    placeholder="{{ $modalAction === 'approve' ? 'e.g. Customer confirmed via phone, payment authorised...' : 'e.g. Insufficient funds, card declined...' }}"
                    class="form-control" style="font-size:.8rem;resize:vertical;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);padding:10px 14px;width:100%;"></textarea>
                @error('modalNote') <span style="font-size:.65rem;color:#DC2626;margin-top:4px;display:block;">{{ $message }}</span> @enderror
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2">
                <button type="button" wire:click="closeModal"
                    style="flex:1;padding:10px;border-radius:10px;border:1.5px solid rgba(51,46,158,.15);background:#fff;font-size:.76rem;font-weight:700;color:#64748B;cursor:pointer;">
                    Cancel
                </button>
                <button type="button" wire:click="{{ $modalAction === 'approve' ? 'executeApprove' : 'executeReject' }}"
                    style="flex:1;padding:10px;border-radius:10px;border:none;font-size:.76rem;font-weight:700;color:#fff;cursor:pointer;
                           {{ $modalAction === 'approve' ? 'background:#16A34A;' : 'background:#F59E0B;' }}">
                    <i class="ph {{ $modalAction === 'approve' ? 'ph-check' : 'ph-x' }} me-1"></i>
                    {{ $modalAction === 'approve' ? 'Approve' : 'Reject' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
