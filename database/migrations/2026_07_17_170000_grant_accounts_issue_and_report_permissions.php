<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Some accounts users were created (pre-permissions, or by hand) without the
 * full accounts permission set, leaving them unable to issue tickets or open
 * reports even though those are core accounts functions. Backfill the accounts
 * role with the permissions its preset already grants, so existing users match
 * the intended default. Idempotent — only adds what's missing.
 */
return new class extends Migration
{
    private array $permissions = [
        'payments.issue',
        'reports.view',
        'reports.performance',
        'customers.view',
    ];

    public function up(): void
    {
        foreach (DB::table('users')->where('role', 'accounts')->get() as $user) {
            $current = json_decode($user->permissions ?? '[]', true) ?: [];
            $merged  = array_values(array_unique(array_merge($current, $this->permissions)));

            if ($merged !== $current) {
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode($merged),
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('users')->where('role', 'accounts')->get() as $user) {
            $current = json_decode($user->permissions ?? '[]', true) ?: [];
            $stripped = array_values(array_diff($current, $this->permissions));

            DB::table('users')->where('id', $user->id)->update([
                'permissions' => json_encode($stripped),
            ]);
        }
    }
};
