<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->string('passenger_type')->default('adult')->after('id');
        });
        DB::statement("ALTER TABLE booking_passengers ADD CONSTRAINT booking_passengers_passenger_type_check CHECK (passenger_type IN ('adult', 'youth', 'child', 'infant'))");
    }

    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropColumn('passenger_type');
        });
    }
};
