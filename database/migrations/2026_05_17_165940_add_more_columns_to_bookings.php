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
            $table->string('referral_name')->nullable()->after('lead_nature');
            $table->string('booker_address')->nullable()->after('booker_whatsapp');
            $table->string('booker_city')->nullable()->after('booker_address');
            $table->string('booker_postcode')->nullable()->after('booker_city');
            $table->string('booker_country')->default('UK')->after('booker_postcode');
            $table->string('emergency_contact_name')->nullable()->after('booker_country');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('via_airport')->nullable()->after('arrival_airport');
            $table->string('baggage_allowance')->nullable()->after('via_airport');
            $table->enum('seat_preference', ['window', 'aisle', 'no_preference'])->default('no_preference')->after('baggage_allowance');
            $table->text('special_requests')->nullable()->after('seat_preference');
            $table->decimal('service_fee', 10, 2)->default(0)->after('sale_price');
            $table->string('currency', 3)->default('GBP')->after('service_fee');
        });

        DB::statement("ALTER TABLE bookings ALTER COLUMN margin TYPE numeric(10,2)");
        DB::statement("ALTER TABLE bookings ALTER COLUMN margin SET DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'referral_name', 'booker_address', 'booker_city', 'booker_postcode',
                'booker_country', 'emergency_contact_name', 'emergency_contact_phone',
                'via_airport', 'baggage_allowance', 'seat_preference', 'special_requests',
                'service_fee', 'currency',
            ]);
        });
    }
};
