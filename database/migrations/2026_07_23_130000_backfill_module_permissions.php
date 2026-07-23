<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The permission manager now exposes several modules that previously had no
 * key at all. Backfill existing users so nobody loses access they had before:
 *
 *  - eticket.access / calldesk.access — the E-Ticket Builder and Call Desk
 *    routes used to be ungated (any authenticated user). Grant to everyone.
 *  - bookings.own_issued — the Payment Plan / Payment Awaiting lists used to
 *    ride on bookings.create. Grant to anyone who has bookings.create.
 *  - reports.performance_all — the all-agents performance view used to be
 *    driven by data.view_all. Grant to anyone who has data.view_all so they
 *    keep seeing every agent's numbers.
 *
 * Admins are the hard-wired super-user (permissions column irrelevant), so
 * they're skipped. Idempotent — only adds what's missing.
 */
return new class extends Migration
{
    private array $addedKeys = [
        'eticket.access',
        'calldesk.access',
        'bookings.own_issued',
        'reports.performance_all',
    ];

    public function up(): void
    {
        foreach (DB::table('users')->where('role', '!=', 'admin')->get() as $user) {
            $current = json_decode($user->permissions ?? '[]', true) ?: [];
            $next    = $current;

            // Previously-ungated tools → everyone keeps access.
            $next[] = 'eticket.access';
            $next[] = 'calldesk.access';

            // Own issued lists used to be gated by bookings.create.
            if (in_array('bookings.create', $current, true)) {
                $next[] = 'bookings.own_issued';
            }

            // All-agents performance view used to be implied by data.view_all.
            if (in_array('data.view_all', $current, true)) {
                $next[] = 'reports.performance_all';
            }

            $next = array_values(array_unique($next));

            if ($next !== $current) {
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode($next),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('users')->where('role', '!=', 'admin')->get() as $user) {
            $current  = json_decode($user->permissions ?? '[]', true) ?: [];
            $stripped = array_values(array_diff($current, $this->addedKeys));

            if ($stripped !== $current) {
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode($stripped),
                ]);
            }
        }
    }
};
