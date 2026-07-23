<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Status Change page is gated by bookings.change_status (see
 * config/permissions.php). The preset only seeds newly created users, so
 * existing issuance users predate the permission and would not see the page —
 * backfill it onto them to match the intended default. Idempotent, and only
 * touches the issuance role; anyone else is granted the box by hand in the
 * permission manager.
 */
return new class extends Migration
{
    private string $permission = 'bookings.change_status';

    private array $roles = ['issuance'];

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
