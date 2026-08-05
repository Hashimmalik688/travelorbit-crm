<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Appends synthesized activity_log entries for gaps found by
 * activity-log:audit-gaps — same matching logic, but writes.
 *
 * Purely additive: never deletes or reorders existing activity_log entries,
 * only appends one entry per confirmed-missing audit_logs row, using that
 * row's real user/timestamp/description. Runs per-booking inside a
 * transaction. Defaults to a dry run; pass --commit to actually write.
 */
class BackfillActivityLogGaps extends Command
{
    protected $signature = 'activity-log:backfill-gaps
        {--booking= : Restrict to a single booking ID}
        {--minutes=3 : Match window (+/- minutes) used to decide an entry is missing}
        {--commit : Actually write the missing entries (default is dry run)}';

    protected $description = 'Backfill Booking activity_log entries that exist in audit_logs but are missing from activity_log (dry run unless --commit)';

    private const RELEVANT_ACTIONS = [
        'status_changed', 'refund_requested', 'payment_requested', 'payment_deleted',
        'payment_voided', 'payment_approved', 'payment_rejected', 'pricing_updated',
        'booking_ticket_in_process', 'booking_invoiced', 'booking_issued', 'booking_queued',
        'booking_removed_from_queue', 'booking_rejected_from_queue', 'booking_restored_to_pending',
        'booking_status_reverted', 'booking_transferred', 'margin_shared', 'margin_share_removed',
        'pnr_cancelled',
    ];

    // audit_logs.action -> the activity_log 'action' label buildActivityLog()/getActivityColorConfig() expect.
    private const ACTION_LABELS = [
        'status_changed'              => 'Status Changed',
        'refund_requested'            => 'Refund Requested',
        'payment_requested'           => 'Request Payment Charge',
        'payment_deleted'             => 'Payment Deleted',
        'payment_voided'              => 'Payment Voided',
        'payment_approved'            => 'Payment Approved',
        'payment_rejected'            => 'Payment Declined',
        'pricing_updated'             => 'Pricing Updated',
        'booking_ticket_in_process'   => 'Ticket In Process',
        'booking_invoiced'            => 'Booking Issued',
        'booking_issued'              => 'Booking Issued',
        'booking_queued'              => 'Request Issuance',
        'booking_removed_from_queue'  => 'Removed From Queue',
        'booking_rejected_from_queue' => 'Rejected From Queue',
        'booking_restored_to_pending' => 'Restored to Pending',
        'booking_status_reverted'     => 'Status Reverted',
        'booking_transferred'         => 'Booking Transferred',
        'margin_shared'               => 'Margin Shared',
        'margin_share_removed'        => 'Margin Share Removed',
        'pnr_cancelled'               => 'Cancel PNR',
    ];

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $commit  = (bool) $this->option('commit');

        $query = AuditLog::where('model', 'Booking')
            ->whereIn('action', self::RELEVANT_ACTIONS)
            ->orderBy('model_id')->orderBy('created_at');

        if ($bookingId = $this->option('booking')) {
            $query->where('model_id', $bookingId);
        }

        $auditRows = $query->get();
        if ($auditRows->isEmpty()) {
            $this->info('No matching audit_logs rows found.');
            return self::SUCCESS;
        }

        $bookingIds = $auditRows->pluck('model_id')->unique()->filter()->values();
        $bookings = Booking::whereIn('id', $bookingIds)->get()->keyBy('id');

        $userIds = $auditRows->pluck('user_id')->unique()->filter()->values();
        $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

        $totalWritten = 0;

        foreach ($auditRows->groupBy('model_id') as $bookingId => $rows) {
            $booking = $bookings->get($bookingId);
            if (!$booking) continue;

            $log = $booking->activity_log ?? [];
            if (is_string($log)) $log = json_decode($log, true) ?? [];

            $entryTimes = collect($log)->map(fn ($e) => [
                'user_id' => $e['user_id'] ?? null,
                'ts'      => isset($e['timestamp']) ? Carbon::parse($e['timestamp']) : null,
            ])->filter(fn ($e) => $e['ts'] !== null);

            $toAppend = [];

            foreach ($rows as $row) {
                $rowTs = Carbon::parse($row->created_at);
                $hasMatch = $entryTimes->contains(function ($e) use ($row, $rowTs, $minutes) {
                    $sameUser = $e['user_id'] === null || $row->user_id === null || $e['user_id'] == $row->user_id;
                    return $sameUser && abs($e['ts']->diffInSeconds($rowTs)) <= ($minutes * 60);
                });

                if ($hasMatch) continue;

                // Prefer the real username over audit_logs.user_email — every
                // other write path (logActivity(), appendBookingActivity())
                // shows $user->name, so a backfilled entry showing an email
                // address stands out as obviously synthetic.
                $rowUser = $row->user_id ? $users->get($row->user_id) : null;
                $agentName = $rowUser?->name ?: ($row->user_email ?: 'System');
                $ini = strtoupper(substr($agentName, 0, 1));
                if (($sp = strpos($agentName, ' ')) !== false) $ini .= strtoupper(substr($agentName, $sp + 1, 1));

                $toAppend[] = [
                    'agent'           => $agentName,
                    'avatar_url'      => $rowUser?->profile_photo_path ? asset('storage/' . $rowUser->profile_photo_path) : null,
                    'avatar_initials' => $ini,
                    'user_id'         => $row->user_id,
                    'timestamp'       => $rowTs->toDateTimeString(),
                    'action'          => self::ACTION_LABELS[$row->action] ?? $row->action,
                    'detail'          => ($row->description ?? '') . ' (recovered from audit log #' . $row->id . ')',
                    'type'            => 'update',
                ];

                $this->line("Booking #{$booking->booking_number}: + [{$row->action}] {$row->description} ({$rowTs})");
            }

            if (empty($toAppend)) continue;

            if ($commit) {
                DB::transaction(function () use ($booking, $log, $toAppend) {
                    // Re-read fresh inside the transaction in case anything wrote since the report ran.
                    $fresh = Booking::where('id', $booking->id)->lockForUpdate()->value('activity_log') ?? [];
                    if (is_string($fresh)) $fresh = json_decode($fresh, true) ?? [];
                    $merged = array_values(array_merge($fresh, $toAppend));
                    Booking::where('id', $booking->id)->update(['activity_log' => $merged]);
                });
                $this->info("  -> wrote " . count($toAppend) . " entr" . (count($toAppend) === 1 ? 'y' : 'ies') . " to booking #{$booking->booking_number}");
            }

            $totalWritten += count($toAppend);
        }

        $this->newLine();
        if (!$commit) {
            $this->warn("DRY RUN — {$totalWritten} entries would be written. Re-run with --commit to apply.");
        } else {
            $this->info("Done — {$totalWritten} entries written.");
        }

        return self::SUCCESS;
    }
}
