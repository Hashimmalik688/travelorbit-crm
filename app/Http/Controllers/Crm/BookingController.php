<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        return view('content.bookings.index');
    }

    public function myBookings()
    {
        // Agent sees only their own bookings
        return view('content.bookings.index', ['filterUserId' => Auth::id(), 'myBookingsOnly' => true]);
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

    public function destroy(Booking $booking)
    {
        // Gated by the bookings.delete permission (admin + manager by default)
        if (!Auth::user()->hasPermission('bookings.delete')) {
            abort(403, 'You do not have permission to delete bookings.');
        }

        $num = $booking->booking_number;
        $booking->delete(); // soft delete

        AuditLog::logAction(
            action:      'booking_deleted',
            user:        Auth::user(),
            model:       'Booking',
            model_id:    $booking->id,
            description: "Booking #{$num} soft-deleted",
        );

        return back()->with('success', "Booking #{$num} has been deleted.");
    }
}
