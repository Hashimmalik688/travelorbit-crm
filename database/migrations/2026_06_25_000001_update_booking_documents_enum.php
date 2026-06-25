<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE booking_documents MODIFY COLUMN document_type ENUM('e_ticket','hotel_voucher','passport','visa','itinerary','invoice','other') NOT NULL DEFAULT 'other'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE booking_documents MODIFY COLUMN document_type ENUM('passport','cnic','other') NOT NULL DEFAULT 'other'");
    }
};
