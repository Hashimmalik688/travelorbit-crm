<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_hotels', function (Blueprint $table) {
            $table->dropColumn(['room_type', 'occupants']);
        });

        Schema::create('booking_hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_hotel_id')->constrained('booking_hotels')->cascadeOnDelete();
            $table->unsignedInteger('room_number')->default(1);
            $table->string('room_type')->nullable();
            $table->unsignedInteger('occupants')->default(1);
            $table->enum('meal_basis', ['room_only', 'breakfast', 'half_board', 'full_board', 'all_inclusive'])->default('room_only');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_hotel_rooms');

        Schema::table('booking_hotels', function (Blueprint $table) {
            $table->string('room_type')->nullable()->after('city');
            $table->unsignedInteger('occupants')->default(1)->after('booking_status');
        });
    }
};
