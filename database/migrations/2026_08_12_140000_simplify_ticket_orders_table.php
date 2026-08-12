<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cost-only pricing (no Sold For / Margin), and ATOL/SAFI need their
        // own flags so the email can label the charge correctly and hide it
        // entirely when neither applies — see emails/ticket-order.blade.php.
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->boolean('atol')->default(false)->after('cost_infant');
            $table->boolean('safi')->default(false)->after('atol');
        });

        // The raw GDS itinerary lines pasted under each segment on the old
        // hand-filled form (see the "AVTVWL ... 2 ET 729 V 05AUG ..." block
        // in the original template) — sourced from booking_flight_details.pnr.
        Schema::table('ticket_order_segments', function (Blueprint $table) {
            $table->text('pnr')->nullable()->after('airline');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->dropColumn(['atol', 'safi']);
        });
        Schema::table('ticket_order_segments', function (Blueprint $table) {
            $table->dropColumn('pnr');
        });
    }
};
