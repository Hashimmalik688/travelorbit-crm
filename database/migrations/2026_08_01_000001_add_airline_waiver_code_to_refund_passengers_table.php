<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_passengers', function (Blueprint $table) {
            $table->string('airline_waiver_code')->nullable()->after('airline_locator');
        });
    }

    public function down(): void
    {
        Schema::table('refund_passengers', function (Blueprint $table) {
            $table->dropColumn('airline_waiver_code');
        });
    }
};
