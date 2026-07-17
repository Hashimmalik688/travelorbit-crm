<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill existing manager users with the new reports.apply_deduction
 * permission introduced alongside the margin_deductions table, so managers
 * who predate this feature don't lose access.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->where('role', 'manager')->get() as $user) {
            $permissions = json_decode($user->permissions ?? '[]', true) ?: [];

            if (!in_array('reports.apply_deduction', $permissions, true)) {
                $permissions[] = 'reports.apply_deduction';
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode(array_values($permissions)),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('users')->where('role', 'manager')->get() as $user) {
            $permissions = json_decode($user->permissions ?? '[]', true) ?: [];
            $permissions = array_values(array_diff($permissions, ['reports.apply_deduction']));
            DB::table('users')->where('id', $user->id)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }
};
