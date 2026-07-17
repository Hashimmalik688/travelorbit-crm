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
            $table->string('flight_number', 20)->nullable()->after('airline');
            $table->string('departure_time', 5)->nullable()->after('departure_date');
            $table->string('arrival_time', 5)->nullable()->after('return_date');
            $table->boolean('arrival_next_day')->default(false)->after('arrival_time');
            $table->string('duration', 20)->nullable()->after('arrival_next_day');
            $table->string('baggage_allowance')->nullable()->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->dropColumn(['flight_number', 'departure_time', 'arrival_time', 'arrival_next_day', 'duration', 'baggage_allowance']);
        });
    }
};
