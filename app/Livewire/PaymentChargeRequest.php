<?php

namespace App\Livewire;

use App\Models\BookingPaymentHistory;
use App\Models\Booking;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentChargeRequest extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    public $showModal = false;
    public $modalAction = '';       // 'approve' or 'reject'
    public $modalPaymentId = null;
    public $modalNote = '';

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

    public function confirmApprove(int $historyId): void
    {
        $this->modalPaymentId = $historyId;
        $this->modalAction = 'approve';
        $this->modalNote = '';
        $this->showModal = true;
    }

    public function confirmReject(int $historyId): void
    {
        $this->modalPaymentId = $historyId;
        $this->modalAction = 'reject';
        $this->modalNote = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset('showModal', 'modalAction', 'modalPaymentId', 'modalNote');
    }

    public function executeApprove(): void
    {
        $this->validate(['modalNote' => 'nullable|string|max:500']);

        $ph = BookingPaymentHistory::findOrFail($this->modalPaymentId);
        $ph->update(['status' => 'approved', 'approved_by' => Auth::id()]);

        $booking = Booking::find($ph->booking_id);

        $details = $ph->payment_details ?? [];
        $details['approval_note'] = $this->modalNote;
        $ph->update(['payment_details' => $details]);

        $logMsg = "Payment charge {$ph->id} approved";
        if ($this->modalNote) {
            $logMsg .= " — {$this->modalNote}";
        }
        AuditLogger::log(Auth::user(), $booking, 'payment_approved', $logMsg);

        // Log to booking activity feed
        if ($booking) {
            $this->appendBookingActivity($booking, 'payment_approved', 'Payment Approved', $this->modalNote ?: '');
            $this->restoreBookingStatus($booking, $ph);
            $this->syncCcCharges($booking);
        }

        // Refund payouts (queued from the booking page's Request Refund Payment
        // button — see BookingShow::submitRefundChargePayment) only actually
        // count as "paid out" once approved here. Booking status is deliberately
        // left untouched — Booking::hasActiveRefund() is what marks it refunded.
        $this->markLinkedRefundProcessed($ph);

        $this->closeModal();
        session()->flash('success', 'Payment charge approved.');
    }

    public function executeReject(): void
    {
        $this->validate(['modalNote' => 'required|string|max:500']);

        $ph = BookingPaymentHistory::findOrFail($this->modalPaymentId);
        $booking = Booking::find($ph->booking_id);

        $ph->update(['status' => 'rejected']);

        $details = $ph->payment_details ?? [];
        $details['rejection_reason'] = $this->modalNote;
        $ph->update(['payment_details' => $details]);

        $logMsg = "Payment charge {$ph->id} rejected — {$this->modalNote}";
        AuditLogger::log(Auth::user(), $booking, 'payment_rejected', $logMsg);

        // Log to booking activity feed
        if ($booking) {
            $this->appendBookingActivity($booking, 'payment_rejected', 'Payment Declined', $this->modalNote);
            $this->restoreBookingStatus($booking, $ph);
            $this->syncCcCharges($booking);
        }

        // A rejected refund payout leaves the underlying Refund as still
        // "requested" — nothing was paid, so the booking stays flagged as
        // needing a refund and can be re-queued from the booking page.
        $this->closeModal();
        session()->flash('success', 'Payment charge rejected.');
    }

    /**
     * If this approved payment history row is a refund payout (see
     * BookingShow::submitRefundChargePayment), mark its linked Refund as
     * processed now that accounts has actually approved paying it out.
     */
    private function markLinkedRefundProcessed(BookingPaymentHistory $ph): void
    {
        if (!($ph->payment_details['refund_payout'] ?? false)) {
            return;
        }

        $refundId = $ph->payment_details['refund_id'] ?? null;
        if (!$refundId) return;

        $refund = \App\Models\Refund::find($refundId);
        if (!$refund) return;

        $refund->update([
            'status' => 'processed',
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);
    }

    public function deletePayment(int $historyId): void
    {
        $ph = BookingPaymentHistory::findOrFail($historyId);
        $booking = Booking::find($ph->booking_id);

        AuditLogger::log(Auth::user(), $booking, 'payment_deleted', "Payment charge {$historyId} deleted");

        if ($booking) {
            $this->restoreBookingStatus($booking, $ph);
        }

        $ph->delete();

        if ($booking) {
            $this->syncCcCharges($booking);
        }

        session()->flash('success', 'Payment charge deleted.');
    }

    /**
     * Restore whatever status the booking was in before this charge request —
     * never hardcode "pending", since the booking may have been issued (or in
     * any other status) when the charge was requested. No-op if the booking's
     * status was never touched in the first place (issued bookings keep their
     * status throughout the request/resolve cycle — see BookingShow::requestPaymentCharge()).
     */
    private function restoreBookingStatus(Booking $booking, BookingPaymentHistory $ph): void
    {
        if ($booking->booking_status !== Booking::STATUS_PAYMENT_CHARGE_REQUEST) {
            return;
        }

        $restoreTo = $ph->payment_details['previous_booking_status'] ?? Booking::STATUS_PENDING;
        $booking->update(['booking_status' => $restoreTo]);
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

    /**
     * Recalculate booking_payments.cc_charges from all approved payment history
     * records.  Mirrors the logic in BookingShow so the stored total stays in
     * sync when requests are approved/rejected/deleted.
     */
    private function syncCcCharges(Booking $booking): void
    {
        $cardRates = [
            'epay_debit'  => 1.5,
            'epay_credit' => 2.5,
            'debit_card'  => 1.5,
            'credit_card' => 2.5,
            'amex'        => 2.5,
        ];

        $booking->load('paymentHistory', 'payment');
        $history  = $booking->paymentHistory ?? collect();
        $approved = $history->filter(fn($ph) => $ph->status === 'approved');

        $bookingCcRate = (float) ($booking->payment?->cc_charge_rate ?? 0);

        $total = $approved->sum(function ($ph) use ($cardRates, $bookingCcRate) {
            if (isset($ph->payment_details['cc_charge'])) {
                return (float) $ph->payment_details['cc_charge'];
            }
            // Fallback for records without a stored cc_charge: use the rate from
            // payment_details if present, then the booking-level rate, then the
            // method default.
            $method = $ph->payment_method;
            if (!isset($cardRates[$method])) {
                return 0;
            }
            $rate = isset($ph->payment_details['cc_charge_rate'])
                ? (float) $ph->payment_details['cc_charge_rate']
                : ($bookingCcRate ?: $cardRates[$method]);
            return round((float) $ph->amount * $rate / 100, 2);
        });

        if ($booking->payment) {
            $booking->payment->update(['cc_charges' => $total]);
        }
    }

    public function render()
    {
        $query = BookingPaymentHistory::with(['booking', 'user'])
            ->where('status', 'pending')
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

        return view('livewire.payment-charge-request', [
            'chargeRequests' => $query->paginate(15),
        ]);
    }
}
