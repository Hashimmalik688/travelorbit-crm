<?php

namespace App\Livewire;

use App\Models\PaymentSchedule;
use App\Services\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $editingSchedule = null;
    public $paymentMode = '';
    public $paidDate = '';
    public $showModal = false;

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getTotalCollectedThisMonthProperty()
    {
        return PaymentSchedule::where('status', 'paid')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('amount');
    }

    public function getTotalOutstandingProperty()
    {
        return PaymentSchedule::whereIn('status', ['pending', 'overdue'])->sum('amount');
    }

    public function getTotalOverdueCountProperty()
    {
        return PaymentSchedule::where('status', 'overdue')->count();
    }

    public function getNext7DaysDueCountProperty()
    {
        return PaymentSchedule::where('status', 'pending')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();
    }

    public function markAsPaid($scheduleId): void
    {
        $this->editingSchedule = PaymentSchedule::with('booking')->find($scheduleId);
        $this->paymentMode = '';
        $this->paidDate = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function markOverdue($scheduleId): void
    {
        $schedule = PaymentSchedule::find($scheduleId);
        if ($schedule) {
            $schedule->update(['status' => 'overdue']);
            AuditLogger::log(
                Auth::user(),
                $schedule->booking,
                'payment_marked_overdue',
                "Installment #{$schedule->installment_number} ({$schedule->amount}) marked as overdue",
            );
        }
    }

    public function confirmPayment(): void
    {
        $this->validate([
            'paymentMode' => 'required|in:cash,bank_transfer,stripe,klarna,card',
            'paidDate' => 'required|date',
        ]);

        if ($this->editingSchedule) {
            $this->editingSchedule->update([
                'status' => 'paid',
                'payment_mode' => $this->paymentMode,
                'paid_date' => $this->paidDate,
                'recorded_by' => Auth::id(),
            ]);

            AuditLogger::log(
                Auth::user(),
                $this->editingSchedule->booking,
                'payment_recorded',
                "Payment of {$this->editingSchedule->amount} recorded for installment #{$this->editingSchedule->installment_number}",
                null,
                [
                    'payment_mode' => $this->paymentMode,
                    'paid_date' => $this->paidDate,
                    'amount' => $this->editingSchedule->amount,
                    'installment_number' => $this->editingSchedule->installment_number,
                ],
            );

            session()->flash('success', 'Payment marked as paid.');
        }

        $this->showModal = false;
        $this->editingSchedule = null;
    }

    public function render()
    {
        $schedules = PaymentSchedule::query()
            ->with(['booking'])
            ->when($this->search, function ($query) {
                $query->whereHas('booking', function ($q) {
                    $q->where('booking_number', 'ILIKE', "%{$this->search}%")
                        ->orWhere('booker_name', 'ILIKE', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderByDesc('due_date')
            ->paginate(15);

        return view('livewire.payment-index', [
            'schedules' => $schedules,
            'totalCollectedThisMonth' => $this->totalCollectedThisMonth,
            'totalOutstanding' => $this->totalOutstanding,
            'totalOverdueCount' => $this->totalOverdueCount,
            'next7DaysDueCount' => $this->next7DaysDueCount,
        ]);
    }
}
