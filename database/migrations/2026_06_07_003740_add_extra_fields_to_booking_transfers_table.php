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
        Schema::table('booking_transfers', function (Blueprint $table) {
            $table->string('vehicle_type')->nullable()->after('route');
            $table->string('supplier')->nullable()->after('vehicle_type');
            $table->decimal('actual_cost', 10, 2)->nullable()->after('supplier');
            $table->decimal('selling_price', 10, 2)->nullable()->after('actual_cost');
            $table->string('status')->default('confirmed')->after('selling_price');
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('booking_transfers', function (Blueprint $table) {
            $table->dropColumn(['vehicle_type', 'supplier', 'actual_cost', 'selling_price', 'status', 'notes']);
        });
    }
};
