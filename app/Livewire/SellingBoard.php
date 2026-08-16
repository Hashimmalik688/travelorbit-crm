<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class SellingBoard extends Component
{
    public function render()
    {
        // Agent role only — the leaderboard is an agents' competition, so
        // managers/operations don't belong on it (matches Agents Performance,
        // which is also agent-only).
        $allAgents = User::where('role', 'agent')
            ->withCount(['bookings as today_bookings' => fn ($q) => $q->whereDate('created_at', today())
                ->whereNotIn('lead_nature', ['date_change', 'refund_booking'])])
            ->get();

        $sellingToday = $allAgents->where('today_bookings', '>', 0)->sortByDesc('today_bookings')->values();
        $chillToday   = $allAgents->where('today_bookings', '=', 0)->sortBy('name')->values();

        return view('livewire.selling-board', compact('sellingToday', 'chillToday'));
    }
}
