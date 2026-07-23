<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds "personal" to the allowed lead_source values. The column is guarded by
 * a CHECK constraint (see 2026_05_19_000001_restructure_bookings_for_wizard),
 * so the form option alone isn't enough — saving would fail at the DB level.
 */
return new class extends Migration
{
    private array $sources = [
        'to_returning', 'to_referral', 'referral_client', 'returning_client',
        'fb', 'wa', 'email', 'diaspora_group', 'instagram', 'tiktok',
        'website', 'google', 'personal',
    ];

    private function apply(array $values): void
    {
        $list = "'" . implode("', '", $values) . "'";
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_lead_source_check');
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_lead_source_check CHECK (lead_source IN ($list))");
    }

    public function up(): void
    {
        $this->apply($this->sources);
    }

    public function down(): void
    {
        // Any rows already using the new value would violate the old constraint.
        DB::table('bookings')->where('lead_source', 'personal')->update(['lead_source' => null]);

        $this->apply(array_values(array_diff($this->sources, ['personal'])));
    }
};
