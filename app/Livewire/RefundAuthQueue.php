<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingPaymentHistory;
use App\Models\MarginClaim;
use App\Models\Refund;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * M&R Auth Queue — manager approval for "Refund to Customer" payouts queued
 * from the booking page (see BookingShow::submitRefundChargePayment). Split
 * out from the accounts Charge Requests queue (PaymentChargeRequest) because
 * this is a manager call, not an accounts one, and because it's genuinely
 * two decisions in one:
 *
 *  1. The refund itself — approve (optionally lowering the amount actually
 *     paid to the customer) or decline.
 *  2. The claimed margin that comes with it (received − paid-to-client) —
 *     release it into the agent's performance report, or hold it back.
 *
 * Nothing is credited or paid out until approved here; a decline leaves the
 * underlying Refund exactly as it was (still 'received'), so it can be
 * re-queued from the booking page.
 */
class RefundAuthQueue extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    public $showModal = false;
    public $modalAction = '';       // 'approve' or 'decline'
    public $modalPaymentId = null;
    public $modalNote = '';

    // Approve-only fields
    public $approveAmount = '';
    public $approveHoldMargin = false;

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

    /** What's left to claim as margin at the currently-typed amount — mirrors BookingShow::getRefundClaimedMarginProperty. */
    public function getLiveClaimedMarginProperty(): float
    {
        $ph = $this->modalPaymentId ? BookingPaymentHistory::find($this->modalPaymentId) : null;
        if (!$ph) return 0;

        $received = (float) ($ph->payment_details['refund_received_amount'] ?? 0);
        $toClient = (float) $this->approveAmount;
        return max(0, $received - $toClient);
    }

    public function confirmApprove(int $historyId): void
    {
        abort_unless($this->canManage(), 403);

        $ph = BookingPaymentHistory::findOrFail($historyId);
        $this->modalPaymentId = $historyId;
        $this->modalAction = 'approve';
        $this->modalNote = '';
        $this->approveAmount = (string) abs((float) $ph->amount);
        $this->approveHoldMargin = false;
        $this->showModal = true;
    }

    public function confirmDecline(int $historyId): void
    {
        abort_unless($this->canManage(), 403);

        $this->modalPaymentId = $historyId;
        $this->modalAction = 'decline';
        $this->modalNote = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset('showModal', 'modalAction', 'modalPaymentId', 'modalNote', 'approveAmount', 'approveHoldMargin');
    }

    public function executeApprove(): void
    {
        abort_unless($this->canManage(), 403);

        $ph = BookingPaymentHistory::findOrFail($this->modalPaymentId);
        $received = (float) ($ph->payment_details['refund_received_amount'] ?? 0);

        $this->validate([
            'approveAmount' => 'required|numeric|min:0.01|max:' . ($received ?: 999999999),
            'modalNote' => 'nullable|string|max:500',
        ]);

        $claimedMargin = max(0, $received - (float) $this->approveAmount);

        $details = $ph->payment_details ?? [];
        $details['claimed_margin'] = $claimedMargin;
        $details['margin_held'] = $this->approveHoldMargin;
        $details['approval_note'] = $this->modalNote;

        $ph->update([
            'amount' => -abs((float) $this->approveAmount),
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'payment_details' => $details,
        ]);

        $booking = Booking::find($ph->booking_id);

        $logMsg = "Refund to customer {$ph->id} approved for £" . number_format((float) $this->approveAmount, 2);
        if ($this->modalNote) {
            $logMsg .= " — {$this->modalNote}";
        }
        AuditLogger::log(Auth::user(), $booking, 'refund_payment_approved', $logMsg);

        if ($booking) {
            $this->appendBookingActivity($booking, 'refund_payment_approved', 'Refund to Customer Approved',
                '£' . number_format((float) $this->approveAmount, 2) . ($this->modalNote ? " — {$this->modalNote}" : ''));
        }

        // Mark the underlying Refund processed, same as the old accounts flow did.
        $refundId = $details['refund_id'] ?? null;
        if ($refundId && ($refund = Refund::find($refundId))) {
            $refund->update([
                'status' => 'processed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);
        }

        // Credit the claimed margin now — not at request time — so a
        // declined refund never credits margin that was never really given
        // up. Held margin simply isn't credited (yet); it stays visible on
        // this row's details for whoever revisits it.
        if (!$this->approveHoldMargin && $claimedMargin > 0) {
            MarginClaim::create([
                'booking_id' => $ph->booking_id,
                'user_id' => $ph->user_id,
                'applied_by_user_id' => Auth::id(),
                'amount' => $claimedMargin,
                'reason' => 'Refund to customer — ' . ($details['comment'] ?? 'margin claimed on refund'),
                'claim_date' => now()->toDateString(),
            ]);
        }

        $this->closeModal();
        session()->flash('success', 'Refund approved.');
    }

    public function executeDecline(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate(['modalNote' => 'required|string|max:500']);

        $ph = BookingPaymentHistory::findOrFail($this->modalPaymentId);
        $booking = Booking::find($ph->booking_id);

        $details = $ph->payment_details ?? [];
        $details['rejection_reason'] = $this->modalNote;

        $ph->update(['status' => 'rejected', 'payment_details' => $details]);

        AuditLogger::log(Auth::user(), $booking, 'refund_payment_declined', "Refund to customer {$ph->id} declined — {$this->modalNote}");

        if ($booking) {
            $this->appendBookingActivity($booking, 'refund_payment_declined', 'Refund to Customer Declined', $this->modalNote);
        }

        // Refund simply stays 'received' — nothing was paid, so it can be re-queued from the booking page.

        $this->closeModal();
        session()->flash('success', 'Refund declined.');
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
        $query = BookingPaymentHistory::with(['booking', 'user'])
            ->where('status', 'pending')
            ->where('payment_details->refund_payout', true)
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

        return view('livewire.refund-auth-queue', [
            'refundRequests' => $query->paginate(15),
        ]);
    }
}
