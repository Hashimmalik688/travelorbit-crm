<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return view('content.bookings.index');
    }

    public function create()
    {
        return view('content.bookings.create');
    }

    public function store()
    {
        return redirect()->route('bookings.create');
    }

    public function show(Booking $booking)
    {
        return view('content.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('content.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        return redirect()->route('bookings.edit', $booking);
    }
}
