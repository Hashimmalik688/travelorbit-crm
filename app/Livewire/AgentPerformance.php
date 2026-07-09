<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AgentPerformance extends Component
{
    /** Full access (all agents, any month) vs. self-only (own data, current/previous month). */
    public bool $canViewAll = false;

    /** Selected month as 'Y-m'. */
    public string $month = '';

    /** Selected agent id. '' = all agents. Forced to the viewer's own id for self-only roles. */
    public $agentId = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->canViewAll = $user->canViewAllData();
        $this->month = now()->format('Y-m');

        // Self-only roles (agent, operations) are locked to their own data.
        if (! $this->canViewAll) {
            $this->agentId = $user->id;
        }
    }

    /** Month toggle for self-only roles — only the current or previous month is allowed. */
    public function setMonth(string $key): void
    {
        $this->month = $key === 'last'
            ? now()->subMonthNoOverflow()->format('Y-m')
            : now()->format('Y-m');
    }

    /** The two months a self-only role may view. */
    protected function allowedSelfMonths(): array
    {
        return [
            now()->format('Y-m'),
            now()->subMonthNoOverflow()->format('Y-m'),
        ];
    }

    /** Effective, tamper-proof agent id for the current viewer. */
    protected function effectiveAgentId()
    {
        return $this->canViewAll ? $this->agentId : Auth::id();
    }

    /** Effective, tamper-proof month (self-only roles are restricted to current/previous). */
    protected function effectiveMonth(): string
    {
        if ($this->canViewAll) {
            return preg_match('/^\d{4}-\d{2}$/', $this->month) ? $this->month : now()->format('Y-m');
        }

        return in_array($this->month, $this->allowedSelfMonths(), true)
            ? $this->month
            : now()->format('Y-m');
    }

    /** [start, end] date strings for the selected month. */
    protected function dateRange(): array
    {
        $start = Carbon::createFromFormat('Y-m', $this->effectiveMonth())->startOfMonth();

        return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
    }

    private function agentUsers()
    {
        return User::whereIn('role', ['agent', 'operations', 'manager'])
            ->orderBy('name')
            ->get();
    }

    /** Aggregate booking totals for a given agent (or all when $agentId is empty). */
    private function statsFor($agentId, string $from, string $to)
    {
        return DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->leftJoin('booking_flight_costs', 'booking_flight_costs.booking_id', '=', 'bookings.id')
            ->when($agentId, fn ($q) => $q->where('bookings.user_id', $agentId))
            ->whereDate('bookings.created_at', '>=', $from)
            ->whereDate('bookings.created_at', '<=', $to)
            ->selectRaw('COUNT(DISTINCT bookings.id) as total_bookings')
            ->selectRaw('SUM(booking_flight_details.selling_price) as total_revenue')
            ->selectRaw('SUM(booking_flight_details.selling_price - COALESCE(booking_flight_costs.cost * booking_flight_costs.quantity, 0)) as total_margin')
            ->first();
    }

    private function shape($totals): array
    {
        $bookings = (int) ($totals->total_bookings ?? 0);
        $margin   = (float) ($totals->total_margin ?? 0);

        return [
            'totalBookings' => $bookings,
            'totalRevenue'  => (float) ($totals->total_revenue ?? 0),
            'totalMargin'   => $margin,
            'avgMargin'     => $bookings > 0 ? $margin / $bookings : 0,
        ];
    }

    /** Summary totals for the current scope (selected agent or all agents). */
    public function getSummaryProperty(): array
    {
        [$from, $to] = $this->dateRange();

        return $this->shape($this->statsFor($this->effectiveAgentId(), $from, $to));
    }

    /** Per-agent leaderboard — only used when no single agent is in scope. */
    public function getLeaderboardProperty()
    {
        [$from, $to] = $this->dateRange();

        return $this->agentUsers()->map(function ($agent) use ($from, $to) {
            return array_merge([
                'id'    => $agent->id,
                'name'  => $agent->name,
                'role'  => $agent->role,
                'photo' => $agent->profile_photo_path,
            ], $this->shape($this->statsFor($agent->id, $from, $to)));
        })->sortByDesc('totalBookings')->values();
    }

    /** The single user whose detail we're showing, or null when viewing all agents. */
    public function getSingleAgentProperty(): ?User
    {
        $id = $this->effectiveAgentId();

        return $id ? User::find($id) : null;
    }

    /** Detailed booking list for the single agent in scope. */
    public function getBookingsProperty()
    {
        $id = $this->effectiveAgentId();

        if (! $id) {
            return collect();
        }

        [$from, $to] = $this->dateRange();

        return Booking::where('user_id', $id)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->with(['flightDetail', 'passengers'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function render()
    {
        $single = $this->singleAgent;

        return view('livewire.agent-performance', [
            'summary'     => $this->summary,
            'singleAgent' => $single,
            'leaderboard' => $single ? collect() : $this->leaderboard,
            'bookings'    => $single ? $this->bookings : collect(),
            'agentUsers'  => $this->canViewAll ? $this->agentUsers() : collect(),
            'monthLabel'  => Carbon::createFromFormat('Y-m', $this->effectiveMonth())->format('F Y'),
        ]);
    }
}
