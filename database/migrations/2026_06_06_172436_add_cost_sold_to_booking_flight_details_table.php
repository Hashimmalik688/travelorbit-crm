<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->nullable()->after('selling_price');
            $table->decimal('sold', 10, 2)->nullable()->after('cost');
        });
    }

    public function down(): void
    {
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->dropColumn(['sold', 'cost']);
        });
    }
};
