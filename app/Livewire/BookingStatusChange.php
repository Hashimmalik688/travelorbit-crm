<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Services\BookingStatusService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Status Change page — lets issuance set the status of ANY booking. The list
 * shows every booking (searchable / filterable by status); any status may be
 * selected as the new value, and BookingStatusService applies the matching
 * date stamp plus the activity + audit entries.
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
            'bookings'       => $bookings,
            'statuses'       => Booking::STATUS_LABELS,
            'filterStatuses' => BookingStatusService::selectableStatuses(),
        ]);
    }
}
