<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Customer;
use Livewire\Component;

class GlobalSearch extends Component
{
    public $query = '';
    public $isOpen = false;

    public function updatedQuery(): void
    {
        $this->isOpen = strlen($this->query) >= 2;
    }

    public function closeDropdown(): void
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function render()
    {
        $results = ['bookings' => collect(), 'customers' => collect()];

        if (strlen($this->query) >= 2) {
            $results['bookings'] = Booking::query()
                ->where('booking_number', 'ILIKE', "%{$this->query}%")
                ->orWhere('booker_name', 'ILIKE', "%{$this->query}%")
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $results['customers'] = Customer::query()
                ->where('name', 'ILIKE', "%{$this->query}%")
                ->orWhere('phone', 'ILIKE', "%{$this->query}%")
                ->limit(5)
                ->get();
        }

        return view('livewire.global-search', $results);
    }
}
