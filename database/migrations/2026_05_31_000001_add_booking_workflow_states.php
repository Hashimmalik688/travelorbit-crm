<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old constraint and add new one with full workflow states
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_booking_status_check");
        DB::statement("
            ALTER TABLE bookings ADD CONSTRAINT bookings_booking_status_check
            CHECK (booking_status IN (
                'pending',
                'issuance_queue',
                'ticket_in_process',
                'invoiced',
                'confirmed',
                'cancelled',
                'refund_queue',
                'awaiting_issuance',
                'issued'
            ))
        ");

        // Rename awaiting_issuance → issuance_queue for existing rows
        DB::statement("UPDATE bookings SET booking_status = 'issuance_queue' WHERE booking_status = 'awaiting_issuance'");

        // Add invoiced_at and ticket_processed_at timestamps
        DB::statement("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS invoiced_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS ticket_processed_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS issuance_queued_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS locked_by_role VARCHAR(50) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_booking_status_check");
        DB::statement("
            ALTER TABLE bookings ADD CONSTRAINT bookings_booking_status_check
            CHECK (booking_status IN ('pending','confirmed','issued','cancelled','refund_queue','awaiting_issuance'))
        ");
        DB::statement("UPDATE bookings SET booking_status = 'awaiting_issuance' WHERE booking_status = 'issuance_queue'");
    }
};
