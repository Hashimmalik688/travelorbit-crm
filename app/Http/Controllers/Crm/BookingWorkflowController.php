<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Services\TicketOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingWorkflowController extends Controller
{
    private function appendBookingActivity(Booking $booking, string $action, string $detail): void
    {
        // God-mode admins (CEO) operate untracked — no activity log entry at all.
        // updateQuietly() below bypasses model events, so guard explicitly here.
        if (Auth::user()?->isGodMode()) {
            return;
        }
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
        $reason = request('reason');
        $detail = 'Rejected from Issuance Queue — returned to Pending' . ($reason ? " — $reason" : '');
        $this->appendBookingActivity($booking, 'status_changed', $detail);
        AuditLog::logAction(
            action: 'booking_rejected_from_queue',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} rejected from issuance queue" . ($reason ? ": $reason" : ""),
        );
        return back()->with('success', "Booking #{$booking->booking_number} rejected from issuance queue.");
    }

    public function markTicketInProcess(Booking $booking)
    {
        $this->authorize('markTicketInProcess', $booking);
        $processedDate = request('processed_date', now()->toDateString());
        $booking->update([
            'booking_status'       => Booking::STATUS_TICKET_IN_PROCESS,
            'ticket_processed_at'  => $processedDate . ' ' . now()->format('H:i:s'),
        ]);
        $reason = request('reason');
        $detail = 'Ticket In Process' . ($reason ? " — $reason" : '') . ' (Date: ' . $processedDate . ')';
        $this->appendBookingActivity($booking, 'status_changed', $detail);
        AuditLog::logAction(
            action: 'booking_ticket_in_process',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} marked as Ticket in Process" . ($reason ? ": $reason" : "") . " (Date: {$processedDate})",
        );

        // "Send Ticket Order" checkbox — grabs its data straight from THIS
        // booking (the one the issuance queue row was for) and emails it.
        // Deliberately not gated behind appendBookingActivity/AuditLog above;
        // see TicketOrderService::createAndSend for why it only ever shows
        // up as a comment badge.
        $sentTicketOrder = false;
        if (request()->boolean('send_ticket_order')) {
            TicketOrderService::createAndSend($booking, Auth::user());
            $sentTicketOrder = true;
        }

        $message = "Booking #{$booking->booking_number} marked as Ticket in Process.";
        if ($sentTicketOrder) $message .= ' Ticket order sent.';
        return back()->with('success', $message);
    }

    /**
     * Invoicing always lands the booking on plain "Issued", never on a
     * still-owing disposition: a booking can only be invoiced once the money is
     * all in (Booking::canInvoice), so leaving it on "Issued - Payment Plan" or
     * "Issued - Payment Awaiting" would misreport a settled sale as outstanding.
     * The invoice itself is recorded by the invoiced_at stamp — that's what the
     * INVOICED seal on the booking page and canInvoice() both read.
     */
    public function invoice(Booking $booking)
    {
        $this->authorize('invoice', $booking);
        $wasStatus = $booking->booking_status;
        $booking->update([
            'booking_status' => Booking::STATUS_ISSUED,
            'invoiced_at'    => now(),
        ]);
        $wasLabel = Booking::STATUS_LABELS[$wasStatus] ?? $wasStatus;
        $detail = 'Invoiced' . ($wasStatus !== Booking::STATUS_ISSUED ? " — status changed: {$wasLabel} → Issued" : '');
        $this->appendBookingActivity($booking, 'status_changed', $detail);
        AuditLog::logAction(
            action: 'booking_invoiced',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} invoiced — status set to Issued (was {$wasStatus})",
        );
        return back()->with('success', "Booking #{$booking->booking_number} invoiced — status set to Issued.");
    }

    public function issue(Booking $booking, Request $request)
    {
        $this->authorize('issue', $booking);

        $request->validate([
            'payment_type'      => 'required|string|in:issued,issued_payment_plan,issued_payment_awaiting',
            'issue_date'        => 'required|date',
            'last_payment_date' => 'nullable|date|required_if:payment_type,issued_payment_plan,issued_payment_awaiting',
        ]);

        $paymentType = $request->input('payment_type');
        $oldStatus = $booking->booking_status;

        $updates = [
            'booking_status'  => $paymentType,
            'last_issue_date' => $request->input('issue_date'),
        ];
        // Last payment date only applies to payment plan / payment awaiting dispositions
        if (in_array($paymentType, [Booking::STATUS_ISSUED_PAYMENT_PLAN, Booking::STATUS_ISSUED_PAYMENT_AWAITING], true)) {
            $updates['last_payment_date'] = $request->input('last_payment_date');
        }

        $booking->update($updates);

        $label = Booking::STATUS_LABELS[$paymentType] ?? 'Issued';

        $this->appendBookingActivity($booking, 'status_changed', "Issued — {$label}");
        AuditLog::logAction(
            action: 'booking_issued',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} issued — {$label}",
        );

        return back()->with('success', "Booking #{$booking->booking_number} issued — {$label}.");
    }

    public function restoreToPending(Booking $booking)
    {
        $this->authorize('restoreToPending', $booking);
        $booking->update(['booking_status' => Booking::STATUS_PENDING]);
        $reason = request('reason');
        $detail = 'Restored to Pending' . ($reason ? " — $reason" : '');
        $this->appendBookingActivity($booking, 'status_changed', $detail);
        AuditLog::logAction(
            action: 'booking_restored_to_pending',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} restored to Pending" . ($reason ? ": $reason" : ""),
        );
        return back()->with('success', "Booking #{$booking->booking_number} restored to Pending.");
    }
}
