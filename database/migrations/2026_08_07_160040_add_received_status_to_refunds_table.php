<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'received' to refunds.status — the stage between the ticket provider's
 * refund landing with Travel Orbit and it actually being paid out to the
 * customer (see PaymentChargeRequest::advanceLinkedRefund). 'under_review' and
 * 'approved' are dropped from the allowed set: that separate Refund-level
 * review workflow was replaced by queuing the receipt/payout as ordinary
 * Charge Requests, and no existing row uses either value.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE refunds DROP CONSTRAINT IF EXISTS refunds_status_check');
        DB::statement("ALTER TABLE refunds ADD CONSTRAINT refunds_status_check CHECK (status IN ('requested', 'received', 'rejected', 'processed'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE refunds DROP CONSTRAINT IF EXISTS refunds_status_check');
        DB::statement("ALTER TABLE refunds ADD CONSTRAINT refunds_status_check CHECK (status IN ('requested', 'under_review', 'approved', 'rejected', 'processed'))");
    }
};
