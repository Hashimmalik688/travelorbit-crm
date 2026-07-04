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

        $outstandingPayments = BookingPayment::where('balance_remaining', '>', 0)->sum('balance_remaining');

        $overduePaymentsCount = BookingPayment::where('balance_remaining', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

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

        $allAgents = \App\Models\User::whereIn('role', ['agent', 'operations', 'manager', 'admin'])->withCount([
            'bookings as month_bookings' => fn($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ])->get();

        return view('content.dashboard.dashboard', compact(
            'totalBookings', 'totalRevenue', 'outstandingPayments',
            'overduePaymentsCount', 'pendingCount', 'confirmedCount',
            'issuedCount', 'issuanceQueue', 'ticketInProcess', 'invoicedCount',
            'cancelledCount', 'topAgents', 'last7DaysBookings', 'allAgents'
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
        $netMargin = fn (Booking $b) => $b->total_margin - (float) ($b->payment->cc_charges ?? 0);

        // ── FRESH: net margin from bookings NOT yet issued, created this month ──
        $freshBookings = Booking::where('user_id', $userId)
            ->whereNotIn('booking_status', $issuedStatuses)
            ->whereBetween('created_at', [$som, $eom])
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $myFresh = (float) $freshBookings->sum($netMargin);

        // ── ISSUED: net margin from issued bookings that are FULLY PAID, created this month ──
        $issuedBookings = Booking::where('user_id', $userId)
            ->whereIn('booking_status', $issuedStatuses)
            ->whereBetween('created_at', [$som, $eom])
            ->whereHas('payment', fn ($q) => $q->where('balance_remaining', '<=', 0))
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->get();
        $myIssued = (float) $issuedBookings->sum($netMargin);

        // ── PENDING (ALL TIME): net margin from issued bookings still awaiting full payment
        //    (the opposite of Issued — moves into Issued once the balance clears) ──
        $pendingBookings = Booking::where('user_id', $userId)
            ->whereIn('booking_status', $issuedStatuses)
            ->whereHas('payment', fn ($q) => $q->where('balance_remaining', '>', 0))
            ->with(['flightDetail', 'passengers', 'hotels', 'visas', 'payment'])
            ->orderByDesc('created_at')
            ->get();
        $myPendingAllTime = (float) $pendingBookings->sum($netMargin);

        // ── PENDING (THIS MONTH): same set, restricted to bookings created this month —
        //    resets to 0 each new month, unlike the all-time figure above ──
        $myPending = (float) $pendingBookings->whereBetween('created_at', [$som, $eom])->sum($netMargin);

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

        // ── Recent bookings: pending only, all time (same set as the Pending stat) ──
        $myRecentBookings = $pendingBookings->sortByDesc('created_at')->take(10)->values();

        // ── All agents with today's booking count ──
        $allAgents = \App\Models\User::where('role', 'agent')
            ->withCount(['bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('name')
            ->get();

        return view('content.dashboard.agent-dashboard', compact(
            'myTotalBookings', 'myTodayBookings',
            'myFresh', 'myIssued', 'myPending', 'myPendingAllTime',
            'calendarDays', 'allMonthData',
            'myRecentBookings', 'allAgents'
        ));
    }
}
