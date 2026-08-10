<?php

namespace App\Livewire;

use App\Models\BookingPaymentHistory;
use App\Models\Refund;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only overview of refund requests for accounts. The real approval gate
 * is split across two queues depending on which direction the money's moving:
 *
 *  - 'requested' → accounts approves/rejects the ticket-provider refund
 *    receipt claim on Charge Requests (see PaymentChargeRequest::advanceLinkedRefund),
 *    which flips this Refund to 'received' or 'rejected'.
 *  - 'received'  → once the booking page queues a payout ("Refund to
 *    Customer"), a manager approves/declines THAT on the M&R Auth Queue (see
 *    RefundAuthQueue::executeApprove/executeDecline), which flips this
 *    Refund to 'processed' (or leaves it 'received' to retry).
 *
 * This page never mutates a Refund itself — it just tells accounts where
 * each one currently sits and links out to the right queue to act on it.
 */
class RefundIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    /** Confirmed received from the ticket provider, sitting in Travel Orbit's balance and not yet paid out to the customer. */
    public function getTotalReceivedProperty()
    {
        return Refund::where('status', 'received')->sum('refund_amount');
    }

    public function getTotalProcessedProperty()
    {
        return Refund::where('status', 'processed')->sum('refund_amount');
    }

    public function getPendingReviewCountProperty()
    {
        return Refund::whereIn('status', ['requested', 'received'])->count();
    }

    /** Refund IDs with a pending booking_payment_history row of the given kind ('refund_receipt' or 'refund_payout'). */
    protected function refundIdsPendingOn(string $flag): array
    {
        return BookingPaymentHistory::where('status', 'pending')
            ->get()
            ->filter(fn ($ph) => $ph->payment_details[$flag] ?? false)
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

    public function render()
    {
        $receiptPendingIds = $this->refundIdsPendingOn('refund_receipt');
        $payoutPendingIds  = $this->refundIdsPendingOn('refund_payout');

        $refunds = Refund::query()
            ->with(['booking', 'requestedBy', 'processedBy'])
            ->when($this->search, function ($query) {
                $query->whereHas('booking', function ($q) {
                    $q->where('booking_number', 'ILIKE', "%{$this->search}%")
                        ->orWhere('booker_name', 'ILIKE', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.refund-index', [
            'refunds' => $refunds,
            'receiptPendingIds' => $receiptPendingIds,
            'payoutPendingIds' => $payoutPendingIds,
        ]);
    }
}
