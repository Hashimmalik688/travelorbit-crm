<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Read-only report: finds Booking lifecycle events that have an audit_logs
 * row (a plain INSERT, never subject to the activity_log overwrite bug) but
 * no matching entry in the booking's activity_log JSON column — i.e. events
 * that were logged and then silently lost when a stale BookingShow session
 * (or BookingWorkflowController / PaymentChargeRequest write) overwrote the
 * column. See the activity_log race-condition fix in BookingShow.php.
 *
 * This command makes NO writes. It only reads audit_logs and bookings.activity_log
 * and prints what a backfill would restore, so it can be reviewed before any
 * backfill command runs.
 */
class AuditActivityLogGaps extends Command
{
    protected $signature = 'activity-log:audit-gaps
        {--booking= : Restrict to a single booking ID}
        {--minutes=3 : Match window (+/- minutes) between an audit_logs row and an activity_log entry}';

    protected $description = 'Dry-run report of Booking activity_log entries that were logged in audit_logs but appear to be missing from activity_log (never writes anything)';

    /**
     * audit_logs.action values that are expected to have a companion
     * activity_log entry. Excludes 'created' (seeded at creation, before any
     * concurrent write is possible), 'comment_added' (lives in booking_comments,
     * a separate table, not activity_log), and test/noise actions.
     */
    private const RELEVANT_ACTIONS = [
        'status_changed',
        'refund_requested',
        'payment_requested',
        'payment_deleted',
        'payment_voided',
        'payment_approved',
        'payment_rejected',
        'pricing_updated',
        'booking_ticket_in_process',
        'booking_invoiced',
        'booking_issued',
        'booking_queued',
        'booking_removed_from_queue',
        'booking_rejected_from_queue',
        'booking_restored_to_pending',
        'booking_status_reverted',
        'booking_transferred',
        'margin_shared',
        'margin_share_removed',
        'pnr_cancelled',
    ];

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');

        $query = AuditLog::where('model', 'Booking')
            ->whereIn('action', self::RELEVANT_ACTIONS)
            ->orderBy('model_id')
            ->orderBy('created_at');

        if ($bookingId = $this->option('booking')) {
            $query->where('model_id', $bookingId);
        }

        $auditRows = $query->get();

        if ($auditRows->isEmpty()) {
            $this->info('No matching audit_logs rows found.');
            return self::SUCCESS;
        }

        $bookingIds = $auditRows->pluck('model_id')->unique()->filter()->values();
        $bookings = Booking::whereIn('id', $bookingIds)->get(['id', 'booking_number', 'activity_log'])->keyBy('id');

        $missing = [];
        $checked = 0;

        foreach ($auditRows->groupBy('model_id') as $bookingId => $rows) {
            $booking = $bookings->get($bookingId);
            if (!$booking) {
                continue; // booking since deleted — nothing to backfill into
            }

            $log = $booking->activity_log ?? [];
            if (is_string($log)) $log = json_decode($log, true) ?? [];

            // Pre-parse activity_log entry timestamps once per booking.
            $entryTimes = collect($log)
                ->map(fn ($e) => [
                    'user_id' => $e['user_id'] ?? null,
                    'ts'      => isset($e['timestamp']) ? Carbon::parse($e['timestamp']) : null,
                ])
                ->filter(fn ($e) => $e['ts'] !== null);

            foreach ($rows as $row) {
                $checked++;
                $rowTs = Carbon::parse($row->created_at);

                $hasMatch = $entryTimes->contains(function ($e) use ($row, $rowTs, $minutes) {
                    $sameUser = $e['user_id'] === null || $row->user_id === null || $e['user_id'] == $row->user_id;
                    return $sameUser && abs($e['ts']->diffInSeconds($rowTs)) <= ($minutes * 60);
                });

                if (!$hasMatch) {
                    $missing[] = [
                        'booking_id'     => $bookingId,
                        'booking_number' => $booking->booking_number,
                        'audit_id'       => $row->id,
                        'action'         => $row->action,
                        'description'    => $row->description,
                        'user_email'     => $row->user_email,
                        'created_at'     => $rowTs->toDateTimeString(),
                    ];
                }
            }
        }

        $this->info("Checked {$checked} audit_logs rows across {$bookingIds->count()} booking(s).");
        $this->newLine();

        if (empty($missing)) {
            $this->info('No gaps found — activity_log appears to match audit_logs for everything checked.');
            return self::SUCCESS;
        }

        $this->warn(count($missing) . ' likely-missing activity_log entr' . (count($missing) === 1 ? 'y' : 'ies') . ' found:');
        $this->table(
            ['Booking #', 'Audit ID', 'Action', 'Description', 'User', 'When'],
            collect($missing)->map(fn ($m) => [
                $m['booking_number'],
                $m['audit_id'],
                $m['action'],
                \Illuminate\Support\Str::limit($m['description'], 60),
                $m['user_email'],
                $m['created_at'],
            ])
        );

        $affectedBookings = collect($missing)->pluck('booking_number')->unique()->count();
        $this->newLine();
        $this->info("Affects {$affectedBookings} booking(s). No data has been changed — this is a dry run.");

        return self::SUCCESS;
    }
}
