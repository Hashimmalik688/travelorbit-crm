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
    public string $search        = '';
    // Filters on payment.booking_plan (Full Payment / Payment Awaiting / Payment
    // Plan / DNPL), not booking_status — 'pending' means no payment structure
    // has been chosen yet (see BookingPayment::booking_plan, empty by default
    // until CreateBooking::save() or the booking's own Payment Structure panel
    // sets it).
    public string $paymentFilter = '';
    public string $typeFilter    = '';
    public string $dateFrom      = '';
    public string $dateTo        = '';

    // Context: 'all' or 'mine'
    public string $context      = 'all';
    public ?int  $filterUserId  = null;

    protected $queryString = [
        'search'        => ['except' => ''],
        'paymentFilter' => ['except' => ''],
        'typeFilter'    => ['except' => ''],
        'dateFrom'      => ['except' => ''],
        'dateTo'        => ['except' => ''],
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

    public function updatingSearch(): void         { $this->resetPage(); }
    public function updatingPaymentFilter(): void  { $this->resetPage(); }
    public function updatingTypeFilter(): void     { $this->resetPage(); }
    public function updatingDateFrom(): void       { $this->resetPage(); }
    public function updatingDateTo(): void         { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = $this->paymentFilter = $this->typeFilter = '';
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
            ->when($this->paymentFilter, function ($q) {
                if ($this->paymentFilter === 'pending') {
                    // No payment structure chosen yet — either no payment row
                    // at all, or one whose booking_plan was never set.
                    $q->where(function ($qq) {
                        $qq->whereDoesntHave('payment')
                           ->orWhereHas('payment', fn ($p) => $p->where('booking_plan', ''));
                    });
                } else {
                    $q->whereHas('payment', fn ($p) => $p->where('booking_plan', $this->paymentFilter));
                }
            })
            ->when($this->typeFilter,   fn($q) => $q->where('booking_type',   $this->typeFilter))
            ->when($this->dateFrom,     fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,       fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->with(['payment', 'passengers', 'flightDetail', 'user', 'refunds', 'paymentHistory'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.booking-index', [
            'bookings' => $bookings,
            'context'  => $this->context,
        ]);
    }
}
