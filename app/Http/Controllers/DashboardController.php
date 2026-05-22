<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user && $user->role === 'agent') {
            return $this->agentDashboard();
        }

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $totalBookings = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        $totalRevenue = DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->whereBetween('bookings.created_at', [$startOfMonth, $endOfMonth])
            ->sum('booking_flight_details.selling_price');

        $outstandingPayments = BookingPayment::where('balance_remaining', '>', 0)->sum('balance_remaining');

        $overduePaymentsCount = BookingPayment::where('balance_remaining', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $bookingsByStatus = Booking::select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->pluck('total', 'booking_status')
            ->toArray();

        $pendingCount = $bookingsByStatus['pending'] ?? 0;
        $confirmedCount = $bookingsByStatus['confirmed'] ?? 0;
        $issuedCount = $bookingsByStatus['issued'] ?? 0;

        $topAgent = Booking::select('user_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->first();

        $last7DaysBookings = [];
        $last7DaysRevenue  = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $last7DaysBookings[] = Booking::whereDate('created_at', $date)->count();
            $last7DaysRevenue[]  = (int) DB::table('booking_flight_details')
                ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
                ->whereDate('bookings.created_at', $date)
                ->sum('booking_flight_details.selling_price');
        }

        return view('content.dashboard.dashboard', compact(
            'totalBookings', 'totalRevenue', 'outstandingPayments',
            'overduePaymentsCount', 'pendingCount', 'confirmedCount',
            'issuedCount', 'topAgent', 'last7DaysBookings', 'last7DaysRevenue'
        ));
    }

    private function agentDashboard()
    {
        $userId = Auth::id();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $myTotalBookings = Booking::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $myRevenue = DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->where('bookings.user_id', $userId)
            ->whereBetween('bookings.created_at', [$startOfMonth, $endOfMonth])
            ->sum('booking_flight_details.selling_price');

        $myOutstanding = BookingPayment::whereHas('booking', fn ($q) => $q->where('user_id', $userId))
            ->where('balance_remaining', '>', 0)
            ->sum('balance_remaining');

        $myPendingIssuance = Booking::where('user_id', $userId)
            ->whereNull('issuance_requested_at')
            ->where('booking_status', 'confirmed')
            ->count();

        $myStatusCounts = Booking::where('user_id', $userId)
            ->select('booking_status', DB::raw('count(*) as total'))
            ->groupBy('booking_status')
            ->pluck('total', 'booking_status')
            ->toArray();

        $myPendingCount = $myStatusCounts['pending'] ?? 0;
        $myConfirmedCount = $myStatusCounts['confirmed'] ?? 0;
        $myCancelledCount = $myStatusCounts['cancelled'] ?? 0;

        $myTotalRevenue = DB::table('booking_flight_details')
            ->join('bookings', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->where('bookings.user_id', $userId)
            ->sum('booking_flight_details.selling_price');

        $myAvgRevenue = $myTotalBookings > 0 ? round($myTotalRevenue / $myTotalBookings) : 0;

        $myRecentBookings = Booking::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(8)
            ->with('flightDetail')
            ->get();

        $myRecentCount = Booking::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $monthlyTarget = 20;

        return view('content.dashboard.agent-dashboard', compact(
            'myTotalBookings', 'myRevenue', 'myOutstanding', 'myPendingIssuance',
            'myPendingCount', 'myConfirmedCount', 'myCancelledCount',
            'myAvgRevenue', 'myRecentBookings', 'myRecentCount',
            'monthlyTarget'
        ));
    }
}
