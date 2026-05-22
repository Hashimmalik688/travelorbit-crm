<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_flight_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('pnr')->nullable();
            $table->string('airline', 2)->nullable();
            $table->string('vendor')->nullable();
            $table->string('gds')->nullable();
            $table->dateTime('ticket_issue_limit')->nullable();
            $table->boolean('atol')->default(false);
            $table->boolean('safi')->default(false);
            $table->string('city_code', 5)->nullable();
            $table->string('departure_airport')->nullable();
            $table->string('arrival_airport')->nullable();
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_flight_details');
    }
};
