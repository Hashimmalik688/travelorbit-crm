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
        Schema::create('callcenter_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('callcenter_customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('source');
            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('infants')->default(0);
            $table->string('status')->default('new');
            $table->json('details')->nullable();
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->string('mis_number')->nullable();
            $table->string('last_disposition')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callcenter_inquiries');
    }
};
