<div>
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="margin-bottom:1.75rem;">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#7C3AED,#A78BFA);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px rgba(124,58,237,.28);flex-shrink:0;">
                <i class="ph ph-arrows-counter-clockwise" style="font-size:1.44rem;color:#fff;"></i>
            </div>
            <h1 class="mb-0" style="font-size:1.65rem;font-weight:700;color:#0F172A;letter-spacing:-.03em;">Refunds</h1>
        </div>
        <span class="d-flex align-items-center gap-2" style="background:rgba(124,58,237,.1);color:#7C3AED;font-size:0.888rem;font-weight:700;padding:8px 18px;border-radius:24px;">
            <i class="ph ph-hourglass-medium"></i> {{ $awaitingPayouts->total() }} Pending
        </span>
    </div>

    @if(session()->has('success'))
      <div class="alert alert-success border-0 py-2 px-3 mb-3" style="font-size:0.984rem;">{{ session('success') }}</div>
    @endif

    {{-- Filter bar --}}
    <div class="d-flex align-items-center gap-2" style="background:#fff;border:1px solid rgba(51,46,158,.08);border-radius:14px;padding:10px 14px;margin-bottom:20px;box-shadow:0 1px 2px rgba(15,23,42,.03);">
        <i class="ph ph-magnifying-glass" style="font-size:1.02rem;color:#94A3B8;"></i>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search booking #, name, email…" style="border:none;outline:none;font-size:0.936rem;color:#1E293B;background:transparent;flex:1;">
        @if($search)
            <button wire:click="$set('search','')"
              style="background:rgba(51,46,158,.06);border:none;color:#475569;border-radius:16px;padding:4px 12px;font-size:0.84rem;font-weight:600;cursor:pointer;">
              ✕ Clear
            </button>
        @endif
    </div>

    {{-- Queue --}}
    <div class="card animate-in" style="border-radius:16px;overflow:hidden;">
        @if($awaitingPayouts->isEmpty())
            <div class="text-center" style="padding:64px 24px;">
                <div style="width:64px;height:64px;border-radius:50%;background:rgba(22,163,74,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="ph ph-check-circle" style="font-size:2rem;color:#16A34A;"></i>
                </div>
                <h5 class="mb-1" style="font-weight:800;color:#16A34A;font-size:1.14rem;">Queue is clear</h5>
                <p style="color:#94A3B8;font-size:0.912rem;margin:0;">No refund payouts awaiting your approval.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#FAFBFF;">
                        <tr style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94A3B8;">
                            <th style="width:40px;font-weight:700;">#</th>
                            <th style="font-weight:700;">Booking</th>
                            <th style="font-weight:700;">Agent</th>
                            <th style="font-weight:700;">Approved By</th>
                            <th class="text-end" style="font-weight:700;">&pound; Amount</th>
                            <th style="font-weight:700;">Mode</th>
                            <th style="font-weight:700;">Requested</th>
                            <th style="width:190px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($awaitingPayouts as $index => $p)
                            @php $details = $p->payment_details ?? []; @endphp
                            <tr>
                                <td style="vertical-align:middle;">
                                    <span style="width:24px;height:24px;border-radius:50%;background:rgba(124,58,237,.1);display:inline-flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:800;color:#7C3AED;">
                                        {{ $awaitingPayouts->firstItem() + $index }}
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
                                <td style="vertical-align:middle;font-size:0.816rem;color:#475569;">
                                    {{ $p->created_at->format('d M Y H:i') }}
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
            <div class="card-footer d-flex justify-content-between align-items-center py-2 px-3" style="border-top:1px solid rgba(51,46,158,.06);">
                <span style="font-size:0.816rem;color:#475569;">
                    Showing {{ $awaitingPayouts->firstItem() }}–{{ $awaitingPayouts->lastItem() }} of {{ $awaitingPayouts->total() }}
                </span>
                {{ $awaitingPayouts->links() }}
            </div>
        @endif
    </div>

    {{-- Approve / Decline Modal --}}
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
