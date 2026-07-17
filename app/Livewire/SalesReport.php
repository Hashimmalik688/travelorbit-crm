<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
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

    private ?\Illuminate\Support\Collection $filteredBookingsCache = null;

    /**
     * The old version of this report summed booking_flight_details.selling_price
     * via a raw join — an INNER join, so any booking_type without a flight
     * segment (hotel/visa/transfers/excursion-only bookings) was silently
     * dropped from every total, even though it appeared fine in the table
     * below. Aggregating over the Booking model's own total_sale_price /
     * total_cost_price accessors (the same figures the booking detail page
     * shows) fixes that and makes every booking type report real totals.
     */
    private function filteredBookings()
    {
        return $this->filteredBookingsCache ??= Booking::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->bookingType, fn($q) => $q->where('booking_type', $this->bookingType))
            ->when($this->agentId, fn($q) => $q->where('user_id', $this->agentId))
            ->with(['flightDetails', 'hotels', 'visas', 'transfers', 'passengers', 'user'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getSummaryProperty()
    {
        $bookings = $this->filteredBookings();
        $count = $bookings->count();
        $margin = (float) $bookings->sum(fn (Booking $b) => $b->total_margin);

        return [
            'totalBookings' => $count,
            'totalRevenue' => (float) $bookings->sum(fn (Booking $b) => $b->total_sale_price),
            'totalCost' => (float) $bookings->sum(fn (Booking $b) => $b->total_cost_price),
            'totalMargin' => $margin,
            'avgMargin' => $count > 0 ? $margin / $count : 0,
        ];
    }

    /**
     * Booking counts by type, for the breakdown strip — deliberately ignores
     * the bookingType filter itself (but keeps date/agent) so switching the
     * type filter doesn't collapse this down to a single count; you can still
     * see how many bookings fall in every other type at the same time.
     */
    public function getTypeCountsProperty()
    {
        return Booking::query()
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->agentId, fn($q) => $q->where('user_id', $this->agentId))
            ->get()
            ->countBy('booking_type');
    }

    public function getChartDataProperty()
    {
        $grouped = $this->filteredBookings()
            ->groupBy(fn (Booking $b) => $b->created_at->format('Y-m'))
            ->sortKeys();

        return [
            'labels' => $grouped->keys()->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'))->toArray(),
            'revenue' => $grouped->map(fn($g) => (float) $g->sum(fn (Booking $b) => $b->total_sale_price))->values()->toArray(),
            'margin' => $grouped->map(fn($g) => (float) $g->sum(fn (Booking $b) => $b->total_margin))->values()->toArray(),
        ];
    }

    public function getBookingsProperty()
    {
        return $this->filteredBookings();
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
            'typeCounts' => $this->typeCounts,
            'agents' => $this->getAgentUsers(),
        ]);
    }
}
