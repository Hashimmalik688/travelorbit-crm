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
 * Refunds queue for accounts — the final sign-off on a "Refund to Customer"
 * payout, once a manager has already approved it on the M&R Auth Queue (see
 * RefundAuthQueue::executeApproveRefund). Approve here → completes the
 * refund (money actually leaves Travel Orbit's balance, the linked Refund
 * flips to 'processed'). Decline → the Refund simply stays 'received', so
 * it can be re-queued from the booking page.
 *
 * A work queue, not a report — it only ever lists what's sitting here
 * waiting on YOUR decision, nothing historical.
 */
class RefundIndex extends Component
{
    use WithPagination;

    public $search = '';

    // Approve/Decline modal
    public $showModal = false;
    public $modalAction = '';       // 'approve' or 'decline'
    public $modalPaymentId = null;
    public $modalNote = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $query = BookingPaymentHistory::with(['booking', 'user'])
            ->where('status', 'pending')
            ->where('payment_details->refund_payout', true)
            ->where('payment_details->manager_approved', true)
            ->when($this->search, function ($q) {
                $q->whereHas('booking', function ($bq) {
                    $bq->where('booking_number', 'like', '%' . $this->search . '%')
                       ->orWhere('lead_name', 'like', '%' . $this->search . '%')
                       ->orWhere('lead_email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'asc');

        return view('livewire.refund-index', [
            'awaitingPayouts' => $query->paginate(15),
        ]);
    }
}
