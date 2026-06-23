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

    public function approvePayment(int $historyId): void
    {
        $ph = BookingPaymentHistory::findOrFail($historyId);
        $ph->update(['status' => 'approved', 'approved_by' => Auth::id()]);

        $booking = Booking::find($ph->booking_id);
        AuditLogger::log(Auth::user(), $booking, 'payment_approved', "Payment charge {$historyId} approved");

        if ($booking && $booking->booking_status === Booking::STATUS_PAYMENT_CHARGE_REQUEST) {
            $booking->update(['booking_status' => Booking::STATUS_PENDING]);
        }

        session()->flash('success', 'Payment charge approved.');
    }

    public function rejectPayment(int $historyId): void
    {
        $ph = BookingPaymentHistory::findOrFail($historyId);
        $booking = Booking::find($ph->booking_id);

        $ph->update(['status' => 'rejected']);
        AuditLogger::log(Auth::user(), $booking, 'payment_rejected', "Payment charge {$historyId} rejected");

        if ($booking && $booking->booking_status === Booking::STATUS_PAYMENT_CHARGE_REQUEST) {
            $booking->update(['booking_status' => Booking::STATUS_PENDING]);
        }

        session()->flash('success', 'Payment charge rejected.');
    }

    public function deletePayment(int $historyId): void
    {
        $ph = BookingPaymentHistory::findOrFail($historyId);
        $booking = Booking::find($ph->booking_id);

        AuditLogger::log(Auth::user(), $booking, 'payment_deleted', "Payment charge {$historyId} deleted");

        if ($booking && $booking->booking_status === Booking::STATUS_PAYMENT_CHARGE_REQUEST) {
            $booking->update(['booking_status' => Booking::STATUS_PENDING]);
        }

        $ph->delete();

        session()->flash('success', 'Payment charge deleted.');
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
