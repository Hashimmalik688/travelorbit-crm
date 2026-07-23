<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

/**
 * Applies a manual booking status change on behalf of the Status Change page.
 *
 * The workflow buttons (queue / ticket-in-process / invoice / issue) each stamp
 * a date alongside the status. A page that wrote only `booking_status` would
 * leave e.g. an Issued booking with no issue date, which reads as zero issued
 * margin on the dashboards — so every transition here stamps the same field its
 * button counterpart does, and writes the same activity + audit entries.
 *
 * BookingWorkflowController is deliberately left untouched; this is the manual
 * override path, not a refactor of the normal flow.
 */
class BookingStatusService
{
    /**
     * Statuses whose booking is considered "in the issuance pipeline" — the only
     * bookings this page may touch. Runs from the moment a booking is queued
     * through to issued, including the two issued-but-still-owing dispositions.
     */
    public const PIPELINE_STATUSES = [
        Booking::STATUS_ISSUANCE_QUEUE,
        Booking::STATUS_TICKET_IN_PROCESS,
        Booking::STATUS_ISSUED,
        Booking::STATUS_ISSUED_PAYMENT_AWAITING,
        Booking::STATUS_ISSUED_PAYMENT_PLAN,
    ];

    /** The two dispositions that are meaningless without a last payment date. */
    public const NEEDS_LAST_PAYMENT_DATE = [
        Booking::STATUS_ISSUED_PAYMENT_AWAITING,
        Booking::STATUS_ISSUED_PAYMENT_PLAN,
    ];

    /**
     * Every status that may be selected as a target. 'awaiting_issuance' is a
     * legacy alias that maps to the same label as 'issuance_queue', so it is
     * dropped to avoid offering the same choice twice.
     */
    public static function selectableStatuses(): array
    {
        return array_values(array_diff(array_keys(Booking::STATUS_LABELS), ['awaiting_issuance']));
    }

    /** Is this booking one the Status Change page is allowed to act on? */
    public static function isInPipeline(Booking $booking): bool
    {
        return in_array($booking->booking_status, self::PIPELINE_STATUSES, true);
    }

    /**
     * The date column each status owns, mirroring the workflow buttons:
     *   queued     -> issuance_queued_at   (queueForIssuance)
     *   in process -> ticket_processed_at  (markTicketInProcess)
     *   invoiced   -> invoiced_at          (invoice)
     *   issued*    -> last_issue_date      (issue)
     * Statuses with no date of their own (pending, confirmed, cancelled, …)
     * stamp nothing.
     */
    public static function stampsFor(string $status, ?string $lastPaymentDate = null): array
    {
        $stamps = match ($status) {
            Booking::STATUS_ISSUANCE_QUEUE    => ['issuance_queued_at'  => now()],
            Booking::STATUS_TICKET_IN_PROCESS => ['ticket_processed_at' => now()],
            Booking::STATUS_INVOICED          => ['invoiced_at'         => now()],
            Booking::STATUS_ISSUED,
            Booking::STATUS_ISSUED_PAYMENT_AWAITING,
            Booking::STATUS_ISSUED_PAYMENT_PLAN => ['last_issue_date'   => now()->toDateString()],
            default                             => [],
        };

        if ($lastPaymentDate && in_array($status, self::NEEDS_LAST_PAYMENT_DATE, true)) {
            $stamps['last_payment_date'] = $lastPaymentDate;
        }

        return $stamps;
    }

    /**
     * Change the status, stamp the matching date, and record it in both the
     * booking's activity feed and the audit log. Returns the human-readable
     * label of the new status.
     */
    public function apply(Booking $booking, string $status, ?string $reason = null, ?string $lastPaymentDate = null): string
    {
        $from      = Booking::STATUS_LABELS[$booking->booking_status] ?? $booking->booking_status;
        $to        = Booking::STATUS_LABELS[$status] ?? $status;
        $wasStatus = $booking->booking_status;

        $booking->update(array_merge(
            ['booking_status' => $status],
            self::stampsFor($status, $lastPaymentDate),
        ));

        $detail = "Status changed: {$from} → {$to}" . ($reason ? " — {$reason}" : '');
        $this->appendBookingActivity($booking, 'status_changed', $detail);

        AuditLog::logAction(
            action: 'booking_status_changed',
            user: Auth::user(),
            model: 'Booking',
            model_id: $booking->id,
            description: "Booking #{$booking->booking_number} status changed from {$wasStatus} to {$status}"
                . ($reason ? ": {$reason}" : ''),
        );

        return $to;
    }

    /**
     * Mirrors BookingWorkflowController::appendBookingActivity so a manual
     * change reads identically to a workflow one in the booking's feed.
     */
    private function appendBookingActivity(Booking $booking, string $action, string $detail): void
    {
        // God-mode admins (CEO) operate untracked — no activity log entry at all.
        if (Auth::user()?->isGodMode()) {
            return;
        }
        $user  = Auth::user();
        $agent = $user->name ?? 'System';
        $ini   = strtoupper(substr($agent, 0, 1));
        if (($sp = strpos($agent, ' ')) !== false) {
            $ini .= strtoupper(substr($agent, $sp + 1, 1));
        }
        $avatarUrl = $user?->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null;

        $log = $booking->activity_log ?? [];
        if (is_string($log)) {
            $log = json_decode($log, true) ?? [];
        }

        $log[] = [
            'agent'           => $agent,
            'avatar_url'      => $avatarUrl,
            'avatar_initials' => $ini,
            'user_id'         => $user?->id,
            'timestamp'       => now()->toDateTimeString(),
            'action'          => $action,
            'detail'          => $detail,
            'type'            => 'update',
        ];

        $booking->updateQuietly(['activity_log' => $log]);
    }
}
