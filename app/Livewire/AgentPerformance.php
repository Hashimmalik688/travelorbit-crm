<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingMarginShare;
use App\Models\MarginClaim;
use App\Models\MarginDeduction;
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

    /** Selected booking type. '' = all types. */
    public string $typeFilter = '';

    // ── Deduction modal ──────────────────────────────────────────────
    public bool $deductionOpen = false;
    public $deductionAmount = '';
    public $deductionReason = '';
    public $deductionNote = '';
    public $deductionDate = '';

    public function mount(): void
    {
        $user = Auth::user();
        // The all-agents view is its own permission now, independent of the
        // global data.view_all scope flag: reports.performance_all sees every
        // agent; a plain reports.performance holder is locked to their own.
        $this->canViewAll = $user->hasPermission('reports.performance_all');
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
     * Performance only counts bookings whose money is all in: plain "Issued"
     * (issued on full payment, or issued on a plan and since invoiced —
     * invoicing moves a booking to Issued, see BookingWorkflowController::invoice)
     * and the legacy "Invoiced" status for bookings invoiced before that rule.
     * Payment plan / payment awaiting are still owed money and earn no margin
     * in this or any month until they're invoiced.
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
            ->when($this->typeFilter, fn ($q) => $q->where('booking_type', $this->typeFilter))
            ->whereIn('booking_status', $this->soldStatuses())
            // last_issue_date is stamped when a booking is actually issued —
            // the true "sold" date, unlike created_at which is when the
            // booking record was first opened (often well before or after).
            ->whereDate('last_issue_date', '>=', $from)
            ->whereDate('last_issue_date', '<=', $to)
            ->with(['flightDetail', 'passengers', 'payment', 'user', 'marginShares'])
            ->orderByDesc('last_issue_date')
            ->get()
            ->map(function (Booking $b) {
                $sold = (float) $b->total_sale_price;
                $cost = (float) $b->total_cost_price;
                $cc   = (float) ($b->payment?->cc_charges ?? 0);
                $fd   = $b->flightDetail;

                // Margin given away to another agent on this booking (owner's side of a share).
                $sharedOut = (float) $b->marginShares->sum('amount');

                // The real customer identity is the booker fields on the booking
                // itself (same fields the Customers page groups by) — customer_id
                // is a legacy relation that points at a single unused placeholder row.
                $customerName = trim(($b->booker_first_name ?? '') . ' ' . ($b->booker_last_name ?? ''));

                return [
                    'booking'      => $b,
                    'date'         => $b->last_issue_date,
                    'bookingNumber'=> $b->booking_number,
                    'type'         => $b->booking_type,
                    'isDateChange' => $b->lead_nature === 'date_change',
                    'route'        => ($fd && $fd->departure_airport && $fd->arrival_airport)
                        ? $fd->departure_airport . ' → ' . $fd->arrival_airport
                        : null,
                    'locator'      => $fd?->locator ?: '—',
                    'airline'      => $fd?->airline ?: '—',
                    'supplier'     => $fd?->vendor ?: '—',
                    'customer'     => $customerName !== '' ? $customerName : '—',
                    'agent'        => $b->user?->name ?: '—',
                    'cost'         => $cost,
                    'sold'         => $sold,
                    'ccCharges'    => $cc,
                    'marginCc'     => $sold - $cost - $cc,   // margin after credit-card charges
                    'marginNoCc'   => $sold - $cost,         // gross margin, CC charges ignored
                    'sharedOut'    => $sharedOut,
                ];
            });
    }

    /**
     * Margin this agent received from OTHER agents' bookings via a share —
     * only meaningful for a single-agent view, since across "all agents" it
     * nets to zero (one agent's shared-out is another's shared-in).
     */
    private function sharedInTotal(): float
    {
        $agentId = $this->effectiveAgentId();
        if (! $agentId) {
            return 0.0;
        }

        [$from, $to] = $this->dateRange();

        return (float) BookingMarginShare::query()
            ->where('shared_with_user_id', $agentId)
            ->whereHas('booking', function ($q) use ($from, $to) {
                $q->whereIn('booking_status', $this->soldStatuses())
                  ->whereDate('last_issue_date', '>=', $from)
                  ->whereDate('last_issue_date', '<=', $to);
            })
            ->sum('amount');
    }

    // ── Margin deductions (manager-applied docks) ────────────────────
    public function canApplyDeduction(): bool
    {
        return Auth::user()->hasPermission('reports.apply_deduction');
    }

    private function deductionsQuery()
    {
        [$from, $to] = $this->dateRange();
        $agentId = $this->effectiveAgentId();

        return MarginDeduction::query()
            ->when($agentId, fn ($q) => $q->where('user_id', $agentId))
            ->whereDate('deduction_date', '>=', $from)
            ->whereDate('deduction_date', '<=', $to);
    }

    private function deductionsTotal(): float
    {
        return (float) $this->deductionsQuery()->sum('amount');
    }

    private function deductionsList()
    {
        return $this->deductionsQuery()
            ->with(['user', 'appliedBy'])
            ->orderByDesc('deduction_date')
            ->get();
    }

    // ── Margin claims (auto-created when accounts approves a Refund to
    // Customer payout that keeps some of what the supplier sent back —
    // see PaymentChargeRequest::recordMarginClaim) ────────────────────
    /**
     * Only 'released' claims count — 'pending' ones are still sitting on the
     * M&R Auth Queue awaiting a manager's release/hold decision, and 'held'
     * ones were deliberately kept back (see RefundAuthQueue::approveMargin/holdMargin).
     */
    private function claimsQuery()
    {
        [$from, $to] = $this->dateRange();
        $agentId = $this->effectiveAgentId();

        return MarginClaim::query()
            ->where('status', 'released')
            ->when($agentId, fn ($q) => $q->where('user_id', $agentId))
            ->whereDate('claim_date', '>=', $from)
            ->whereDate('claim_date', '<=', $to);
    }

    private function claimsTotal(): float
    {
        return (float) $this->claimsQuery()->sum('amount');
    }

    public function openDeduction(): void
    {
        abort_unless($this->canApplyDeduction(), 403);
        abort_if(empty($this->effectiveAgentId()), 400, 'Select a specific agent before applying a deduction.');

        $this->deductionAmount = '';
        $this->deductionReason = '';
        $this->deductionNote = '';
        $this->deductionDate = now()->toDateString();
        $this->resetErrorBag();
        $this->deductionOpen = true;
    }

    public function saveDeduction(): void
    {
        abort_unless($this->canApplyDeduction(), 403);
        $agentId = $this->effectiveAgentId();
        abort_if(empty($agentId), 400, 'Select a specific agent before applying a deduction.');

        $this->validate([
            'deductionAmount' => 'required|numeric|min:0.01',
            'deductionReason' => 'required|string|max:255',
            'deductionDate'   => 'required|date',
            'deductionNote'   => 'nullable|string|max:255',
        ]);

        MarginDeduction::create([
            'user_id'            => $agentId,
            'applied_by_user_id' => Auth::id(),
            'amount'             => $this->deductionAmount,
            'reason'             => $this->deductionReason,
            'deduction_date'     => $this->deductionDate,
            'note'               => $this->deductionNote ?: null,
        ]);

        $this->deductionOpen = false;
        session()->flash('success', 'Deduction applied.');
    }

    public function removeDeduction(int $deductionId): void
    {
        abort_unless($this->canApplyDeduction(), 403);

        MarginDeduction::whereKey($deductionId)->delete();
        session()->flash('success', 'Deduction removed.');
    }

    /** Counts by lead source / booking type, for the breakdown strip. */
    private function breakdown($rows): array
    {
        $bookings = $rows->pluck('booking');
        // Date change / refund follow-ups aren't new sales — they stay in the
        // table and margin totals, they just don't inflate these count chips.
        $countableBookings = $bookings->whereNotIn('lead_nature', ['date_change', 'refund_booking']);

        $leadSourceLabels = [
            'to_returning'     => 'TO Returning',
            'to_referral'      => 'TO Referral',
            'returning_client' => 'Returning Client',
            'referral_client'  => 'Referral Client',
            'fb'               => 'Facebook',
            'wa'               => 'WhatsApp',
            'email'            => 'Email',
            'diaspora_group'   => 'Diaspora Group',
            'instagram'        => 'Instagram',
            'tiktok'           => 'TikTok',
            'website'          => 'Website',
            'google'           => 'Google',
            'personal'         => 'Personal',
        ];

        $bySource = $countableBookings->countBy(fn ($b) => $b->lead_source)->all();
        $leadSource = [];
        foreach ($leadSourceLabels as $key => $label) {
            if (!empty($bySource[$key])) {
                $leadSource[$label] = $bySource[$key];
            }
        }

        return [
            'leadSource'  => $leadSource,
            'bookingType' => $this->typeCounts(),
        ];
    }

    /**
     * Booking counts by type, for the breakdown strip — deliberately ignores
     * $typeFilter itself (but keeps agent/month/status) so picking one type
     * doesn't collapse this down to a single count; you can still see how
     * many bookings fall in every other type at the same time. Always
     * includes every canonical type (0 when there are none) so the filter
     * buttons stay visible and clickable regardless of what has data.
     */
    private function typeCounts(): array
    {
        [$from, $to] = $this->dateRange();
        $agentId = $this->effectiveAgentId();

        $byType = Booking::query()
            ->when($agentId, fn ($q) => $q->where('user_id', $agentId))
            ->whereIn('booking_status', $this->soldStatuses())
            ->whereDate('last_issue_date', '>=', $from)
            ->whereDate('last_issue_date', '<=', $to)
            ->whereNotIn('lead_nature', ['date_change', 'refund_booking'])
            ->get()
            ->countBy('booking_type');

        $labels = ['flight' => 'Flight', 'hotel' => 'Hotel', 'umrah' => 'Umrah', 'holiday' => 'Holiday', 'visa' => 'Visa', 'transfers' => 'Transfers', 'excursion' => 'Excursion'];

        $bookingType = [];
        foreach ($labels as $key => $label) {
            $bookingType[$label] = (int) ($byType[$key] ?? 0);
        }

        return $bookingType;
    }

    public function render()
    {
        $rows        = $this->rows();
        $sharedOut   = (float) $rows->sum('sharedOut');
        $sharedIn    = $this->sharedInTotal();
        $marginCc    = (float) $rows->sum('marginCc');
        $marginNoCc  = (float) $rows->sum('marginNoCc');
        $deductions  = $this->deductionsTotal();
        $claims      = $this->claimsTotal();

        // Net Booking = Total Booking - Date Change - Cancellation - Full
        // Refund - Refund Booking. A booking is excluded once even if it
        // matches more than one of those reasons (e.g. a refund_booking that
        // also ended up fully refunded), so this can never go negative.
        // Cancelled bookings never actually reach $rows (only issued/invoiced
        // statuses do — see soldStatuses()), so that term is a no-op today,
        // kept for when a booking's status changes after being counted here.
        $netBookingCount = $rows->reject(fn ($r) => $r['isDateChange']
            || $r['booking']->booking_status === Booking::STATUS_CANCELLED
            || $r['booking']->lead_nature === 'refund_booking'
            || $r['booking']->isFullyRefunded()
        )->count();

        return view('livewire.agent-performance', [
            'rows'       => $rows,
            'totals'     => [
                'count'         => $rows->count(),
                'netCount'      => $netBookingCount,
                'cost'          => (float) $rows->sum('cost'),
                'sold'          => (float) $rows->sum('sold'),
                'ccCharges'     => (float) $rows->sum('ccCharges'),
                'marginCc'      => $marginCc,
                'marginNoCc'    => $marginNoCc,
                'sharedOut'     => $sharedOut,
                'sharedIn'      => $sharedIn,
                'deductions'    => $deductions,
                'claims'        => $claims,
                'netMarginCc'   => $marginCc - $sharedOut + $sharedIn - $deductions + $claims,
                'netMarginNoCc' => $marginNoCc - $sharedOut + $sharedIn - $deductions + $claims,
            ],
            'deductionsList'     => $this->deductionsList(),
            'canApplyDeduction'  => $this->canApplyDeduction(),
            'breakdown'  => $this->breakdown($rows),
            'showAgent'  => $this->canViewAll && empty($this->effectiveAgentId()),
            'agentUsers' => $this->canViewAll ? $this->agentUsers() : collect(),
            'monthLabel' => Carbon::createFromFormat('Y-m', $this->effectiveMonth())->format('F Y'),
        ]);
    }
}
