<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirrors the ticket-order form consultants used to fill by hand
        // (see the "Ticket Order Form" template) and email to whichever
        // consolidator is issuing the tickets — "requested_by" here plays
        // the role the mock-up calls "Consultant" and we call "Agent".
        Schema::create('ticket_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('ref_number')->nullable();
            $table->string('issued_to'); // consolidator name, e.g. "Crystal Travel" — free text, not a stored vendor list
            $table->decimal('sold_adult', 10, 2)->default(0);
            $table->decimal('sold_child', 10, 2)->default(0);
            $table->decimal('sold_infant', 10, 2)->default(0);
            $table->decimal('cost_adult', 10, 2)->default(0);
            $table->decimal('cost_child', 10, 2)->default(0);
            $table->decimal('cost_infant', 10, 2)->default(0);
            $table->decimal('safi_charges', 10, 2)->default(0);
            $table->decimal('payment_amount', 10, 2)->default(0);
            $table->date('clearance_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_order_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('passenger_id')->nullable()->constrained('booking_passengers')->nullOnDelete();
            $table->string('name')->nullable(); // snapshot in case the passenger record changes later
            $table->date('date_of_birth')->nullable();
            $table->string('passport_number')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ticket_order_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flight_detail_id')->nullable()->constrained('booking_flight_details')->nullOnDelete();
            $table->string('locator')->nullable();
            $table->string('folder')->nullable();
            $table->string('type')->default('Console');
            $table->string('booked_in')->nullable(); // GDS, e.g. "ET NDC" / "Amadeus CTR"
            $table->string('issue_from')->nullable(); // consolidator/vendor
            $table->string('airline', 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_order_segments');
        Schema::dropIfExists('ticket_order_passengers');
        Schema::dropIfExists('ticket_orders');
    }
};
