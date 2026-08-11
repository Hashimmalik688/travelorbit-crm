<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingPaymentHistory;
use App\Models\Refund;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Refunds overview for accounts — plus the actual approval gate for the
 * customer-payout half. The gate is split across three stops depending on
 * which direction the money's moving, and how far a payout has gotten:
 *
 *  - 'requested' → accounts approves/rejects the ticket-provider refund
 *    receipt claim on Charge Requests (see PaymentChargeRequest::advanceLinkedRefund),
 *    which flips this Refund to 'received' or 'rejected'.
 *  - 'received'  → once the booking page queues a payout ("Refund to
 *    Customer"), a manager approves/declines it on the M&R Auth Queue (see
 *    RefundAuthQueue::executeApproveRefund/executeDeclineRefund) first —
 *    declining leaves this Refund 'received' to retry; approving forwards it
 *    HERE, to accounts, for the final sign-off (see executeApprovePayout/executeDeclinePayout
 *    below), which flips it to 'processed'.
 *
 * The receipt half stays on Charge Requests (it's an ordinary accounts
 * approval, no manager step involved); only the payout half — the one that
 * already went through a manager — lands on this page.
 */
class RefundIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Approve/Decline modal for a manager-approved payout
    public $showModal = false;
    public $modalAction = '';       // 'approve' or 'decline'
    public $modalPaymentId = null;
    public $modalNote = '';

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

    // ── Payout approval (manager-approved, awaiting accounts) ──────────

    public function confirmApprovePayout(int $historyId): void
    {
        $this->modalPaymentId = $historyId;
        $this->modalAction = 'approve';
        $this->modalNote = '';
        $this->showModal = true;
    }

    public function confirmDeclinePayout(int $historyId): void
    {
        $this->modalPaymentId = $historyId;
        $this->modalAction = 'decline';
        $this->modalNote = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset('showModal', 'modalAction', 'modalPaymentId', 'modalNote');
    }

    public function executeApprovePayout(): void
    {
        $this->validate(['modalNote' => 'nullable|string|max:500']);

        $ph = BookingPaymentHistory::findOrFail($this->modalPaymentId);
        $ph->update(['status' => 'approved', 'approved_by' => Auth::id()]);

        $details = $ph->payment_details ?? [];
        $details['approval_note'] = $this->modalNote;
        $ph->update(['payment_details' => $details]);

        $booking = Booking::find($ph->booking_id);
        $amountLabel = '£' . number_format(abs((float) $ph->amount), 2);
        $detail = "{$amountLabel} paid to customer — approved by manager, signed off by accounts";
        if ($this->modalNote) {
            $detail .= " — {$this->modalNote}";
        }

        AuditLogger::log(Auth::user(), $booking, 'refund_payout_completed', "Refund to Customer Completed — payment charge {$ph->id} — {$detail}");

        if ($booking) {
            $this->appendBookingActivity($booking, 'refund_payout_completed', 'Refund to Customer Completed', $detail);
        }

        $refundId = $details['refund_id'] ?? null;
        if ($refundId && ($refund = Refund::find($refundId))) {
            $refund->update([
                'status' => 'processed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);
        }

        $this->closeModal();
        session()->flash('success', 'Refund payout approved — completed.');
    }

    public function executeDeclinePayout(): void
    {
        $this->validate(['modalNote' => 'required|string|max:500']);

        $ph = BookingPaymentHistory::findOrFail($this->modalPaymentId);
        $booking = Booking::find($ph->booking_id);

        $details = $ph->payment_details ?? [];
        $details['rejection_reason'] = $this->modalNote;
        $ph->update(['status' => 'rejected', 'payment_details' => $details]);

        $amountLabel = '£' . number_format(abs((float) $ph->amount), 2);
        $detail = "{$amountLabel} payout declined — refund stays received, can be re-queued — {$this->modalNote}";

        AuditLogger::log(Auth::user(), $booking, 'refund_payout_declined', "Refund to Customer Declined at Accounts — payment charge {$ph->id} — {$detail}");

        if ($booking) {
            $this->appendBookingActivity($booking, 'refund_payout_declined', 'Refund to Customer Declined at Accounts', $detail);
        }

        // Refund simply stays 'received' — nothing was paid, so it can be re-queued from the booking page.

        $this->closeModal();
        session()->flash('success', 'Refund payout declined.');
    }

    private function appendBookingActivity(Booking $booking, string $action, string $label, string $detail): void
    {
        $user = Auth::user();
        $agent = $user->name ?? 'System';
        $ini = strtoupper(substr($agent, 0, 1));
        if (($sp = strpos($agent, ' ')) !== false) {
            $ini .= strtoupper(substr($agent, $sp + 1, 1));
        }
        $avatarUrl = $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;

        $log = $booking->activity_log ?? [];
        if (is_string($log)) {
            $log = json_decode($log, true) ?? [];
        }

        $log[] = [
            'agent'           => $agent,
            'avatar_url'      => $avatarUrl,
            'avatar_initials' => $ini,
            'user_id'         => $user->id,
            'timestamp'       => now()->toDateTimeString(),
            'action'          => $action,
            'detail'          => $detail,
            'type'            => 'update',
        ];

        $booking->update(['activity_log' => $log]);
    }

    public function render()
    {
        $receiptPendingIds = $this->refundIdsPendingOn('refund_receipt');
        // Still awaiting a manager decision on the M&R Auth Queue, vs. manager
        // already approved and it's now awaiting the final accounts sign-off
        // right here — two different links/labels in the overview table below.
        $payoutPendingAtManagerIds  = $this->refundIdsPendingOn('refund_payout', fn ($ph) => !($ph->payment_details['manager_approved'] ?? false));
        $payoutPendingAtAccountsIds = $this->refundIdsPendingOn('refund_payout', fn ($ph) => (bool) ($ph->payment_details['manager_approved'] ?? false));

        $awaitingPayouts = BookingPaymentHistory::with(['booking', 'user'])
            ->where('status', 'pending')
            ->where('payment_details->refund_payout', true)
            ->where('payment_details->manager_approved', true)
            ->orderBy('created_at', 'asc')
            ->get();

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
            'awaitingPayouts' => $awaitingPayouts,
            'receiptPendingIds' => $receiptPendingIds,
            'payoutPendingAtManagerIds' => $payoutPendingAtManagerIds,
            'payoutPendingAtAccountsIds' => $payoutPendingAtAccountsIds,
        ]);
    }
}
