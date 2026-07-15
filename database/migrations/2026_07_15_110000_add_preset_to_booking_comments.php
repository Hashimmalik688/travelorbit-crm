<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_comments', function (Blueprint $table) {
            // Null = a plain comment with no preset heading (all existing rows).
            $table->string('preset')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('booking_comments', function (Blueprint $table) {
            $table->dropColumn('preset');
        });
    }
};
