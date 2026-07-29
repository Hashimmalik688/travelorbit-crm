<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function agentDashboardPage()
    {
        return $this->agentDashboard();
    }

    public function accountsDashboardPage() { return $this->accountsDashboard(); }

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
        $data = $this->buildIssuedPaymentReportData($status, Auth::id());

        return view('content.dashboard.issued-unpaid-report', array_merge($data, compact('title', 'subtitle')));
    }

    /**
     * Admin/manager version of the Payment Plan / Payment Awaiting reports —
     * not scoped to the current user, with report-type/date/agent filters on
     * top of the existing booking-type filter.
     */
    public function paymentStatusReport()
    {
        $status = request('status', 'issued_payment_awaiting');
        if (!in_array($status, ['issued_payment_awaiting', 'issued_payment_plan'])) {
            $status = 'issued_payment_awaiting';
        }

        $titles = [
            'issued_payment_awaiting' => ['Payment Awaiting', 'Issued bookings awaiting their outstanding balance.'],
            'issued_payment_plan'     => ['Payment Plan', 'Issued bookings still being paid off in instalments.'],
        ];
        [$title, $subtitle] = $titles[$status];

        $userId   = request('user_id') ? (int) request('user_id') : null;
        $dateFrom = request('from') ?: null;
        $dateTo   = request('to') ?: null;

        $data = $this->buildIssuedPaymentReportData($status, $userId, $dateFrom, $dateTo);

        // Only agents who actually have bookings show up in the filter — an
        // empty dropdown of every user would just add noise.
        $agents = \App\Models\User::whereHas('bookings')->orderBy('name')->get(['id', 'name']);

        return view('content.dashboard.payment-status-report', array_merge($data, compact(
            'title', 'subtitle', 'status', 'userId', 'dateFrom', 'dateTo', 'agents'
        )));
    }

    /**
     * Departure/Arrival report — one row per flight segment (PNR), split into
     * a Departure row and (for return trips) a separate Return row, so the
     * report can answer "who's moving on date X" directly. There's no
     * passenger-to-segment link anywhere in the schema (a booking's
     * passengers and its segments are two independent lists), so passengers
     * are shown as a count per row rather than named individuals — naming
     * them would imply a per-person-per-leg truth the data doesn't have.
     */
    public function departureArrivalReport()
    {
        $type = request('type');

        // Default view (no date filter touched yet) is a genuine urgency
        // window: 5 days of recent past for context, 7 days of upcoming —
        // not open-ended, otherwise legs months out clutter what's supposed
        // to be an "act on this now" list. from/to default independently —
        // a URL/link that only sets one (e.g. an old bookmark with just
        // ?from=...) must not silently drop the default on the other side.
        $dateFrom = request()->has('from') ? (request('from') ?: null) : now()->subDays(5)->toDateString();
        $dateTo   = request()->has('to')   ? (request('to') ?: null)   : now()->addDays(7)->toDateString();

        // Managers/accounts (data.view_all) see every booking's legs; an agent
        // reaching this via reports.departure_arrival is scoped to their own
        // bookings only, matching the app-wide own-vs-all data convention.
        $user = auth()->user();
        $segments = \App\Models\BookingFlightDetail::with(['booking.user', 'booking.passengers'])
            ->when(! $user->canViewAllData(), fn ($q) => $q->whereHas('booking', fn ($b) => $b->where('user_id', $user->id)))
            ->orderBy('booking_id')
            ->orderBy('id')
            ->get()
            ->filter(fn ($seg) => $seg->booking !== null)
            ->groupBy('booking_id');

        $allRows = collect();
        foreach ($segments as $bookingSegments) {
            $booking = $bookingSegments->first()->booking;
            $passengerLabels = $this->pnrPassengerLabels($booking);

            foreach ($bookingSegments->values() as $si => $seg) {
                $dep = $seg->departure_airport ? strtoupper($seg->departure_airport) : '?';
                $arr = $seg->arrival_airport ? strtoupper($seg->arrival_airport) : '?';
                $passenger = $passengerLabels[$si] ?? ['tag' => 'PNR ' . ($si + 1), 'name' => null];

                $allRows->push([
                    'booking'       => $booking,
                    'date'          => $seg->departure_date,
                    'leg'           => 'Departure',
                    'route'         => "{$dep} - {$arr}",
                    'passenger_tag' => $passenger['tag'],
                    'passenger_name'=> $passenger['name'],
                    'airline'       => $seg->airline,
                    'urgency'       => $this->legUrgency($seg->departure_date),
                ]);

                if (($seg->flight_type ?? 'return') !== 'one_way' && $seg->return_date) {
                    $allRows->push([
                        'booking'       => $booking,
                        'date'          => $seg->return_date,
                        'leg'           => 'Return',
                        'route'         => "{$arr} - {$dep}",
                        'passenger_tag' => $passenger['tag'],
                        'passenger_name'=> $passenger['name'],
                        'airline'       => $seg->airline,
                        'urgency'       => $this->legUrgency($seg->return_date),
                    ]);
                }
            }
        }

        // Type counts always reflect the full unfiltered list (ignoring $type
        // itself) so switching the filter doesn't hide how many rows exist
        // in every other type.
        $typeCounts = $allRows->countBy(fn ($r) => $r['booking']->booking_type);

        $rows = $type ? $allRows->filter(fn ($r) => $r['booking']->booking_type === $type) : $allRows;

        if ($dateFrom) {
            $rows = $rows->filter(fn ($r) => $r['date'] && $r['date']->gte(\Carbon\Carbon::parse($dateFrom)));
        }
        if ($dateTo) {
            $rows = $rows->filter(fn ($r) => $r['date'] && $r['date']->lte(\Carbon\Carbon::parse($dateTo)));
        }

        // Urgent upcoming first (soonest next), then recent past below (most
        // recent first) as trailing context — a plain ascending sort would
        // bury today's/tomorrow's departures under anything from the last
        // few days.
        $today = now()->startOfDay();
        $upcoming = $rows->filter(fn ($r) => $r['date'] && $r['date']->gte($today))->sortBy('date');
        $past = $rows->filter(fn ($r) => !$r['date'] || $r['date']->lt($today))->sortByDesc('date');
        $sorted = $upcoming->concat($past)->values();

        $perPage = 20;
        $page = (int) request('page', 1);
        $rowsPage = new \Illuminate\Pagination\LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('content.dashboard.departure-arrival-report', compact(
            'rowsPage', 'typeCounts', 'type', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Mirrors BookingShow::getPnrLabel() — the booking page itself labels
     * each PNR block by position ("Adult x 1", "Adult x 2", ...), the Nth
     * passenger (adult, then youth/gbe, then child, then infant) matching
     * the Nth flight segment in creation order. There's no FK behind this —
     * it's a positional convention — but it's the one the app already uses,
     * so the report follows it rather than inventing a different scheme.
     */
    private function pnrPassengerLabels(Booking $booking): array
    {
        $typeLabels = ['adult' => 'Adult', 'gbe' => 'Youth', 'child' => 'Child', 'infant' => 'Infant'];
        $grouped = $booking->passengers->sortBy('id')->groupBy('passenger_type');

        $labels = [];
        foreach ($typeLabels as $type => $label) {
            foreach ($grouped->get($type, collect())->values() as $i => $passenger) {
                $name = trim($passenger->full_name ?: trim(($passenger->first_name ?? '') . ' ' . ($passenger->last_name ?? '')));
                $labels[] = ['tag' => "{$label} x " . ($i + 1), 'name' => $name !== '' ? $name : null];
            }
        }

        return $labels;
    }

    /**
     * How urgent a leg is relative to today — same spirit as the due-date
     * badge on the Payment Plan report ("3 days to go" / "2 days ago"), but
     * with finer tiers (today/tomorrow/this week) since flight urgency needs
     * sharper granularity than a payment due date does.
     */
    private function legUrgency(?\Carbon\Carbon $date): array
    {
        if (!$date) {
            return ['label' => '—', 'tier' => 'none'];
        }

        $days = (int) floor(($date->copy()->startOfDay()->timestamp - now()->startOfDay()->timestamp) / 86400);

        if ($days < 0) {
            return ['label' => abs($days) . 'd ago', 'tier' => 'past'];
        }
        if ($days === 0) {
            return ['label' => 'Today', 'tier' => 'today'];
        }
        if ($days === 1) {
            return ['label' => 'Tomorrow', 'tier' => 'tomorrow'];
        }
        if ($days <= 3) {
            return ['label' => "In {$days}d", 'tier' => 'soon'];
        }
        if ($days <= 7) {
            return ['label' => "In {$days}d", 'tier' => 'week'];
        }

        return ['label' => "In {$days}d", 'tier' => 'later'];
    }

    protected function buildIssuedPaymentReportData(string $status, ?int $userId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $type = request('type');

        $baseQuery = fn () => Booking::where('booking_status', $status)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->whereHas('payment');

        // Booking-type counts always reflect the FULL list (ignoring $type
        // itself) so switching the type filter doesn't hide how many bookings
        // exist in every other type.
        $typeCounts = $baseQuery()->get()->countBy('booking_type');

        // No balance filter — a booking stays in its payment-plan/awaiting status
        // even once fully paid, so settled bookings stay visible here (shown
        // with a "Settled" highlight) rather than silently disappearing.
        $all = $baseQuery()
            ->when($type, fn ($q) => $q->where('booking_type', $type))
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

        return compact('bookings', 'totals', 'type', 'typeCounts');
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

    protected function accountsDashboard()
    {
        $issueQueueBookings = Booking::where('booking_status', 'ticket_in_process')
            ->orderByDesc('updated_at')->take(15)->with(['user', 'payment'])->get();
        // Ready to Invoice = issued & fully paid. Includes bookings issued on
        // Payment Awaiting / Payment Plan once their balance is settled, so
        // "fully paid" is evaluated in PHP via Booking::canInvoice().
        // Already-invoiced bookings sit on plain 'issued' too, so they're
        // excluded by invoiced_at — otherwise they'd never leave this queue.
        $invoiceQueueBookings = Booking::whereIn('booking_status', [
                Booking::STATUS_ISSUED,
                Booking::STATUS_ISSUED_PAYMENT_AWAITING,
                Booking::STATUS_ISSUED_PAYMENT_PLAN,
            ])
            ->whereNull('invoiced_at')
            ->orderByDesc('updated_at')
            ->with(['user', 'payment', 'paymentHistory'])
            ->get()
            ->filter(fn (Booking $b) => $b->canInvoice())
            ->take(15)
            ->values();

        return view('content.dashboard.accounts-dashboard', compact(
            'issueQueueBookings', 'invoiceQueueBookings'
        ));
    }

    protected function adminDashboard()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Same Fresh/Issued/Pending split as the agent dashboard, just
        // company-wide instead of scoped to one agent — Fresh is the whole
        // month's margin, Issued and Pending are the two slices of it.
        $deadStatuses = ['cancelled', 'refund_queue'];
        // "Has been issued at all" — drives the Pending slice, which is labelled
        // "not yet issued" and so must exclude every issued disposition.
        $issuedStatuses = ['issued', 'issued_payment_awaiting', 'issued_payment_plan', 'invoiced'];
        // Margin actually banked. Only plain Issued and Invoiced count: a
        // booking on a Payment Plan / Payment Awaiting is still owed money and
        // earns no margin until it's invoiced, at which point invoicing moves it
        // to plain Issued. 'invoiced' stays in the list for bookings invoiced
        // before that rule (they kept the old status).
        $marginStatuses = ['issued', 'invoiced'];
        $netMargin = fn (Booking $b) => $b->netMargin();

        // ── FRESH: all margin generated this month, issued or still pending,
        //    excluding only cancelled/refunded bookings. ──
        $freshBookings = Booking::whereNotIn('booking_status', $deadStatuses)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $freshMarginThisMonth = (float) $freshBookings->sum($netMargin);
        $freshCountThisMonth  = $freshBookings->count();

        // ── ISSUED: net margin from issued & invoiced bookings that are FULLY
        //    PAID, created this month. ──
        $issuedBookingsThisMonth = Booking::whereIn('booking_status', $marginStatuses)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
            ->get()
            ->filter(fn (Booking $b) => $b->total_sale_price - $b->totalReceived() <= 0.005);
        $issuedMarginThisMonth = (float) $issuedBookingsThisMonth->sum($netMargin);
        $issuedCountThisMonth  = $issuedBookingsThisMonth->count();

        // ── PENDING: the not-yet-issued slice of Fresh, this month. ──
        $pendingBookingsThisMonth = Booking::whereNotIn('booking_status', array_merge($issuedStatuses, $deadStatuses))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $pendingMarginThisMonth = (float) $pendingBookingsThisMonth->sum($netMargin);
        $pendingCountThisMonth  = $pendingBookingsThisMonth->count();

        // Outstanding balance is scoped to bookings that have actually been
        // issued and are still being paid off — Payment Awaiting / Payment
        // Plan — not bookings that simply haven't been issued yet.
        //
        // The remaining amount itself must come from the approved-payments
        // ledger (the same source as BookingShow's "Balance Due"), NOT
        // booking_payments.balance_remaining — that's a stale creation-time
        // snapshot never updated as payments are recorded, so it can read 0
        // on a booking that's still genuinely owed money.
        $outstandingBookings = Booking::whereIn('booking_status', [
                Booking::STATUS_ISSUED_PAYMENT_AWAITING,
                Booking::STATUS_ISSUED_PAYMENT_PLAN,
            ])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
            ->get();

        $outstandingPayments = (float) $outstandingBookings->sum(
            fn (Booking $b) => max(0, $b->total_sale_price - $b->totalReceived())
        );
        $outstandingCount = $outstandingBookings->count();

        // Agent Leaderboard / Chill Squad now lives in the SellingBoard Livewire
        // component (polls on its own) — see resources/views/livewire/selling-board.blade.php.

        $perf = $this->agentsPerformanceData(true);
        $agentsPerformance = $perf['agentsPerformance'];
        $performanceLabel  = $perf['performanceLabel'];

        // Recent Bookings: the 10 most recent bookings company-wide, not a
        // date window — same eager-loads as Fresh so netMargin() doesn't
        // trigger N+1 queries.
        $recentBookings = Booking::with(['user', 'flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('content.dashboard.dashboard', compact(
            'freshMarginThisMonth', 'freshCountThisMonth',
            'issuedMarginThisMonth', 'issuedCountThisMonth',
            'pendingMarginThisMonth', 'pendingCountThisMonth',
            'outstandingPayments', 'outstandingCount',
            'agentsPerformance', 'performanceLabel', 'recentBookings'
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

    /**
     * Agents Performance widget data — every agent-role user with their photo,
     * today-activity flag, this-month booking count and commission-window
     * margin. Shared by the admin Operations Centre and the agent dashboard.
     *
     * Fresh margin for the first 19 days of the month; from the 20th it
     * switches to Issued margin (the commission-cycle cutoff). Always scoped
     * to "this month", so it resets naturally on the 1st.
     *
     * $showMargin also drives the SORT, not just the display: agents must not
     * see each other's margins, and ordering the tiles by margin would leak
     * the same ranking the hidden number carries. When margin is hidden the
     * wall is ordered by booking count instead.
     */
    protected function agentsPerformanceData(bool $showMargin): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();
        $deadStatuses   = ['cancelled', 'refund_queue'];
        // Only banked margin counts here — plain Issued and Invoiced. Payment
        // Plan / Payment Awaiting are still owed money and stay out until
        // invoicing settles them onto plain Issued.
        $marginStatuses = ['issued', 'invoiced'];
        $cutoffPassed   = now()->day >= 20;

        $agentsPerformance = \App\Models\User::where('role', 'agent')
            ->withCount(['bookings as today_bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('name')->get()
            ->map(function ($agent) use ($startOfMonth, $endOfMonth, $deadStatuses, $marginStatuses, $cutoffPassed) {
                $bookingsThisMonth = Booking::where('user_id', $agent->id)
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
                    ->get();

                // The commission window flips on the 20th. Both the count and
                // the margin come from the SAME set so they always agree:
                //   days 1-19  → every live booking this month (Fresh)
                //   day 20 on  → only issued/invoiced, fully-paid bookings (Issued)
                // On the 1st the month window is empty, so everything is 0
                // until new bookings land — the monthly reset is automatic.
                if ($cutoffPassed) {
                    $relevant = $bookingsThisMonth->whereIn('booking_status', $marginStatuses)
                        ->filter(fn (Booking $b) => $b->total_sale_price - $b->totalReceived() <= 0.005);
                } else {
                    $relevant = $bookingsThisMonth->whereNotIn('booking_status', $deadStatuses);
                }

                return (object) [
                    'name'               => $agent->name,
                    'profile_photo_path' => $agent->profile_photo_path,
                    'made_booking_today' => $agent->today_bookings > 0,
                    'margin'             => (float) $relevant->sum(fn (Booking $b) => $b->netMargin()),
                    'count'              => $relevant->count(),
                ];
            })
            // It's a competition on volume: most bookings this month comes top,
            // regardless of margin. Ranking never uses margin, which also means
            // the order can't leak margin to agents who aren't shown it.
            // Equal counts are broken by name so the order is stable rather
            // than shuffling between requests.
            ->sort(fn ($a, $b) => ($b->count <=> $a->count) ?: strcasecmp($a->name, $b->name))
            ->values();

        return [
            'agentsPerformance' => $agentsPerformance,
            'performanceLabel'  => $cutoffPassed ? 'Issued Margin' : 'Fresh Margin',
        ];
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

        // "Has been issued at all" — used only to exclude issued bookings from
        // the two Pending figures, both of which are labelled "not yet issued".
        $issuedStatuses = ['issued', 'issued_payment_awaiting', 'issued_payment_plan', 'invoiced'];

        // Margin actually banked, and the only thing the Issued KPI counts:
        // plain Issued (full payment) and Invoiced. A booking on a Payment Plan
        // / Payment Awaiting is still owed money, so it earns the agent no
        // margin in this or any month until it's invoiced — invoicing settles it
        // onto plain Issued. 'invoiced' remains for bookings invoiced before
        // that rule, which kept the old status.
        $marginStatuses = ['issued', 'invoiced'];

        // Net margin = gross margin (sale - cost) minus CC charges — always used for these figures.
        $netMargin = fn (Booking $b) => $b->netMargin();

        // Statuses that represent a lost booking — never counted as margin.
        $deadStatuses = ['cancelled', 'refund_queue'];

        // ── FRESH: ALL margin generated this month — issued or still pending —
        //    excluding only cancelled/refunded bookings (no margin earned). This
        //    is the total this-month figure; Issued and Pending below are the two
        //    slices of it. ──
        $freshBookings = Booking::where('user_id', $userId)
            ->whereNotIn('booking_status', $deadStatuses)
            ->whereBetween('created_at', [$som, $eom])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $myFresh = (float) $freshBookings->sum($netMargin);
        $myFreshCount = $freshBookings->count();

        // ── ISSUED: net margin from issued & invoiced bookings that are FULLY
        //    PAID, created this month ──
        // "Fully paid" is checked against the live approved-payments ledger
        // (Booking::totalReceived()), not booking_payments.balance_remaining —
        // that column is a stale creation-time snapshot and doesn't reflect
        // instalments/charges approved afterwards.
        $issuedBookings = Booking::where('user_id', $userId)
            ->whereIn('booking_status', $marginStatuses)
            ->whereBetween('created_at', [$som, $eom])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment', 'paymentHistory'])
            ->get()
            ->filter(fn (Booking $b) => $b->total_sale_price - $b->totalReceived() <= 0.005);
        $myIssued = (float) $issuedBookings->sum($netMargin);
        $myIssuedCount = $issuedBookings->count();

        // ── PENDING (THIS MONTH): the not-yet-issued slice of Fresh — bookings
        //    created this month that aren't issued yet (and aren't cancelled). Once
        //    a booking is issued it leaves this figure and shows under Issued. ──
        $pendingBookings = Booking::where('user_id', $userId)
            ->whereNotIn('booking_status', array_merge($issuedStatuses, $deadStatuses))
            ->whereBetween('created_at', [$som, $eom])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $myPending = (float) $pendingBookings->sum($netMargin);
        $myPendingCount = $pendingBookings->count();

        // ── PENDING (ALL TIME): the same "not yet issued at all" rule as Fresh/Pending,
        //    just without the month restriction — never resets. Bookings that are
        //    already issued (payment plan/awaiting/etc.) are tracked separately in the
        //    tabs below, not rolled into this figure. ──
        $allTimeNotYetIssuedBookings = Booking::where('user_id', $userId)
            ->whereNotIn('booking_status', array_merge($issuedStatuses, $deadStatuses))
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
        //    disagree with the Pending KPI above it.
        //
        //    Type counts are always computed from the FULL unfiltered set (so
        //    the breakdown chips don't collapse to one number once a type is
        //    picked); the KPI card above stays unfiltered too — only the table
        //    itself narrows down when a type is selected. ──
        $pendingTypeCounts = $allTimeNotYetIssuedBookings->countBy('booking_type');
        $pendingTypeFilter = request('type');
        $pendingBookingsForTab = $pendingTypeFilter
            ? $allTimeNotYetIssuedBookings->where('booking_type', $pendingTypeFilter)->values()
            : $allTimeNotYetIssuedBookings;
        $pendingTabBookings = $this->sortByNextDueDate($pendingBookingsForTab)->values();

        // ── All agents with today's booking count ──
        $allAgents = \App\Models\User::where('role', 'agent')
            ->withCount(['bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('name')
            ->get();

        // Agents Performance on this dashboard: an agent may see their own
        // margin in the KPIs above, but not their colleagues'. Gate on
        // data.view_all (the "see everyone's data" permission) so a manager
        // landing here still gets the full picture while an agent sees only
        // names, photos and booking counts. The flag drives the sort too —
        // see agentsPerformanceData().
        $showPerformanceMargin = Auth::user()->canViewAllData();
        $perf = $this->agentsPerformanceData($showPerformanceMargin);
        $agentsPerformance = $perf['agentsPerformance'];
        $performanceLabel  = $perf['performanceLabel'];

        return view('content.dashboard.agent-dashboard', compact(
            'myTotalBookings', 'myTodayBookings',
            'myFresh', 'myIssued', 'myPending', 'myPendingAllTime',
            'myFreshCount', 'myIssuedCount', 'myPendingCount', 'myPendingAllTimeCount',
            'calendarDays', 'allMonthData',
            'pendingTabBookings', 'pendingTypeCounts', 'pendingTypeFilter',
            'allAgents', 'agentsPerformance', 'performanceLabel', 'showPerformanceMargin'
        ));
    }
}
