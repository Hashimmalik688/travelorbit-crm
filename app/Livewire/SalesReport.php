<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SalesReport extends Component
{
    public $dateFrom;
    public $dateTo;
    public $bookingType = '';
    public $agentId = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getSummaryProperty()
    {
        $totals = DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->leftJoin('booking_flight_costs', 'booking_flight_costs.booking_id', '=', 'bookings.id')
            ->when($this->dateFrom, fn($q) => $q->whereDate('bookings.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('bookings.created_at', '<=', $this->dateTo))
            ->when($this->bookingType, fn($q) => $q->where('bookings.booking_type', $this->bookingType))
            ->when($this->agentId, fn($q) => $q->where('bookings.user_id', $this->agentId))
            ->selectRaw('COUNT(DISTINCT bookings.id) as total_bookings')
            ->selectRaw('SUM(booking_flight_details.selling_price) as total_revenue')
            ->selectRaw('SUM(COALESCE(booking_flight_costs.cost * booking_flight_costs.quantity, 0)) as total_cost')
            ->selectRaw('SUM(booking_flight_details.selling_price - COALESCE(booking_flight_costs.cost * booking_flight_costs.quantity, 0)) as total_margin')
            ->first();

        return [
            'totalBookings' => (int) ($totals->total_bookings ?? 0),
            'totalRevenue' => (float) ($totals->total_revenue ?? 0),
            'totalCost' => (float) ($totals->total_cost ?? 0),
            'totalMargin' => (float) ($totals->total_margin ?? 0),
            'avgMargin' => $totals->total_bookings > 0 ? ($totals->total_margin / $totals->total_bookings) : 0,
        ];
    }

    public function getChartDataProperty()
    {
        $data = DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->leftJoin('booking_flight_costs', 'booking_flight_costs.booking_id', '=', 'bookings.id')
            ->when($this->dateFrom, fn($q) => $q->whereDate('bookings.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('bookings.created_at', '<=', $this->dateTo))
            ->when($this->bookingType, fn($q) => $q->where('bookings.booking_type', $this->bookingType))
            ->when($this->agentId, fn($q) => $q->where('bookings.user_id', $this->agentId))
            ->selectRaw("TO_CHAR(bookings.created_at, 'YYYY-MM') as month")
            ->selectRaw('SUM(booking_flight_details.selling_price) as revenue')
            ->selectRaw('SUM(booking_flight_details.selling_price - COALESCE(booking_flight_costs.cost * booking_flight_costs.quantity, 0)) as margin')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $data->pluck('month')->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'))->toArray(),
            'revenue' => $data->pluck('revenue')->toArray(),
            'margin' => $data->pluck('margin')->toArray(),
        ];
    }

    public function getBookingsProperty()
    {
        return Booking::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->bookingType, fn($q) => $q->where('booking_type', $this->bookingType))
            ->when($this->agentId, fn($q) => $q->where('user_id', $this->agentId))
            ->with(['user', 'passengers'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function getAgentUsers()
    {
        return User::whereIn('role', ['agent', 'operations', 'manager'])->get();
    }

    public function exportCsv()
    {
        $bookings = $this->getBookingsProperty();

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Booking#', 'Booker', 'Routes', 'Type', 'Sale Price', 'Cost', 'Margin', 'Pax', 'Status', 'Agent', 'Date']);

        foreach ($bookings as $b) {
            $routes = '';
            if ($b->flightDetail && $b->flightDetail->departure_airport && $b->flightDetail->arrival_airport) {
                $routes = $b->flightDetail->departure_airport . ' → ' . $b->flightDetail->arrival_airport;
            }

            fputcsv($output, [
                $b->booking_number,
                $b->booker_name,
                $routes ?: '',
                $b->booking_type,
                $b->total_sale_price,
                $b->total_cost_price,
                $b->total_margin,
                $b->passengers->count(),
                $b->booking_status,
                $b->user->name ?? '',
                $b->created_at->format('Y-m-d'),
            ]);
        }

        fclose($output);
    }

    public function render()
    {
        return view('livewire.sales-report', [
            'summary' => $this->summary,
            'chartData' => $this->chartData,
            'bookings' => $this->bookings,
            'agents' => $this->getAgentUsers(),
        ]);
    }
}
