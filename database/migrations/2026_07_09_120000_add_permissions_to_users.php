<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill presets. Kept inline (not read from config) so this data
     * migration stays a stable historical record even if the registry
     * changes later. Mirrors the old role-based access exactly.
     */
    private function presets(): array
    {
        return [
            'agent' => [
                'bookings.create', 'bookings.queue_issuance',
                'customers.view', 'reports.performance',
            ],
            'operations' => [
                'bookings.create', 'bookings.queue_issuance', 'bookings.view_mine',
                'customers.view', 'reports.performance',
            ],
            'accounts' => [
                'bookings.create', 'bookings.view_mine', 'customers.view',
                'accounts.access', 'payments.charge', 'payments.invoice',
                'payments.issue', 'reports.view', 'reports.performance', 'data.view_all',
            ],
            'issuance' => [
                'issuance.access', 'issuance.manage',
            ],
            // Everything except the admin-only "All Bookings" (bookings.view_all).
            'manager' => [
                'bookings.create', 'bookings.queue_issuance', 'bookings.view_mine',
                'bookings.delete', 'bookings.edit_any', 'customers.view',
                'issuance.access', 'issuance.manage', 'accounts.access',
                'payments.charge', 'payments.invoice', 'payments.issue',
                'reports.view', 'reports.performance', 'refunds.manage', 'data.view_all',
            ],
            // admin is a hard-wired super-user; leave permissions null.
        ];
    }

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->jsonb('permissions')->nullable()->after('role');
        });

        foreach ($this->presets() as $role => $keys) {
            DB::table('users')
                ->where('role', $role)
                ->update(['permissions' => json_encode(array_values($keys))]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
