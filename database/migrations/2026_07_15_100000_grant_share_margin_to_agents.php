<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * bookings.share_margin was originally granted only to admin/manager, then
 * corrected to also be an agent-facing permission (agents share margin with
 * each other). Backfill existing agent/operations users who predate that fix.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->whereIn('role', ['agent', 'operations'])->get() as $user) {
            $permissions = json_decode($user->permissions ?? '[]', true) ?: [];

            if (!in_array('bookings.share_margin', $permissions, true)) {
                $permissions[] = 'bookings.share_margin';
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode(array_values($permissions)),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('users')->whereIn('role', ['agent', 'operations'])->get() as $user) {
            $permissions = json_decode($user->permissions ?? '[]', true) ?: [];
            $permissions = array_values(array_diff($permissions, ['bookings.share_margin']));
            DB::table('users')->where('id', $user->id)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }
};
