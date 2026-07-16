<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function agentDashboardPage()
    {
        return $this->agentDashboard();
    }

    public function accountsDashboardPage()  { return $this->accountsDashboard(); }
    public function issuanceDashboardPage()  { return $this->issuanceDashboard(); }

    public function paymentPlanReport()
    {
        return $this->issuedPaymentReport(
            'issued_payment_plan',
            'Payment Plan',
            'Your issued bookings still being paid off in instalments.'
        );
    }

    public function paymentAwaitingReport()
    {
        return $this->issuedPaymentReport(
            'issued_payment_awaiting',
            'Payment Awaiting',
            'Your issued bookings awaiting their outstanding balance.'
        );
    }

    protected function issuedPaymentReport(string $status, string $title, string $subtitle)
    {
        // No balance filter — a booking stays in its payment-plan/awaiting status
        // even once fully paid, so settled bookings stay visible here (shown
        // with a "Settled" highlight) rather than silently disappearing.
        $all = Booking::where('user_id', Auth::id())
            ->where('booking_status', $status)
            ->whereHas('payment')
            ->with(['user', 'flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
            ->get();

        $sorted = $this->sortByNextDueDate($all)->values();

        // Totals across the whole filtered list, not just the current page —
        // shown prominently at the top of the page, not buried in a footer.
        // Received/remaining come from the approved-payments ledger (same as
        // BookingShow's "Balance Due"), not booking_payments.amount_paid /
        // balance_remaining, which are a stale creation-time snapshot.
        $totals = [
            'cost'      => $sorted->sum('total_cost_price'),
            'sold'      => $sorted->sum('total_sale_price'),
            'margin'    => $sorted->sum(fn (Booking $b) => $b->netMargin()),
            'received'  => $sorted->sum(fn (Booking $b) => $b->totalReceived()),
            'remaining' => $sorted->sum(fn (Booking $b) => $b->total_sale_price - $b->totalReceived()),
        ];

        $perPage = 20;
        $page = (int) request('page', 1);
        $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('content.dashboard.issued-unpaid-report', compact('bookings', 'title', 'subtitle', 'totals'));
    }

    /**
     * Sorts bookings by their next unpaid payment date, soonest (or most overdue)
     * first. Bookings with no scheduled date, or that are actually fully paid
     * per the real payment ledger (regardless of what the stale due_date /
     * instalment JSON says), sort to the end.
     */
    protected function sortByNextDueDate($bookings)
    {
        return $bookings->sortBy(function (Booking $b) {
            if ($b->total_sale_price - $b->totalReceived() <= 0) {
                return PHP_INT_MAX;
            }

            $due = $b->payment?->nextDueDate();

            return $due ? $due->timestamp : PHP_INT_MAX;
        });
    }

    public function index()
    {
        $user = Auth::user();

        return match ($user?->role) {
            'agent', 'operations' => $this->agentDashboard(),
            'accounts'            => $this->accountsDashboard(),
            'issuance'            => $this->issuanceDashboard(),
            default               => $this->adminDashboard(),
        };
    }

    protected function adminDashboard()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalBookings = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        $totalSale = (float) DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->whereBetween('bookings.created_at', [$startOfMonth, $endOfMonth])
            ->whereNull('bookings.deleted_at')
            ->sum('booking_flight_details.selling_price');

        $totalSale += (float) DB::table('booking_hotels')
            ->join('bookings', 'bookings.id', '=', 'booking_hotels.booking_id')
            ->whereBetween('bookings.created_at', [$startOfMonth, $endOfMonth])
            ->whereNull('bookings.deleted_at')
            ->sum('booking_hotels.selling_price');

        $totalCost = (float) DB::table('booking_flight_costs')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_costs.booking_id')
            ->whereBetween('bookings.created_at', [$startOfMonth, $endOfMonth])
            ->whereNull('bookings.deleted_at')
            ->selectRaw('SUM(cost * quantity) as total')
            ->value('total');

        $totalRevenue = $totalSale - $totalCost;

        // Outstanding balance must come from the approved-payments ledger (the same
        // source as BookingShow's "Balance Due"), NOT booking_payments.balance_remaining,
        // which is a stale creation-time snapshot that is never updated as payments
        // are recorded — that snapshot inflates the figure by every payment taken
        // since the booking was created. We pre-filter on the snapshot only to bound
        // the set of candidate bookings (it is a superset of anything still owing),
        // then compute the real remaining per booking.
        $outstandingBookings = Booking::whereHas('payment', fn ($q) => $q->where('balance_remaining', '>', 0))
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
            ->get();

        $outstandingPayments = (float) $outstandingBookings->sum(
            fn (Booking $b) => max(0, $b->total_sale_price - $b->totalReceived())
        );

        $overduePaymentsCount = $outstandingBookings->filter(
            fn (Booking $b) => ($b->total_sale_price - $b->totalReceived()) > 0
                && $b->payment?->due_date
                && $b->payment->due_date->isPast()
        )->count();

        $bookingsByStatus = Booking::select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->pluck('total', 'booking_status')
            ->toArray();

        $pendingCount    = $bookingsByStatus['pending']   ?? 0;
        $confirmedCount  = $bookingsByStatus['confirmed'] ?? 0;
        $issuedCount     = $bookingsByStatus['issued']    ?? 0;
        $issuanceQueue   = $bookingsByStatus['issuance_queue'] ?? 0;
        $ticketInProcess = $bookingsByStatus['ticket_in_process'] ?? 0;
        $invoicedCount   = $bookingsByStatus['invoiced'] ?? 0;

        $topAgents = Booking::select('user_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->take(5)
            ->get();

        $last7DaysBookings = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $last7DaysBookings[] = Booking::whereDate('created_at', $date)->count();
        }

        $cancelledCount = $bookingsByStatus['cancelled'] ?? 0;

        // Leaderboard: only roles that actually create bookings — admin is excluded
        // (admins manage the system, they don't book).
        $allAgents = \App\Models\User::whereIn('role', ['agent', 'operations', 'manager'])->withCount([
            'bookings as month_bookings' => fn($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ])->get();

        // Agents Today: same section shown on the agent dashboard — every agent with
        // their booking count for today, so managers/admins can see who's active.
        $agentsToday = \App\Models\User::where('role', 'agent')
            ->withCount(['bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('name')
            ->get();

        return view('content.dashboard.dashboard', compact(
            'totalBookings', 'totalRevenue', 'outstandingPayments',
            'overduePaymentsCount', 'pendingCount', 'confirmedCount',
            'issuedCount', 'issuanceQueue', 'ticketInProcess', 'invoicedCount',
            'cancelledCount', 'topAgents', 'last7DaysBookings', 'allAgents', 'agentsToday'
        ));
    }

    protected function accountsDashboard()
    {
        $now = now();
        $som = $now->copy()->startOfMonth();
        $eom = $now->copy()->endOfMonth();

        $ticketsInProcess = Booking::where('booking_status', 'ticket_in_process')->count();
        $readyToInvoice   = Booking::where('booking_status', 'issued')->count();
        $invoicedToday    = Booking::where('booking_status', 'invoiced')->whereDate('invoiced_at', today())->count();
        $invoicedMonth    = Booking::where('booking_status', 'invoiced')->whereBetween('invoiced_at', [$som, $eom])->count();
        $outstandingTotal = BookingPayment::where('balance_remaining', '>', 0)->sum('balance_remaining');

        $issueQueueBookings = Booking::where('booking_status', 'ticket_in_process')
            ->orderByDesc('updated_at')->take(15)->with(['user', 'payment'])->get();

        $invoiceQueueBookings = Booking::where('booking_status', 'issued')
            ->orderByDesc('updated_at')->take(15)->with(['user', 'payment'])->get();

        return view('content.dashboard.accounts-dashboard', compact(
            'ticketsInProcess', 'readyToInvoice', 'invoicedToday', 'invoicedMonth',
            'outstandingTotal', 'issueQueueBookings', 'invoiceQueueBookings'
        ));
    }

    protected function issuanceDashboard()
    {
        $inQueue   = Booking::where('booking_status', 'issuance_queue')->count();
        $doneToday = Booking::where('booking_status', 'ticket_in_process')
            ->whereDate('ticket_processed_at', today())->count();

        $queueBookings = Booking::where('booking_status', 'issuance_queue')
            ->orderBy('issuance_queued_at')->take(20)->with(['user','flightDetail','passengers'])->get();

        return view('content.dashboard.issuance-dashboard', compact(
            'inQueue', 'doneToday', 'queueBookings'
        ));
    }

    protected function agentDashboard()
    {
        $userId = Auth::id();
        $now    = now();
        $som    = $now->copy()->startOfMonth();
        $eom    = $now->copy()->endOfMonth();

        // ── This month counts ──
        $myTotalBookings = Booking::where('user_id', $userId)->whereBetween('created_at', [$som, $eom])->count();
        $myTodayBookings = Booking::where('user_id', $userId)->whereDate('created_at', today())->count();

        // 'invoiced' is included because invoicing only happens after a booking is
        // issued and fully paid — it must not be miscounted as "Fresh".
        $issuedStatuses = ['issued', 'issued_payment_awaiting', 'issued_payment_plan', 'invoiced'];

        // Net margin = gross margin (sale - cost) minus CC charges — always used for these figures.
        $netMargin = fn (Booking $b) => $b->netMargin();

        // ── FRESH: net margin from bookings NOT yet issued, created this month ──
        $freshBookings = Booking::where('user_id', $userId)
            ->whereNotIn('booking_status', $issuedStatuses)
            ->whereBetween('created_at', [$som, $eom])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $myFresh = (float) $freshBookings->sum($netMargin);
        $myFreshCount = $freshBookings->count();

        // ── ISSUED: net margin from issued bookings that are FULLY PAID, created this month ──
        $issuedBookings = Booking::where('user_id', $userId)
            ->whereIn('booking_status', $issuedStatuses)
            ->whereBetween('created_at', [$som, $eom])
            ->whereHas('payment', fn ($q) => $q->where('balance_remaining', '<=', 0))
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $myIssued = (float) $issuedBookings->sum($netMargin);
        $myIssuedCount = $issuedBookings->count();

        // ── PENDING (THIS MONTH): same status filter as Fresh (not yet issued at all,
        //    no balance check) — it's Fresh's number restated as "Pending" for this
        //    card. Resets to 0 each new month. ──
        $myPending = $myFresh;
        $myPendingCount = $myFreshCount;

        // ── PENDING (ALL TIME): the same "not yet issued at all" rule as Fresh/Pending,
        //    just without the month restriction — never resets. Bookings that are
        //    already issued (payment plan/awaiting/etc.) are tracked separately in the
        //    tabs below, not rolled into this figure. ──
        $allTimeNotYetIssuedBookings = Booking::where('user_id', $userId)
            ->whereNotIn('booking_status', $issuedStatuses)
            ->with(['user', 'flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
            ->orderByDesc('created_at')
            ->get();
        $myPendingAllTime = (float) $allTimeNotYetIssuedBookings->sum($netMargin);
        $myPendingAllTimeCount = $allTimeNotYetIssuedBookings->count();

        // Note: issued-but-unpaid bookings (payment plan / payment awaiting) have their
        // own dedicated report pages now — see DashboardController::issuedPaymentReport().

        // ── Current month calendar ──
        $calendarDays = Booking::where('user_id', $userId)
            ->whereBetween('created_at', [$som, $eom])
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // ── Current month calendar data ──
        $startDow = (int) $som->dayOfWeek;
        $startDow = $startDow === 0 ? 6 : $startDow - 1;
        $currentKey = $now->format('Y-m');
        $allMonthData = [
            $currentKey => [
                'label'       => $now->format('F Y'),
                'daysInMonth' => $now->daysInMonth,
                'startDow'    => $startDow,
                'days'        => $calendarDays,
                'total'       => array_sum($calendarDays),
                'year'        => $now->year,
                'month'       => $now->month,
                'isCurrent'   => true,
            ],
        ];

        // ── Pending Bookings box: all-time, not-yet-issued bookings, most
        //    urgent payment date first. Not capped — the table's Totals row has
        //    to cover every pending booking, and a silent take() made it
        //    disagree with the Pending KPI above it. ──
        $pendingTabBookings = $this->sortByNextDueDate($allTimeNotYetIssuedBookings)->values();

        // ── All agents with today's booking count ──
        $allAgents = \App\Models\User::where('role', 'agent')
            ->withCount(['bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('name')
            ->get();

        return view('content.dashboard.agent-dashboard', compact(
            'myTotalBookings', 'myTodayBookings',
            'myFresh', 'myIssued', 'myPending', 'myPendingAllTime',
            'myFreshCount', 'myIssuedCount', 'myPendingCount', 'myPendingAllTimeCount',
            'calendarDays', 'allMonthData',
            'pendingTabBookings',
            'allAgents'
        ));
    }
}
