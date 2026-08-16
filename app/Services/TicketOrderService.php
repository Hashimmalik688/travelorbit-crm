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
 * form) and emails it — used exclusively by the Issuance Queue's "Send
 * Ticket Order" checkbox inside the Process/Approve dialog (see
 * BookingWorkflowController::markTicketInProcess). There is deliberately no
 * other way to trigger a ticket order: no button on the booking page, no
 * standalone form — only issuance.manage permission holders can send one,
 * and only once a booking has actually reached the issuance queue.
 */
class TicketOrderService
{
    /**
     * @param bool $logComment Set false for a one-off test send (see the
     *   Issuance Queue path) that shouldn't leave the "Ticket Order Sent"
     *   badge in the booking's Comments feed.
     * @param ?string $to  Comma-separated recipient addresses. Falls back to
     *   TICKET_ORDER_MAIL_TO when blank — the Issuance Queue's "Send Ticket
     *   Order" checkbox lets the agent type these in per booking.
     * @param ?string $cc  Comma-separated CC addresses, optional.
     * @param ?string $bcc Comma-separated BCC addresses, optional.
     */
    public static function createAndSend(
        Booking $booking,
        User $agent,
        bool $logComment = true,
        ?string $to = null,
        ?string $cc = null,
        ?string $bcc = null,
    ): TicketOrder {
        $booking->loadMissing(['passengers', 'flightDetails']);

        $firstFd = $booking->flightDetails->first();
        $issuedTo = $firstFd && $firstFd->vendor
            ? ucwords(str_replace(['-', '_'], ' ', $firstFd->vendor))
            : 'N/A';

        $passengersList = $booking->passengers->values();
        $nonInfant = $passengersList->filter(fn ($p) => $p->passenger_type !== 'infant')->count();
        $atol = (bool) ($firstFd?->atol);
        $safi = (bool) ($firstFd?->safi);
        $safiCharges = ($atol ? 2.50 * $nonInfant : 0) + ($safi ? 2.50 * $nonInfant : 0);

        $costSums = ['cost_adult' => 0, 'cost_child' => 0, 'cost_infant' => 0];
        foreach ($booking->flightDetails as $fd) {
            foreach ($fd->passenger_costs ?? [] as $pi => $pc) {
                $type = $passengersList->get($pi)?->passenger_type ?? 'adult';
                $bucket = in_array($type, ['child', 'infant'], true) ? $type : 'adult';
                $costSums["cost_{$bucket}"] += (float) ($pc['cost'] ?? 0);
            }
        }

        $ticketOrder = TicketOrder::create([
            'booking_id' => $booking->id,
            'requested_by' => $agent->id,
            'issued_to' => $issuedTo,
            'cost_adult' => $costSums['cost_adult'],
            'cost_child' => $costSums['cost_child'],
            'cost_infant' => $costSums['cost_infant'],
            'atol' => $atol,
            'safi' => $safi,
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
                'booked_in' => $fd->gds ?: null,
                'issue_from' => $fd->vendor ?: null,
                'airline' => $fd->airline ?: null,
                'pnr' => $fd->pnr ?: null,
                'sort_order' => $i,
            ]);
        }

        // Comment-feed-only marker — deliberately NOT written to the JSON
        // activity_log / AuditLog like every other issuance-queue event
        // (see BookingWorkflowController::appendBookingActivity). The user
        // asked for this to show up as a coloured comment badge and nowhere
        // else — no status banner, no audit trail entry. Skipped entirely
        // for a test send (see $logComment).
        if ($logComment) {
            BookingComment::create([
                'booking_id' => $booking->id,
                'user_id' => $agent->id,
                'agent_name' => $agent->name,
                'avatar_url' => $agent->profile_photo_path ? asset('storage/' . $agent->profile_photo_path) : null,
                'action' => 'ticket_order_sent',
                'comment' => "Ticket order sent to {$issuedTo}.",
                'preset' => 'ticket_order_sent',
            ]);
        }

        $toAddresses = self::parseEmailList($to);
        if (empty($toAddresses)) {
            $toAddresses = self::parseEmailList(env('TICKET_ORDER_MAIL_TO', 'info@travelorbit.co.uk'));
        }
        $ccAddresses = self::parseEmailList($cc);
        $bccAddresses = self::parseEmailList($bcc);

        $ticketOrder->load('passengers', 'segments', 'requestedBy', 'booking');
        try {
            $mailer = Mail::mailer('ticket_order_smtp')->to($toAddresses);
            if ($ccAddresses) $mailer->cc($ccAddresses);
            if ($bccAddresses) $mailer->bcc($bccAddresses);
            $mailer->send(new TicketOrderMail($ticketOrder));

            $ticketOrder->update([
                'sent_at' => now(),
                'sent_to' => implode(', ', $toAddresses) ?: null,
                'sent_cc' => implode(', ', $ccAddresses) ?: null,
                'sent_bcc' => implode(', ', $bccAddresses) ?: null,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $ticketOrder;
    }

    /** Splits a comma/semicolon-separated string into trimmed, de-duped, valid email addresses. */
    private static function parseEmailList(?string $raw): array
    {
        if (!$raw) return [];
        return collect(preg_split('/[,;]+/', $raw))
            ->map(fn ($e) => trim($e))
            ->filter(fn ($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }
}
