<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Self-referencing link for the Date Change flow: when set, this
            // booking was created as a date change of the referenced booking.
            // Nullable/nullOnDelete so deleting an original booking never
            // blocks on — or silently deletes — the date-change booking it spawned.
            $table->foreignId('original_booking_id')->nullable()->after('id')
                ->constrained('bookings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('original_booking_id');
        });
    }
};
