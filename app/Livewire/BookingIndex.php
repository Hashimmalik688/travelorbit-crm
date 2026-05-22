<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class BookingIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $bookings = Booking::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('booking_number', 'ILIKE', "%{$this->search}%")
                      ->orWhere('booker_first_name', 'ILIKE', "%{$this->search}%")
                      ->orWhere('booker_last_name', 'ILIKE', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('booking_status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('booking_type', $this->typeFilter);
            })
            ->with(['payment', 'passengers', 'flightDetail'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.booking-index', compact('bookings'));
    }
}
