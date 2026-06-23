<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingWorkflowController extends Controller
{
    public function queueForIssuance(Booking $booking)
    {
        $this->authorize('queueForIssuance', $booking);
        $booking->update([
            'booking_status'    => Booking::STATUS_ISSUANCE_QUEUE,
            'issuance_queued_at'=> now(),
        ]);
        AuditLog::logAction(
            action: 'booking_queued',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} queued for issuance",
        );
        return back()->with('success', "Booking #{$booking->booking_number} queued for issuance.");
    }

    public function removeFromIssuanceQueue(Booking $booking)
    {
        $this->authorize('removeFromIssuanceQueue', $booking);
        $booking->update(['booking_status' => Booking::STATUS_PENDING]);
        AuditLog::logAction(
            action: 'booking_removed_from_queue',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} removed from issuance queue",
        );
        return back()->with('success', "Booking #{$booking->booking_number} removed from issuance queue.");
    }

    public function markTicketInProcess(Booking $booking)
    {
        $this->authorize('markTicketInProcess', $booking);
        $booking->update([
            'booking_status'       => Booking::STATUS_TICKET_IN_PROCESS,
            'ticket_processed_at'  => now(),
        ]);
        AuditLog::logAction(
            action: 'booking_ticket_in_process',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} marked as Ticket in Process",
        );
        return back()->with('success', "Booking #{$booking->booking_number} marked as Ticket in Process.");
    }

    public function invoice(Booking $booking)
    {
        $this->authorize('invoice', $booking);
        $booking->update([
            'booking_status' => Booking::STATUS_INVOICED,
            'invoiced_at'    => now(),
        ]);
        AuditLog::logAction(
            action: 'booking_invoiced',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} invoiced",
        );
        return back()->with('success', "Booking #{$booking->booking_number} invoiced.");
    }

    public function restoreToPending(Booking $booking)
    {
        $this->authorize('restoreToPending', $booking);
        $booking->update(['booking_status' => Booking::STATUS_PENDING]);
        AuditLog::logAction(
            action: 'booking_restored_to_pending',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} restored to Pending",
        );
        return back()->with('success', "Booking #{$booking->booking_number} restored to Pending.");
    }
}
