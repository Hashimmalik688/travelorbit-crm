<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up existing test data (1 booking)
        DB::table('booking_comments')->delete();
        DB::table('booking_passengers')->delete();
        DB::table('booking_payments')->delete();
        DB::table('bookings')->delete();

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'lead_nature',
                'booker_name',
                'booker_phone',
                'booker_city',
                'emergency_contact_name',
                'emergency_contact_phone',
                'supplier',
            ]);

            $table->string('booker_title')->nullable()->after('referral_name');
            $table->string('booker_first_name')->nullable()->after('booker_title');
            $table->string('booker_last_name')->nullable()->after('booker_first_name');
            $table->string('booker_mobile')->nullable()->after('booker_last_name');
            $table->string('booker_landline')->nullable()->after('booker_mobile');

            $table->text('booker_address')->nullable()->change();
        });

        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_lead_source_check");
        DB::statement("ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_booking_type_check");

        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_lead_source_check CHECK (lead_source IN ('to_returning', 'to_referral', 'referral_client', 'returning_client', 'fb', 'wa', 'email', 'diaspora_group', 'instagram', 'tiktok', 'website', 'google'))");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_booking_type_check CHECK (booking_type IN ('flight', 'hotel', 'umrah', 'holiday', 'transfers', 'ancillary_services'))");
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booker_title', 'booker_first_name', 'booker_last_name', 'booker_mobile', 'booker_landline']);

            $table->enum('lead_nature', ['new', 'returning', 'group'])->after('lead_source');
            $table->string('booker_name')->after('lead_nature');
            $table->string('booker_phone')->after('booker_name');
            $table->string('booker_city')->nullable()->after('booker_address');
            $table->string('emergency_contact_name')->nullable()->after('booker_country');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('supplier')->nullable()->after('passenger_count');
        });
    }
};
