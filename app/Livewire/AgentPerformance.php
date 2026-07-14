<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Performance only counts bookings issued as full payment. "Invoiced" is
     * included because a booking can only be invoiced after it was issued as
     * full payment (see Booking::canInvoice / BookingPolicy) — it's the same
     * fully-settled sale, just further along in accounts' pipeline. Payment
     * plan / payment awaiting (still owed) are excluded on purpose.
     */
    private function soldStatuses(): array
    {
        return [
            Booking::STATUS_ISSUED,
            Booking::STATUS_INVOICED,
        ];
    }

    /** Booking-level rows for the current scope (selected agent or all agents). */
    private function rows()
    {
        [$from, $to] = $this->dateRange();
        $agentId = $this->effectiveAgentId();

        return Booking::query()
            ->when($agentId, fn ($q) => $q->where('user_id', $agentId))
            ->whereIn('booking_status', $this->soldStatuses())
            // last_issue_date is stamped when a booking is actually issued —
            // the true "sold" date, unlike created_at which is when the
            // booking record was first opened (often well before or after).
            ->whereDate('last_issue_date', '>=', $from)
            ->whereDate('last_issue_date', '<=', $to)
            ->with(['flightDetail', 'passengers', 'payment', 'paymentHistory', 'user'])
            ->orderByDesc('last_issue_date')
            ->get()
            ->map(function (Booking $b) {
                $sold = (float) $b->total_sale_price;
                $cost = (float) $b->total_cost_price;
                $cc   = (float) ($b->payment?->cc_charges ?? 0);
                $fd   = $b->flightDetail;

                // booking_payments.amount_paid isn't reliably kept in sync —
                // totalReceived() (the approved payment-history ledger, same
                // source the booking page itself uses) is the trustworthy figure.
                $paid = $b->totalReceived();

                // The real customer identity is the booker fields on the booking
                // itself (same fields the Customers page groups by) — customer_id
                // is a legacy relation that points at a single unused placeholder row.
                $customerName = trim(($b->booker_first_name ?? '') . ' ' . ($b->booker_last_name ?? ''));

                return [
                    'booking'    => $b,
                    'date'       => $b->last_issue_date,
                    'route'      => ($fd && $fd->departure_airport && $fd->arrival_airport)
                        ? $fd->departure_airport . ' → ' . $fd->arrival_airport
                        : null,
                    'customer'   => $customerName !== '' ? $customerName : '—',
                    'agent'      => $b->user?->name ?: '—',
                    'cost'       => $cost,
                    'sold'       => $sold,
                    'marginCc'   => $sold - $cost - $cc,   // margin after credit-card charges
                    'marginNoCc' => $sold - $cost,         // gross margin, CC charges ignored
                    'paid'       => $paid,
                    'balance'    => max(0.0, $sold - $paid),
                ];
            });
    }

    public function render()
    {
        $rows = $this->rows();

        return view('livewire.agent-performance', [
            'rows'       => $rows,
            'totals'     => [
                'count'      => $rows->count(),
                'cost'       => (float) $rows->sum('cost'),
                'sold'       => (float) $rows->sum('sold'),
                'marginCc'   => (float) $rows->sum('marginCc'),
                'marginNoCc' => (float) $rows->sum('marginNoCc'),
                'paid'       => (float) $rows->sum('paid'),
                'balance'    => (float) $rows->sum('balance'),
            ],
            'showAgent'  => $this->canViewAll && empty($this->effectiveAgentId()),
            'agentUsers' => $this->canViewAll ? $this->agentUsers() : collect(),
            'monthLabel' => Carbon::createFromFormat('Y-m', $this->effectiveMonth())->format('F Y'),
        ]);
    }
}
