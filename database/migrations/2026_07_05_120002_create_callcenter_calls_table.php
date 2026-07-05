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
        Schema::create('callcenter_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('callcenter_inquiries')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction')->default('inbound');
            $table->string('disposition')->nullable();
            $table->text('caller_comment')->nullable();
            $table->text('agent_comment')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->boolean('is_followup')->default(false);
            $table->timestamps();

            $table->index('disposition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callcenter_calls');
    }
};
