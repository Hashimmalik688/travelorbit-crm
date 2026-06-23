<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->json('passenger_costs')->nullable()->after('sold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->dropColumn('passenger_costs');
        });
    }
};
