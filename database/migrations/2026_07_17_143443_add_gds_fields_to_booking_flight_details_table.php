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
            $table->string('rez_class', 10)->nullable()->after('cabin');
            $table->string('fare_basis', 20)->nullable()->after('rez_class');
            $table->string('nvb', 20)->nullable()->after('fare_basis');
            $table->string('nva', 20)->nullable()->after('nvb');
            $table->string('seat', 40)->nullable()->after('duration');
            $table->string('dep_terminal', 10)->nullable()->after('departure_time');
            $table->string('arr_terminal', 10)->nullable()->after('arrival_time');
            $table->string('ticket_status', 10)->nullable()->after('reservation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->dropColumn(['rez_class', 'fare_basis', 'nvb', 'nva', 'seat', 'dep_terminal', 'arr_terminal', 'ticket_status']);
        });
    }
};
