<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            // Drop flight and pricing fields (moved to booking_flight_details/flight_costs)
            $table->dropColumn([
                'airline', 'flight_number', 'departure_airport', 'arrival_airport',
                'via_airport', 'ticket_class', 'departure_date', 'return_date',
                'baggage_allowance', 'seat_preference', 'special_requests',
                'cost_price', 'sale_price', 'service_fee', 'currency',
            ]);

            // Drop gender (no longer needed)
            $table->dropColumn('gender');

            // Rename full_name → we're splitting it, but keep old data
            // Add new columns
            $table->string('first_name')->nullable()->after('full_name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('ticket_number')->nullable()->after('nationality');
            $table->string('passenger_status_label')->nullable()->after('ticket_number');
        });
    }

    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'ticket_number', 'passenger_status_label']);

            $table->enum('gender', ['male', 'female'])->nullable()->after('title');
            $table->string('airline')->nullable();
            $table->string('flight_number')->nullable();
            $table->string('departure_airport')->nullable();
            $table->string('arrival_airport')->nullable();
            $table->string('via_airport')->nullable();
            $table->string('ticket_class')->default('economy');
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->string('baggage_allowance')->nullable();
            $table->string('seat_preference')->default('no_preference');
            $table->text('special_requests')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->string('currency', 3)->default('GBP');
        });
    }
};
