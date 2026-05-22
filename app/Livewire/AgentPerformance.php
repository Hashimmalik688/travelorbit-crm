<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AgentPerformance extends Component
{
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getAgentStatsProperty()
    {
        $agents = User::whereIn('role', ['agent', 'operations', 'manager'])->get();

        return $agents->map(function ($agent) {
            $totals = DB::table('booking_flight_details')
                ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
                ->leftJoin('booking_flight_costs', 'booking_flight_costs.booking_id', '=', 'bookings.id')
                ->where('bookings.user_id', $agent->id)
                ->when($this->dateFrom, fn($q) => $q->whereDate('bookings.created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($q) => $q->whereDate('bookings.created_at', '<=', $this->dateTo))
                ->selectRaw('COUNT(DISTINCT bookings.id) as total_bookings')
                ->selectRaw('SUM(booking_flight_details.selling_price) as total_revenue')
                ->selectRaw('SUM(booking_flight_details.selling_price - COALESCE(booking_flight_costs.cost * booking_flight_costs.quantity, 0)) as total_margin')
                ->selectRaw('AVG(booking_flight_details.selling_price - COALESCE(booking_flight_costs.cost * booking_flight_costs.quantity, 0)) as avg_margin')
                ->first();

            $bookings = Booking::where('user_id', $agent->id)
                ->with('passengers')
                ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->get();

            $statusCounts = $bookings->groupBy('booking_status')->map->count();

            $topRoute = null;
            $flightDetails = $bookings->load('flightDetail')->pluck('flightDetail')->filter();
            if ($flightDetails->isNotEmpty()) {
                $topRoute = $flightDetails
                    ->filter(fn($fd) => $fd && $fd->departure_airport && $fd->arrival_airport)
                    ->groupBy(fn($fd) => $fd->departure_airport . ' → ' . $fd->arrival_airport)
                    ->sortByDesc(fn($g) => $g->count())
                    ->keys()
                    ->first();
            }

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'role' => $agent->role,
                'totalBookings' => (int) ($totals->total_bookings ?? 0),
                'totalRevenue' => (float) ($totals->total_revenue ?? 0),
                'totalMargin' => (float) ($totals->total_margin ?? 0),
                'avgMargin' => (float) ($totals->avg_margin ?? 0),
                'statusBreakdown' => $statusCounts,
                'topRoute' => $topRoute,
            ];
        })->sortByDesc('totalBookings')->values();
    }

    public function getChartDataProperty()
    {
        $stats = $this->agentStats;

        return [
            'labels' => $stats->pluck('name')->toArray(),
            'revenue' => $stats->pluck('totalRevenue')->toArray(),
            'margin' => $stats->pluck('totalMargin')->toArray(),
            'bookings' => $stats->pluck('totalBookings')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.agent-performance', [
            'stats' => $this->agentStats,
            'chartData' => $this->chartData,
        ]);
    }
}
