<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingPaymentHistory;
use App\Models\MarginClaim;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * M&R Auth Queue — manager review for "Refund to Customer" requests queued
 * from the booking page (see BookingShow::submitRefundChargePayment). Two
 * entirely separate entries per request, each with its own approval:
 *
 *  1. Refund Request — approve (optionally lowering the amount actually
 *     paid) or decline the payout itself. Approving does NOT finish it —
 *     it forwards to the accounts Charge Requests queue for the actual,
 *     final sign-off (see PaymentChargeRequest::advanceLinkedRefund).
 *     Declining is final: the underlying Refund simply stays 'received',
 *     so it can be re-queued from the booking page.
 *
 *  2. Margin Claim — the gap between what was received and what's being
 *     paid out (see BookingShow::getRefundClaimedMarginProperty), queued as
 *     its own MarginClaim row the moment the refund was requested. A manager
 *     releases it (counts in Agent Performance) or holds it (doesn't) —
 *     entirely independent of whatever happens to the refund request above.
 */
class RefundAuthQueue extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Refund Request modal
    public $showRefundModal = false;
    public $refundModalAction = '';       // 'approve' or 'decline'
    public $refundModalPaymentId = null;
    public $refundModalNote = '';
    public $approveAmount = '';

    protected $queryString = ['search', 'dateFrom', 'dateTo'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function canManage(): bool
    {
        return Auth::user()->hasPermission('refunds.manage');
    }

    // ── Refund Request half ──────────────────────────────────────────

    public function confirmApproveRefund(int $historyId): void
    {
        abort_unless($this->canManage(), 403);

        $ph = BookingPaymentHistory::findOrFail($historyId);
        $this->refundModalPaymentId = $historyId;
        $this->refundModalAction = 'approve';
        $this->refundModalNote = '';
        $this->approveAmount = (string) abs((float) $ph->amount);
        $this->showRefundModal = true;
    }

    public function confirmDeclineRefund(int $historyId): void
    {
        abort_unless($this->canManage(), 403);

        $this->refundModalPaymentId = $historyId;
        $this->refundModalAction = 'decline';
        $this->refundModalNote = '';
        $this->showRefundModal = true;
    }

    public function closeRefundModal(): void
    {
        $this->reset('showRefundModal', 'refundModalAction', 'refundModalPaymentId', 'refundModalNote', 'approveAmount');
    }

    /**
     * Manager sign-off only — does not finish the refund. Sets
     * manager_approved so it now surfaces in the accounts Charge Requests
     * queue (see PaymentChargeRequest::render()) for the real, final
     * approval; row itself stays 'pending' throughout.
     */
    public function executeApproveRefund(): void
    {
        abort_unless($this->canManage(), 403);

        $ph = BookingPaymentHistory::findOrFail($this->refundModalPaymentId);
        $received = (float) ($ph->payment_details['refund_received_amount'] ?? 0);
        $originalAmount = abs((float) $ph->amount); // what the agent requested, before any manager edit

        $this->validate([
            'approveAmount' => 'required|numeric|min:0.01|max:' . ($received ?: 999999999),
            'refundModalNote' => 'nullable|string|max:500',
        ]);

        $newAmount = (float) $this->approveAmount;

        $details = $ph->payment_details ?? [];
        $details['manager_approved'] = true;
        $details['manager_approved_by'] = Auth::id();
        $details['manager_approved_at'] = now()->toDateTimeString();
        $details['manager_note'] = $this->refundModalNote;
        $details['manager_original_amount'] = $originalAmount;

        $ph->update([
            'amount' => -abs($newAmount),
            'payment_details' => $details,
        ]);

        $booking = Booking::find($ph->booking_id);

        // Says exactly what changed, not just the end state — a manager
        // lowering the payout is the whole point of this approval step
        // being separate from the agent's original request.
        $amountPhrase = abs($originalAmount - $newAmount) > 0.005
            ? 'amount changed from £' . number_format($originalAmount, 2) . ' to £' . number_format($newAmount, 2)
            : 'amount confirmed at £' . number_format($newAmount, 2);

        $logMsg = "Refund to customer {$ph->id} approved by manager — {$amountPhrase} — forwarded to accounts";
        if ($this->refundModalNote) {
            $logMsg .= " — {$this->refundModalNote}";
        }
        AuditLogger::log(Auth::user(), $booking, 'refund_payment_manager_approved', $logMsg);

        if ($booking) {
            $this->appendBookingActivity($booking, 'refund_payment_manager_approved', 'Refund to Customer Approved by Manager',
                ucfirst($amountPhrase) . ' — forwarded to accounts' . ($this->refundModalNote ? " — {$this->refundModalNote}" : ''));
        }

        $this->closeRefundModal();
        session()->flash('success', 'Refund approved — forwarded to accounts for final sign-off.');
    }

    public function executeDeclineRefund(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate(['refundModalNote' => 'required|string|max:500']);

        $ph = BookingPaymentHistory::findOrFail($this->refundModalPaymentId);
        $booking = Booking::find($ph->booking_id);
        $requestedAmount = abs((float) $ph->amount);

        $details = $ph->payment_details ?? [];
        $details['rejection_reason'] = $this->refundModalNote;

        $ph->update(['status' => 'rejected', 'payment_details' => $details]);

        $amountLabel = '£' . number_format($requestedAmount, 2);
        AuditLogger::log(Auth::user(), $booking, 'refund_payment_declined', "Refund to customer {$ph->id} for {$amountLabel} declined by manager — {$this->refundModalNote}");

        if ($booking) {
            $this->appendBookingActivity($booking, 'refund_payment_declined', 'Refund to Customer Declined',
                "{$amountLabel} requested — {$this->refundModalNote}");
        }

        // Refund simply stays 'received' — nothing was paid, so it can be re-queued from the booking page.

        $this->closeRefundModal();
        session()->flash('success', 'Refund declined.');
    }

    // ── Margin Claim half ────────────────────────────────────────────

    /**
     * Releasing a claim does two things at once: credits it toward the
     * agent's monthly performance (MarginClaim::status), and pulls that same
     * amount OUT of the booking's own running balance — it was still sitting
     * there as part of "Overpaid" (the supplier refund landed as a positive
     * approved row, only the amount actually paid back out to the customer
     * left it) until now. A plain approved negative ledger row does that;
     * it's tagged margin_claim so Payment History reads it as what it is
     * rather than another refund payout. Deliberately does NOT add to this
     * booking's own Cost & Margins total — that stays cost-vs-sold only; the
     * claim lives solely in Agent Performance (see AgentPerformance::claimsQuery).
     */
    public function approveMargin(int $claimId): void
    {
        abort_unless($this->canManage(), 403);

        $claim = MarginClaim::findOrFail($claimId);
        $claim->update(['status' => 'released', 'applied_by_user_id' => Auth::id()]);

        BookingPaymentHistory::create([
            'booking_id' => $claim->booking_id,
            'user_id' => $claim->user_id,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'margin_claim',
            'amount' => -abs((float) $claim->amount),
            'payment_details' => ['margin_claim' => true, 'margin_claim_id' => $claim->id, 'comment' => $claim->reason],
            'status' => 'approved',
            'approved_by' => Auth::id(),
        ]);

        $booking = Booking::find($claim->booking_id);
        $agentName = $claim->user?->name ?? 'agent';
        $amountLabel = '£' . number_format((float) $claim->amount, 2);

        AuditLogger::log(Auth::user(), $booking, 'margin_claim_released',
            "Margin claim of {$amountLabel} released — cleared from booking balance, credited to {$agentName}'s performance");

        if ($booking) {
            $this->appendBookingActivity($booking, 'margin_claim_released', 'Margin Claim Released',
                "{$amountLabel} credited to {$agentName}'s performance — {$claim->reason}");
        }

        session()->flash('success', 'Margin claim released — cleared from the booking balance and now counts in Agent Performance.');
    }

    public function holdMargin(int $claimId): void
    {
        abort_unless($this->canManage(), 403);

        $claim = MarginClaim::findOrFail($claimId);
        $claim->update(['status' => 'held', 'applied_by_user_id' => Auth::id()]);

        $booking = Booking::find($claim->booking_id);
        $agentName = $claim->user?->name ?? 'agent';
        $amountLabel = '£' . number_format((float) $claim->amount, 2);

        AuditLogger::log(Auth::user(), $booking, 'margin_claim_held',
            "Margin claim of {$amountLabel} held — not credited to {$agentName}'s performance, stays on the booking balance");

        if ($booking) {
            $this->appendBookingActivity($booking, 'margin_claim_held', 'Margin Claim Held',
                "{$amountLabel} held — not yet credited to {$agentName}'s performance — {$claim->reason}");
        }

        session()->flash('success', 'Margin claim held.');
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
        $refundQuery = BookingPaymentHistory::with(['booking', 'user'])
            ->where('status', 'pending')
            ->where('payment_details->refund_payout', true)
            ->where(function ($q) {
                $q->whereNull('payment_details->manager_approved')
                    ->orWhere('payment_details->manager_approved', false);
            })
            ->when($this->search, function ($q) {
                $q->whereHas('booking', function ($bq) {
                    $bq->where('booking_number', 'like', '%' . $this->search . '%')
                       ->orWhere('lead_name', 'like', '%' . $this->search . '%')
                       ->orWhere('lead_email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'asc');

        $marginQuery = MarginClaim::with(['booking', 'user'])
            ->where('status', 'pending')
            ->when($this->search, function ($q) {
                $q->whereHas('booking', function ($bq) {
                    $bq->where('booking_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'asc');

        return view('livewire.refund-auth-queue', [
            'refundRequests' => $refundQuery->paginate(10, pageName: 'refundPage'),
            'marginClaims'   => $marginQuery->paginate(10, pageName: 'marginPage'),
        ]);
    }
}
