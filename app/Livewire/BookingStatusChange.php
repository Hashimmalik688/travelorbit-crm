<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Services\BookingStatusService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Status Change page — lets issuance correct the status of a booking that is
 * somewhere in the issuance pipeline. Any status may be set as the target, but
 * only pipeline bookings may be acted on (see BookingStatusService).
 */
class BookingStatusChange extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    /** Booking currently open in the change panel. */
    public ?int $editingId = null;
    public string $newStatus = '';
    public string $reason = '';
    public string $lastPaymentDate = '';

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    /** Every user reaching this component already passed the route middleware. */
    private function guard(): void
    {
        abort_unless(Auth::user()?->hasPermission('bookings.change_status'), 403);
    }

    public function startEdit(int $id): void
    {
        $this->guard();
        $booking = Booking::findOrFail($id);
        abort_unless(BookingStatusService::isInPipeline($booking), 403);

        $this->editingId       = $id;
        $this->newStatus       = $booking->booking_status;
        $this->reason          = '';
        $this->lastPaymentDate = $booking->last_payment_date?->format('Y-m-d') ?? '';
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->newStatus = '';
        $this->reason    = '';
        $this->lastPaymentDate = '';
        $this->resetErrorBag();
    }

    public function save(BookingStatusService $service): void
    {
        $this->guard();

        $booking = Booking::findOrFail($this->editingId);
        abort_unless(BookingStatusService::isInPipeline($booking), 403);

        $this->validate([
            'newStatus' => ['required', 'string', 'in:' . implode(',', BookingStatusService::selectableStatuses())],
            'reason'    => ['nullable', 'string', 'max:500'],
            // Payment-plan / payment-awaiting are meaningless without the date
            // the balance is due, so it is required for those two targets only.
            'lastPaymentDate' => [
                in_array($this->newStatus, BookingStatusService::NEEDS_LAST_PAYMENT_DATE, true) ? 'required' : 'nullable',
                'date',
            ],
        ], [
            'lastPaymentDate.required' => 'A last payment date is required for the payment plan / payment awaiting statuses.',
        ]);

        if ($this->newStatus === $booking->booking_status && $this->reason === '') {
            $this->addError('newStatus', 'That is already the current status — pick a different one, or add a reason to record the change.');
            return;
        }

        $label = $service->apply($booking, $this->newStatus, $this->reason ?: null, $this->lastPaymentDate ?: null);

        $this->cancelEdit();
        session()->flash('success', "Booking #{$booking->booking_number} is now {$label}.");
    }

    public function render()
    {
        $bookings = Booking::query()
            ->whereIn('booking_status', BookingStatusService::PIPELINE_STATUSES)
            ->when($this->statusFilter, fn ($q) => $q->where('booking_status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(fn ($w) => $w
                    ->where('booking_number', 'like', $term)
                    ->orWhere('booker_first_name', 'like', $term)
                    ->orWhere('booker_last_name', 'like', $term)
                    ->orWhere('booker_email', 'like', $term));
            })
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('livewire.booking-status-change', [
            'bookings'         => $bookings,
            'statuses'         => Booking::STATUS_LABELS,
            'pipelineStatuses' => BookingStatusService::PIPELINE_STATUSES,
        ]);
    }
}
