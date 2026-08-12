<?php

namespace App\Services;

use App\Mail\TicketOrderMail;
use App\Models\Booking;
use App\Models\BookingComment;
use App\Models\TicketOrder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Builds a Ticket Order straight off a booking's own stored data (no manual
 * form) and emails it — used by the issuance queue's "Send Ticket Order"
 * checkbox (see BookingWorkflowController::markTicketInProcess). The
 * agent-editable version of this same flow lives in BookingShow::requestTicketOrder/
 * submitTicketOrder, which prefills the same way but lets the agent adjust
 * before sending; this one goes straight from booking to email.
 */
class TicketOrderService
{
    public static function createAndSend(Booking $booking, User $agent): TicketOrder
    {
        $booking->loadMissing(['passengers', 'flightDetails']);

        $firstFd = $booking->flightDetails->first();
        $issuedTo = $firstFd && $firstFd->vendor
            ? ucwords(str_replace(['-', '_'], ' ', $firstFd->vendor))
            : 'N/A';

        $passengersList = $booking->passengers->values();
        $nonInfant = $passengersList->filter(fn ($p) => $p->passenger_type !== 'infant')->count();
        $safiCharges = 0;
        if ($firstFd) {
            if ($firstFd->atol) $safiCharges += 2.50 * $nonInfant;
            if ($firstFd->safi) $safiCharges += 2.50 * $nonInfant;
        }

        $sums = ['sold_adult' => 0, 'sold_child' => 0, 'sold_infant' => 0, 'cost_adult' => 0, 'cost_child' => 0, 'cost_infant' => 0];
        foreach ($booking->flightDetails as $fd) {
            foreach ($fd->passenger_costs ?? [] as $pi => $pc) {
                $type = $passengersList->get($pi)?->passenger_type ?? 'adult';
                $bucket = in_array($type, ['child', 'infant'], true) ? $type : 'adult';
                $sums["sold_{$bucket}"] += (float) ($pc['sold'] ?? 0);
                $sums["cost_{$bucket}"] += (float) ($pc['cost'] ?? 0);
            }
        }

        $ticketOrder = TicketOrder::create([
            'booking_id' => $booking->id,
            'requested_by' => $agent->id,
            'issued_to' => $issuedTo,
            'sold_adult' => $sums['sold_adult'],
            'sold_child' => $sums['sold_child'],
            'sold_infant' => $sums['sold_infant'],
            'cost_adult' => $sums['cost_adult'],
            'cost_child' => $sums['cost_child'],
            'cost_infant' => $sums['cost_infant'],
            'safi_charges' => $safiCharges,
        ]);

        foreach ($passengersList as $i => $p) {
            $ticketOrder->passengers()->create([
                'passenger_id' => $p->id,
                'name' => trim(($p->title ?? '') . ' ' . ($p->first_name ?? '') . ' ' . ($p->last_name ?? '')),
                'date_of_birth' => $p->date_of_birth ?: null,
                'passport_number' => $p->passport_number ?: null,
                'sort_order' => $i,
            ]);
        }

        foreach ($booking->flightDetails as $i => $fd) {
            $ticketOrder->segments()->create([
                'flight_detail_id' => $fd->id,
                'locator' => $fd->locator ?: null,
                'folder' => $firstFd->folder_number ?? null,
                'type' => 'Console',
                'booked_in' => $fd->gds ?: null,
                'issue_from' => $fd->vendor ?: null,
                'airline' => $fd->airline ?: null,
                'sort_order' => $i,
            ]);
        }

        // Comment-feed-only marker — deliberately NOT written to the JSON
        // activity_log / AuditLog like every other issuance-queue event
        // (see BookingWorkflowController::appendBookingActivity). The user
        // asked for this to show up as a coloured comment badge and nowhere
        // else — no status banner, no audit trail entry.
        BookingComment::create([
            'booking_id' => $booking->id,
            'user_id' => $agent->id,
            'agent_name' => $agent->name,
            'avatar_url' => $agent->profile_photo_path ? asset('storage/' . $agent->profile_photo_path) : null,
            'action' => 'ticket_order_sent',
            'comment' => "Ticket order sent to {$issuedTo}.",
            'preset' => 'ticket_order_sent',
        ]);

        $ticketOrder->load('passengers', 'segments', 'requestedBy', 'booking');
        try {
            Mail::mailer('ticket_order_smtp')
                ->to(env('TICKET_ORDER_MAIL_TO', 'tickets@travelorbit.co.uk'))
                ->send(new TicketOrderMail($ticketOrder));
            $ticketOrder->update(['sent_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $ticketOrder;
    }
}
