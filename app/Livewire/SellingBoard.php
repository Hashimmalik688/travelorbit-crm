<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class SellingBoard extends Component
{
    public function render()
    {
        // Same "who actually creates bookings" scope as the rest of the
        // Operations Centre dashboard — admin excluded.
        $allAgents = User::whereIn('role', ['agent', 'operations', 'manager'])
            ->withCount(['bookings as today_bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->get();

        $sellingToday = $allAgents->where('today_bookings', '>', 0)->sortByDesc('today_bookings')->values();
        $chillToday   = $allAgents->where('today_bookings', '=', 0)->sortBy('name')->values();

        return view('livewire.selling-board', compact('sellingToday', 'chillToday'));
    }
}
