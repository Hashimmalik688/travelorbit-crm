<?php

namespace App\Http\Controllers;

use App\Models\Booking;

class EticketController extends Controller
{
    /**
     * Standalone e-ticket builder. Not reachable from a specific booking's page —
     * an agent picks a booking from the dropdown to pull its data in as a starting
     * point, edits freely, and prints. Nothing typed here is saved back to the booking.
     */
    public function index()
    {
        $bookings = Booking::query()
            ->select('id', 'booking_number', 'customer_id', 'booker_first_name', 'booker_last_name')
            ->with('customer:id,full_name')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(fn (Booking $b) => [
                'id' => $b->id,
                'reference' => '#' . $b->booking_number,
                'label' => sprintf('#%d — %s', $b->booking_number, self::resolveName($b)),
            ]);

        return view('eticket.builder', compact('bookings'));
    }

    /** JSON snapshot of a booking's agent/passenger/flight data, used to prefill the builder form. */
    public function data(Booking $booking)
    {
        $booking->load(['user', 'customer', 'passengers', 'flightDetails']);

        return response()->json([
            'bookingRef' => '#' . $booking->booking_number,
            'customerName' => self::resolveName($booking),
            'issueDate'  => \Carbon\Carbon::parse($booking->invoiced_at ?? $booking->created_at)->format('d M Y'),
            'status'     => Booking::STATUS_LABELS[$booking->booking_status === 'invoiced' ? 'issued' : $booking->booking_status]
                ?? ucfirst(str_replace('_', ' ', $booking->booking_status)),
            'agentName'    => $booking->user->name ?? '',
            'agentPhone'   => $booking->user->phone ?? '',
            'agentWhatsapp'=> $booking->user->whatsapp ?? '',
            'agentEmail'   => $booking->user->email ?? '',
            'passengers'   => $booking->passengers->map(fn ($p) => [
                'name'       => $p->display_name,
                'type'       => $p->type_label,
                'eticket'    => $p->e_ticket_number ?? '',
                'airlineRef' => $p->airline_reference ?? '',
            ])->values(),
            'segments' => $booking->flightDetails->map(fn ($f) => [
                'airline'           => $f->airline ?? '',
                'flightNumber'      => $f->flight_number ?? '',
                'cabin'             => $f->cabin ?? '',
                'flightType'        => $f->flight_type ?? 'return',
                'departureAirport'  => $f->departure_airport ?? '',
                'arrivalAirport'    => $f->arrival_airport ?? '',
                'departureDate'     => $f->departure_date ? $f->departure_date->format('Y-m-d') : '',
                'departureTime'     => $f->departure_time ?? '',
                'depTerminal'       => $f->dep_terminal ?? '',
                'returnDate'        => $f->return_date ? $f->return_date->format('Y-m-d') : '',
                'arrivalTime'       => $f->arrival_time ?? '',
                'arrTerminal'       => $f->arr_terminal ?? '',
                'arrivalNextDay'    => (bool) $f->arrival_next_day,
                'duration'          => $f->duration ?? '',
                'seat'              => $f->seat ?? '',
                'baggage'           => $f->baggage_allowance ?? '',
                'rezClass'          => $f->rez_class ?? '',
                'fareBasis'         => $f->fare_basis ?? '',
                'nvb'               => $f->nvb ?? '',
                'nva'               => $f->nva ?? '',
                'locator'           => $f->locator ?? '',
                'airlineLocator'    => $f->airline_locator ?? '',
                'reservationStatus' => $f->reservation_status ?? '',
                'ticketStatus'      => $f->ticket_status ?: 'O',
            ])->values(),
        ]);
    }

    /**
     * The booker's own entered name, not the linked customer record — most
     * bookings here point at a shared "Default Customer" placeholder, so
     * customer->full_name is almost never the traveller's real name.
     */
    private static function resolveName(Booking $booking): string
    {
        $booker = trim("{$booking->booker_first_name} {$booking->booker_last_name}");

        return $booker !== '' ? $booker : ($booking->customer->full_name ?? 'Unnamed');
    }
}
