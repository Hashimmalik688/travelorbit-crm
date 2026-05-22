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
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->renameColumn('cnic_number', 'national_id_number');
            $table->enum('title', ['mr', 'mrs', 'ms', 'dr'])->nullable()->after('full_name');
            $table->enum('gender', ['male', 'female'])->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropColumn(['title', 'gender']);
            $table->renameColumn('national_id_number', 'cnic_number');
        });
    }
};
