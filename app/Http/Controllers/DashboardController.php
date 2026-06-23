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

        $readyToInvoice  = Booking::where('booking_status', 'ticket_in_process')->count();
        $invoicedToday   = Booking::where('booking_status', 'invoiced')->whereDate('invoiced_at', today())->count();
        $invoicedMonth   = Booking::where('booking_status', 'invoiced')->whereBetween('invoiced_at', [$som, $eom])->count();
        $outstandingTotal = BookingPayment::where('balance_remaining', '>', 0)->sum('balance_remaining');

        $recentBookings = Booking::whereIn('booking_status', ['ticket_in_process','invoiced','pending','confirmed'])
            ->orderByDesc('updated_at')->take(15)->with(['user','payment'])->get();

        return view('content.dashboard.accounts-dashboard', compact(
            'readyToInvoice', 'invoicedToday', 'invoicedMonth',
            'outstandingTotal', 'recentBookings'
        ));
    }

    protected function issuanceDashboard()
    {
        $inQueue    = Booking::where('booking_status', 'issuance_queue')->count();
        $inProcess  = Booking::where('booking_status', 'ticket_in_process')->count();
        $doneToday  = Booking::where('booking_status', 'ticket_in_process')->whereDate('ticket_processed_at', today())->count();

        $queueBookings = Booking::whereIn('booking_status', ['issuance_queue','ticket_in_process'])
            ->orderBy('issuance_queued_at')->take(20)->with(['user','flightDetail','passengers'])->get();

        return view('content.dashboard.issuance-dashboard', compact(
            'inQueue', 'inProcess', 'doneToday', 'queueBookings'
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

        // ── FRESH: total revenue (sold) from all bookings ever ──
        $myFresh = (float) DB::table('booking_passengers')
            ->join('bookings', 'bookings.id', '=', 'booking_passengers.booking_id')
            ->where('bookings.user_id', $userId)
            ->whereNull('bookings.deleted_at')
            ->sum('booking_passengers.sold_per_pax');

        // ── ISSUED: total amount actually received (payment history) ──
        $myIssued = (float) DB::table('booking_payment_history')
            ->join('bookings', 'bookings.id', '=', 'booking_payment_history.booking_id')
            ->where('bookings.user_id', $userId)
            ->whereNull('bookings.deleted_at')
            ->sum('booking_payment_history.amount');

        // ── PENDING: total outstanding balance ──
        $myPending = (float) \App\Models\BookingPayment::whereHas('booking', fn ($q) => $q->where('user_id', $userId))
            ->where('balance_remaining', '>', 0)
            ->sum('balance_remaining');

        // ── Current month calendar ──
        $calendarDays = Booking::where('user_id', $userId)
            ->whereBetween('created_at', [$som, $eom])
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->toArray();

        // ── Last 12 months booking days - for the navigable calendar ──
        $allMonthData = [];
        for ($i = 11; $i >= 0; $i--) {
            $m    = $now->copy()->subMonths($i);
            $mSom = $m->copy()->startOfMonth();
            $mEom = $m->copy()->endOfMonth();
            $days = Booking::where('user_id', $userId)
                ->whereBetween('created_at', [$mSom, $mEom])
                ->selectRaw('DATE(created_at) as day, count(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day')
                ->toArray();
            $startDow = (int) $mSom->dayOfWeek;
            $startDow = $startDow === 0 ? 6 : $startDow - 1;
            $key = $m->format('Y-m');
            $allMonthData[$key] = [
                'label'       => $m->format('F Y'),
                'shortLabel'  => $m->format('M Y'),
                'daysInMonth' => $m->daysInMonth,
                'startDow'    => $startDow,
                'days'        => $days,
                'total'       => array_sum($days),
                'year'        => $m->year,
                'month'       => $m->month,
                'key'         => $key,
                'isCurrent'   => $m->isSameMonth($now),
            ];
        }

        // ── Recent bookings ──
        $myRecentBookings = Booking::where('user_id', $userId)
            ->orderByDesc('created_at')->take(10)
            ->with(['flightDetail', 'payment'])
            ->get();

        // ── All agents with today's booking count ──
        $allAgents = \App\Models\User::where('role', 'agent')
            ->withCount(['bookings' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('name')
            ->get();

        return view('content.dashboard.agent-dashboard', compact(
            'myTotalBookings', 'myTodayBookings',
            'myFresh', 'myIssued', 'myPending',
            'calendarDays', 'allMonthData',
            'myRecentBookings', 'allAgents'
        ));
    }
}
