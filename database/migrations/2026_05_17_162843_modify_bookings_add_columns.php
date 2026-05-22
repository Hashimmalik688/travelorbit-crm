<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('booking_number')->unique()->after('id');
            $table->enum('lead_source', ['facebook', 'whatsapp', 'instagram', 'referral', 'walk_in', 'other'])->after('user_id');
            $table->enum('lead_nature', ['new', 'returning', 'group'])->after('lead_source');
            $table->string('booker_name')->after('lead_nature');
            $table->string('booker_phone')->after('booker_name');
            $table->string('booker_email')->nullable()->after('booker_phone');
            $table->string('booker_whatsapp')->nullable()->after('booker_email');
            $table->string('airline')->nullable()->after('route');
            $table->string('flight_number')->nullable()->after('airline');
            $table->string('departure_airport')->nullable()->after('flight_number');
            $table->string('arrival_airport')->nullable()->after('departure_airport');
            $table->enum('ticket_class', ['economy', 'business', 'first'])->default('economy')->after('arrival_airport');
            $table->decimal('margin', 10, 2)->nullable()->after('sale_price');
            $table->renameColumn('status', 'booking_status');
            $table->timestamp('issuance_requested_at')->nullable()->after('booking_status');
            $table->timestamp('refund_requested_at')->nullable()->after('issuance_requested_at');
            $table->text('refund_reason')->nullable()->after('refund_requested_at');
        });

        // Update booking_status CHECK constraint to include new values (PostgreSQL compatible)
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_booking_status_check CHECK (booking_status IN ('pending', 'confirmed', 'issued', 'cancelled', 'refund_queue', 'awaiting_issuance'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_number', 'lead_source', 'lead_nature', 'booker_name',
                'booker_phone', 'booker_email', 'booker_whatsapp', 'airline',
                'flight_number', 'departure_airport', 'arrival_airport',
                'ticket_class', 'margin', 'issuance_requested_at',
                'refund_requested_at', 'refund_reason'
            ]);
            $table->renameColumn('booking_status', 'status');
        });
    }
};
