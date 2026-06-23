<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->decimal('cost_per_pax', 10, 2)->nullable()->after('contact_number');
            $table->decimal('sold_per_pax', 10, 2)->nullable()->after('cost_per_pax');
        });
    }

    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropColumn(['cost_per_pax', 'sold_per_pax']);
        });
    }
};
