<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance tracking — ported from taurus-crm.
 *
 * One row per user per day. `login_time`/`logout_time` are the check-in/out
 * stamps; `working_hours` is auto-derived on save (see Attendance model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->dateTime('login_time')->nullable();
            $table->dateTime('logout_time')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint')->nullable();
            $table->string('device_name')->nullable();
            $table->string('status')->default('present'); // present | late | absent | leave
            $table->decimal('working_hours', 5, 1)->default(0);
            $table->boolean('auto_checkout')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
