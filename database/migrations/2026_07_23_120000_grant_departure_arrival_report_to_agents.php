<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Departure/Arrival report is now available to agents & operations, scoped
 * to their own bookings (see reports.departure_arrival in config/permissions.php).
 * Existing agent/operations users predate this permission, so backfill it onto
 * them to match the intended default. Idempotent — only adds what's missing.
 */
return new class extends Migration
{
    private string $permission = 'reports.departure_arrival';

    private array $roles = ['agent', 'operations'];

    public function up(): void
    {
        foreach (DB::table('users')->whereIn('role', $this->roles)->get() as $user) {
            $current = json_decode($user->permissions ?? '[]', true) ?: [];

            if (! in_array($this->permission, $current, true)) {
                $current[] = $this->permission;
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode(array_values($current)),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('users')->whereIn('role', $this->roles)->get() as $user) {
            $current  = json_decode($user->permissions ?? '[]', true) ?: [];
            $stripped = array_values(array_diff($current, [$this->permission]));

            DB::table('users')->where('id', $user->id)->update([
                'permissions' => json_encode($stripped),
            ]);
        }
    }
};
