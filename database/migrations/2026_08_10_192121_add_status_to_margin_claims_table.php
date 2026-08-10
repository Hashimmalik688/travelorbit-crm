<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Margin claims are now queued as 'pending' the moment a refund is
        // requested (their own M&R Auth Queue entry, decoupled from the
        // refund request itself) and only count toward Agent Performance
        // once a manager releases them — see RefundAuthQueue::approveMargin.
        Schema::table('margin_claims', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('reason'); // pending | released | held
        });
    }

    public function down(): void
    {
        Schema::table('margin_claims', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
