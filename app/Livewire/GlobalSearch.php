<?php

namespace App\Livewire;

use App\Models\Booking;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';
    public string $filter = 'booking_reference';
    public bool $isOpen = false;

    public function updatedQuery(): void
    {
        $this->isOpen = strlen($this->query) >= 1;
    }

    public function updatedFilter(): void
    {
        $this->isOpen = strlen($this->query) >= 1;
    }

    public function closeDropdown(): void
    {
        $this->isOpen = false;
        $this->query  = '';
    }

    public function render()
    {
        $bookings = collect();

        if (strlen($this->query) >= 1) {
            $q = $this->query;

            $bookings = match ($this->filter) {
                'booking_reference' => Booking::query()
                    ->where('booking_number', 'ILIKE', "%{$q}%")
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(),

                'name' => Booking::query()
                    ->where(function ($b) use ($q) {
                        $b->where('booker_first_name', 'ILIKE', "%{$q}%")
                          ->orWhere('booker_last_name', 'ILIKE', "%{$q}%");
                    })
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(),

                'locator' => Booking::query()
                    ->whereHas('flightDetail', fn($f) => $f->where('locator', 'ILIKE', "%{$q}%"))
                    ->with('flightDetail')
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(),

                'email' => Booking::query()
                    ->where('booker_email', 'ILIKE', "%{$q}%")
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(),

                'phone' => Booking::query()
                    ->where('booker_mobile', 'ILIKE', "%{$q}%")
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(),

                'airline_reference' => Booking::query()
                    ->whereHas('flightDetail', fn($f) => $f->where('airline_locator', 'ILIKE', "%{$q}%"))
                    ->with('flightDetail')
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(),

                default => collect(),
            };
        }

        return view('livewire.global-search', compact('bookings'));
    }
}
