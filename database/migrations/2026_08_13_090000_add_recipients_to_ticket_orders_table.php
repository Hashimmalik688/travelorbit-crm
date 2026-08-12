<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Who it actually went to — set by TicketOrderService::createAndSend
        // after fallback/parsing, so this is what was really used, not just
        // what was typed in the form.
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->text('sent_to')->nullable()->after('sent_at');
            $table->text('sent_cc')->nullable()->after('sent_to');
            $table->text('sent_bcc')->nullable()->after('sent_cc');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->dropColumn(['sent_to', 'sent_cc', 'sent_bcc']);
        });
    }
};
