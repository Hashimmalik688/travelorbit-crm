<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingWorkflowController extends Controller
{
    private function appendBookingActivity(Booking $booking, string $action, string $detail): void
    {
        $user = Auth::user();
        $agent = $user->name ?? 'System';
        $ini = strtoupper(substr($agent, 0, 1));
        if (($sp = strpos($agent, ' ')) !== false) {
            $ini .= strtoupper(substr($agent, $sp + 1, 1));
        }
        $avatarUrl = $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;

        $log = $booking->activity_log ?? [];
        if (is_string($log)) {
            $log = json_decode($log, true) ?? [];
        }

        $log[] = [
            'agent'           => $agent,
            'avatar_url'      => $avatarUrl,
            'avatar_initials' => $ini,
            'user_id'         => $user->id,
            'timestamp'       => now()->toDateTimeString(),
            'action'          => $action,
            'detail'          => $detail,
            'type'            => 'update',
        ];

        $booking->updateQuietly(['activity_log' => $log]);
    }

    public function queueForIssuance(Booking $booking)
    {
        $this->authorize('queueForIssuance', $booking);
        $booking->update([
            'booking_status'    => Booking::STATUS_ISSUANCE_QUEUE,
            'issuance_queued_at'=> now(),
        ]);
        $this->appendBookingActivity($booking, 'status_changed', 'Moved to Issuance Queue');
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
        $this->appendBookingActivity($booking, 'status_changed', 'Removed from Issuance Queue — restored to Pending');
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
        $this->appendBookingActivity($booking, 'status_changed', 'Ticket In Process');
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
        $this->appendBookingActivity($booking, 'status_changed', 'Invoiced');
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
        $this->appendBookingActivity($booking, 'status_changed', 'Restored to Pending');
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
