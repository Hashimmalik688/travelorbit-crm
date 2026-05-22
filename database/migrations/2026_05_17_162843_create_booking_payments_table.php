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
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->enum('payment_type', ['full', 'awaiting', 'payment_plan', 'dnpl']);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('balance_remaining', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('installment_period', ['none', '30_days', '2_months'])->default('none');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->boolean('is_deposit_nonrefundable')->default(true);
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'stripe', 'klarna', 'card'])->default('cash');
            $table->boolean('invoice_generated')->default(false);
            $table->timestamp('invoice_generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
