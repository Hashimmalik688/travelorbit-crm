<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('refund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('passenger_id')->nullable()->constrained('booking_passengers')->nullOnDelete();
            $table->string('e_ticket_number')->nullable();
            $table->string('gds_locator')->nullable();
            $table->string('airline_locator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_passengers');
    }
};
