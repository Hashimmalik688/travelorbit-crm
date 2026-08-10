<?php

namespace App\Livewire;

use App\Models\BookingPaymentHistory;
use App\Models\Refund;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Read-only overview of refund requests for accounts. The real approval gate
 * is split across three stops depending on which direction the money's
 * moving, and how far a payout has gotten:
 *
 *  - 'requested' → accounts approves/rejects the ticket-provider refund
 *    receipt claim on Charge Requests (see PaymentChargeRequest::advanceLinkedRefund),
 *    which flips this Refund to 'received' or 'rejected'.
 *  - 'received'  → once the booking page queues a payout ("Refund to
 *    Customer"), a manager approves/declines it on the M&R Auth Queue (see
 *    RefundAuthQueue::executeApproveRefund/executeDeclineRefund) first —
 *    declining leaves this Refund 'received' to retry; approving forwards
 *    it to accounts on Charge Requests for the final sign-off
 *    (PaymentChargeRequest::advanceLinkedRefund), which flips it to
 *    'processed'.
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

    /**
     * Refund IDs with a pending booking_payment_history row of the given kind
     * ('refund_receipt' or 'refund_payout'), optionally further filtered.
     *
     * Note: Collection::when() invokes a Closure passed as its condition
     * argument (treating it as a value-computing callback), so $extra must
     * be passed as `!== null`, not handed to when() directly — otherwise
     * Laravel calls $extra($collection) instead of filtering rows with it.
     */
    protected function refundIdsPendingOn(string $flag, ?\Closure $extra = null): array
    {
        return BookingPaymentHistory::where('status', 'pending')
            ->get()
            ->filter(fn ($ph) => $ph->payment_details[$flag] ?? false)
            ->when($extra !== null, fn ($rows) => $rows->filter($extra))
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
        // Still awaiting a manager decision on the M&R Auth Queue, vs. manager
        // already approved and it's now awaiting the final accounts sign-off
        // on Charge Requests — two different links/labels in the table below.
        $payoutPendingAtManagerIds  = $this->refundIdsPendingOn('refund_payout', fn ($ph) => !($ph->payment_details['manager_approved'] ?? false));
        $payoutPendingAtAccountsIds = $this->refundIdsPendingOn('refund_payout', fn ($ph) => (bool) ($ph->payment_details['manager_approved'] ?? false));

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
            'payoutPendingAtManagerIds' => $payoutPendingAtManagerIds,
            'payoutPendingAtAccountsIds' => $payoutPendingAtAccountsIds,
        ]);
    }
}
