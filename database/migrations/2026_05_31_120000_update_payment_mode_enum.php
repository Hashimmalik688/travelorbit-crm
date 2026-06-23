<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old enum constraint and replace with a flexible string column
        DB::statement("ALTER TABLE booking_payments DROP CONSTRAINT IF EXISTS booking_payments_payment_mode_check");
        DB::statement("ALTER TABLE booking_payments ALTER COLUMN payment_mode TYPE VARCHAR(50)");
        DB::statement("ALTER TABLE booking_payments ALTER COLUMN payment_mode SET DEFAULT 'none'");
        // Remap old 'cash' values to 'bank_transfer'
        DB::statement("UPDATE booking_payments SET payment_mode = 'bank_transfer' WHERE payment_mode = 'cash'");
    }

    public function down(): void
    {
        // Restore enum (best effort)
        DB::statement("ALTER TABLE booking_payments ALTER COLUMN payment_mode TYPE VARCHAR(50)");
    }
};
