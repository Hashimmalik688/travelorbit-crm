<?php

namespace App\Livewire;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BookingIndex extends Component
{
    use WithPagination;

    // Filters
    public string $search       = '';
    public string $statusFilter = '';
    public string $typeFilter   = '';
    public string $dateFrom     = '';
    public string $dateTo       = '';

    // Context: 'all' or 'mine'
    public string $context      = 'all';
    public ?int  $filterUserId  = null;

    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter'   => ['except' => ''],
        'dateFrom'     => ['except' => ''],
        'dateTo'       => ['except' => ''],
    ];

    public function mount(?int $filterUserId = null, bool $myBookingsOnly = false): void
    {
        if ($myBookingsOnly || $filterUserId) {
            $this->context      = 'mine';
            $this->filterUserId = $filterUserId ?? Auth::id();
            // Default date range: this month
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingStatusFilter(): void  { $this->resetPage(); }
    public function updatingTypeFilter(): void    { $this->resetPage(); }
    public function updatingDateFrom(): void      { $this->resetPage(); }
    public function updatingDateTo(): void        { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = $this->statusFilter = $this->typeFilter = '';
        if ($this->context === 'mine') {
            $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
            $this->dateTo   = now()->endOfMonth()->format('Y-m-d');
        } else {
            $this->dateFrom = $this->dateTo = '';
        }
        $this->resetPage();
    }

    public function render()
    {
        $bookings = Booking::query()
            ->when($this->filterUserId, fn($q) => $q->where('user_id', $this->filterUserId))
            ->when($this->search, fn($q) => $q->where(function ($b) {
                $s = $this->search;
                $b->where('booking_number',    'ILIKE', "%{$s}%")
                  ->orWhere('booker_first_name','ILIKE', "%{$s}%")
                  ->orWhere('booker_last_name', 'ILIKE', "%{$s}%")
                  ->orWhere('booker_mobile',    'ILIKE', "%{$s}%");
            }))
            ->when($this->statusFilter, fn($q) => $q->where('booking_status', $this->statusFilter))
            ->when($this->typeFilter,   fn($q) => $q->where('booking_type',   $this->typeFilter))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->with(['payment', 'passengers', 'flightDetail'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Pre-compute monthly numbers for agent role (N queries, max 20 per page)
        $monthlyNumbers = [];
        if (Auth::user()->role === 'agent') {
            foreach ($bookings as $b) {
                $monthlyNumbers[$b->id] = Booking::withTrashed()
                    ->where('user_id', $b->user_id)
                    ->whereYear('created_at', $b->created_at->year)
                    ->whereMonth('created_at', $b->created_at->month)
                    ->where('booking_number', '<=', $b->booking_number)
                    ->count();
            }
        }

        return view('livewire.booking-index', [
            'bookings'       => $bookings,
            'context'        => $this->context,
            'monthlyNumbers' => $monthlyNumbers,
        ]);
    }
}
