<?php

namespace App\Livewire;

use App\Models\BookingPaymentHistory;
use App\Models\Refund;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only-ish overview of refund requests for accounts. The actual money
 * movement — and the real approval gate — happens on the Charge Requests
 * queue (see PaymentChargeRequest), once a refund payout has been requested
 * from the booking page (BookingShow::submitRefundChargePayment). This page
 * just tracks the underlying "does this booking still owe a refund" record
 * and lets accounts decline one outright before any payout is ever queued.
 */
class RefundIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    /** Still owed — not yet paid out or declined. */
    public function getTotalOutstandingProperty()
    {
        return Refund::where('status', 'requested')->sum('refund_amount');
    }

    public function getTotalProcessedProperty()
    {
        return Refund::where('status', 'processed')->sum('refund_amount');
    }

    public function getPendingReviewCountProperty()
    {
        return Refund::where('status', 'requested')->count();
    }

    /** Refund IDs that already have a payout sitting in the Charge Requests queue. */
    protected function refundIdsWithPendingPayout(): array
    {
        return BookingPaymentHistory::where('status', 'pending')
            ->where('payment_method', 'refund')
            ->get()
            ->map(fn ($ph) => $ph->payment_details['refund_id'] ?? null)
            ->filter()
            ->unique()
            ->all();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Declines the refund need outright — only while it's still just
     * "requested" with nothing queued at accounts yet. Once a payout has
     * been requested, it must be resolved on the Charge Requests queue
     * (approve or reject there) instead.
     */
    public function rejectRefund($refundId, string $reason = ''): void
    {
        if (!Auth::user()->hasPermission('refunds.manage')) {
            return;
        }

        $refund = Refund::find($refundId);
        if (!$refund || $refund->status !== 'requested') return;
        if (in_array($refund->id, $this->refundIdsWithPendingPayout())) return;

        $refund->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'processed_by' => Auth::id(),
            'notes' => $reason ?: $refund->notes,
        ]);

        AuditLogger::log(
            Auth::user(),
            $refund->booking,
            'refund_rejected',
            'Refund request declined' . ($reason ? " — {$reason}" : ''),
        );

        session()->flash('success', "Refund #{$refundId} declined.");
    }

    public function render()
    {
        $pendingPayoutIds = $this->refundIdsWithPendingPayout();

        $refunds = Refund::query()
            ->with(['booking', 'requestedBy', 'processedBy'])
            ->when($this->search, function ($query) {
                $query->whereHas('booking', function ($q) {
                    $q->where('booking_number', 'ILIKE', "%{$this->search}%")
                        ->orWhere('booker_name', 'ILIKE', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'payout_pending', function ($query) use ($pendingPayoutIds) {
                $query->whereIn('id', $pendingPayoutIds);
            })
            ->when($this->statusFilter && $this->statusFilter !== 'payout_pending', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.refund-index', [
            'refunds' => $refunds,
            'pendingPayoutIds' => $pendingPayoutIds,
        ]);
    }
}
