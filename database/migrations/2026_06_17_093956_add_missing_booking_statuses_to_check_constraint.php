<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_booking_status_check");

        DB::statement("
            ALTER TABLE bookings ADD CONSTRAINT bookings_booking_status_check
            CHECK (booking_status IN (
                'pending',
                'confirmed',
                'issuance_queue',
                'ticket_in_process',
                'invoiced',
                'issued',
                'issued_payment_awaiting',
                'issued_payment_plan',
                'payment_charge_request',
                'cancelled',
                'refund_queue'
            ))
        ");
    }

    public function down(): void
    {
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
                'issued'
            ))
        ");
    }
};
