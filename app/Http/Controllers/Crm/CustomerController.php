<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Booking::select(
                'bookings.booker_mobile',
                DB::raw("MAX(CONCAT(bookings.booker_first_name, ' ', bookings.booker_last_name)) as booker_name"),
                DB::raw('MAX(bookings.booker_whatsapp) as booker_whatsapp'),
                DB::raw('MAX(bookings.booker_email) as booker_email'),
                DB::raw('COUNT(DISTINCT bookings.id) as total_bookings'),
                DB::raw('COALESCE(SUM(booking_flight_details.selling_price), 0) as total_spent'),
                DB::raw('MAX(bookings.created_at) as last_booking_date')
            )
            ->leftJoin('booking_flight_details', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->groupBy('bookings.booker_mobile')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where(DB::raw("CONCAT(booker_first_name, ' ', booker_last_name)"), 'ILIKE', "%{$search}%")
                      ->orWhere('booker_mobile', 'ILIKE', "%{$search}%");
                });
            })
            ->orderByDesc('total_bookings')
            ->get();

        return view('content.crm.customers', compact('customers', 'search'));
    }

    public function show(string $phone)
    {
        $customer = Booking::select(
                'bookings.booker_mobile',
                DB::raw("MAX(CONCAT(bookings.booker_first_name, ' ', bookings.booker_last_name)) as booker_name"),
                DB::raw('MAX(bookings.booker_whatsapp) as booker_whatsapp'),
                DB::raw('MAX(bookings.booker_email) as booker_email'),
                DB::raw('COUNT(DISTINCT bookings.id) as total_bookings'),
                DB::raw('COALESCE(SUM(booking_flight_details.selling_price), 0) as total_spent'),
            )
            ->leftJoin('booking_flight_details', 'bookings.id', '=', 'booking_flight_details.booking_id')
            ->where('bookings.booker_mobile', $phone)
            ->groupBy('bookings.booker_mobile')
            ->firstOrFail();

        $bookings = Booking::where('booker_mobile', $phone)
            ->orderByDesc('created_at')
            ->get();

        return view('content.crm.customers-show', compact('customer', 'bookings'));
    }
}
