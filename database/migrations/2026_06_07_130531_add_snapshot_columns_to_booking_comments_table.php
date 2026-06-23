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
        Schema::table('booking_comments', function (Blueprint $table) {
            $table->string('agent_name')->nullable()->after('user_id');
            $table->string('avatar_url', 1000)->nullable()->after('agent_name');
        });
    }

    public function down(): void
    {
        Schema::table('booking_comments', function (Blueprint $table) {
            $table->dropColumn(['agent_name', 'avatar_url']);
        });
    }
};
