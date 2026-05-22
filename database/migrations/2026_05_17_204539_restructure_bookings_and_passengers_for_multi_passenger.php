<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Remove flight and pricing columns from bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'route', 'airline', 'flight_number', 'departure_airport',
                'arrival_airport', 'via_airport', 'baggage_allowance',
                'seat_preference', 'special_requests', 'ticket_class',
                'departure_date', 'return_date', 'cost_price', 'sale_price',
                'service_fee', 'currency', 'margin',
            ]);
        });

        // 2. Add flight and pricing columns to booking_passengers
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->string('airline')->nullable()->after('date_of_birth');
            $table->string('flight_number')->nullable()->after('airline');
            $table->string('departure_airport')->nullable()->after('flight_number');
            $table->string('arrival_airport')->nullable()->after('departure_airport');
            $table->string('via_airport')->nullable()->after('arrival_airport');
            $table->string('ticket_class')->default('economy')->after('via_airport');
            $table->date('departure_date')->nullable()->after('ticket_class');
            $table->date('return_date')->nullable()->after('departure_date');
            $table->string('baggage_allowance')->nullable()->after('return_date');
            $table->string('seat_preference')->default('no_preference')->after('baggage_allowance');
            $table->text('special_requests')->nullable()->after('seat_preference');
            $table->decimal('cost_price', 10, 2)->default(0)->after('special_requests');
            $table->decimal('sale_price', 10, 2)->default(0)->after('cost_price');
            $table->decimal('service_fee', 10, 2)->default(0)->after('sale_price');
            $table->string('currency', 3)->default('GBP')->after('service_fee');
            $table->string('passport_country_code', 3)->nullable()->after('nationality');
            $table->string('passport_issuing_country')->nullable()->after('passport_country_code');
        });

        DB::statement("ALTER TABLE booking_passengers ADD CONSTRAINT booking_passengers_ticket_class_check CHECK (ticket_class IN ('economy', 'business', 'first'))");
        DB::statement("ALTER TABLE booking_passengers ADD CONSTRAINT booking_passengers_seat_preference_check CHECK (seat_preference IN ('window', 'aisle', 'no_preference'))");
    }

    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropColumn([
                'airline', 'flight_number', 'departure_airport', 'arrival_airport',
                'via_airport', 'ticket_class', 'departure_date', 'return_date',
                'baggage_allowance', 'seat_preference', 'special_requests',
                'cost_price', 'sale_price', 'service_fee', 'currency',
                'passport_country_code', 'passport_issuing_country',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('route')->nullable();
            $table->string('airline')->nullable();
            $table->string('flight_number')->nullable();
            $table->string('departure_airport')->nullable();
            $table->string('arrival_airport')->nullable();
            $table->string('via_airport')->nullable();
            $table->string('baggage_allowance')->nullable();
            $table->enum('seat_preference', ['window', 'aisle', 'no_preference'])->default('no_preference');
            $table->text('special_requests')->nullable();
            $table->enum('ticket_class', ['economy', 'business', 'first'])->default('economy');
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->string('currency', 3)->default('GBP');
            $table->decimal('margin', 10, 2)->default(0);
        });
    }
};
