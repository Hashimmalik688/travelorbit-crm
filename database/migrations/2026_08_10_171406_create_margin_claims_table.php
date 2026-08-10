<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors margin_deductions, but the other direction: the difference
        // between what Travel Orbit actually received back from a supplier
        // refund and what was paid on to the customer, kept as extra margin.
        // Created automatically when accounts approves a "Refund to Customer"
        // payout that claims margin (see PaymentChargeRequest::advanceLinkedRefund) —
        // not manually applied like a deduction, so there's no "removed by" side.
        Schema::create('margin_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('applied_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->date('claim_date');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margin_claims');
    }
};
