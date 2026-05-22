<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- bookings table ---
        Schema::table('bookings', function (Blueprint $table) {
            // Step 1: Lead Nature
            $table->string('lead_nature')->nullable()->after('lead_source');
            $table->boolean('is_returning_or_referral')->default(false)->after('lead_nature');
            $table->string('old_booking_reference')->nullable()->after('is_returning_or_referral');
            $table->date('last_payment_date')->nullable()->after('old_booking_reference');
            $table->date('last_issue_date')->nullable()->after('last_payment_date');

            // Activity log field — stores JSON array of activity entries
            $table->json('activity_log')->nullable()->after('notes');
        });

        // --- booking_passengers ---
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->string('frequent_flyer_number')->nullable()->after('ticket_number');
            $table->string('e_ticket_number')->nullable()->after('frequent_flyer_number');
            $table->string('ptc')->nullable()->after('passenger_status_label');
        });

        // --- booking_flight_details ---
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->string('folder_number')->nullable()->after('pnr');
            $table->string('locator')->nullable()->after('folder_number');
            $table->string('airline_locator')->nullable()->after('locator');
            $table->string('type_issuer')->nullable()->after('airline_locator');
            $table->string('reservation_status')->nullable()->after('type_issuer');
        });

        // --- booking_flight_costs ---
        Schema::table('booking_flight_costs', function (Blueprint $table) {
            $table->decimal('sold_price', 10, 2)->default(0)->after('quantity');
            // Add GBE (youth) type support via just cost_type = 'gbe' (existing enum covers strings)
        });

        // --- booking_payments ---
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->string('payment_mode_2')->nullable()->after('payment_mode');
            $table->decimal('installment_first_amount', 10, 2)->nullable()->after('installment_period');
            $table->boolean('debit_card_change')->default(false)->after('installment_first_amount');
            $table->decimal('cc_charges', 10, 2)->default(0)->after('debit_card_change');
            $table->decimal('margin_without_cc', 10, 2)->nullable()->after('cc_charges');
            $table->decimal('total_margin_amount', 10, 2)->nullable()->after('margin_without_cc');
        });

        // --- booking_payment_history (new table) ---
        Schema::create('booking_payment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->date('payment_date');
            $table->string('payment_method')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('receipt_number')->nullable();
            $table->timestamps();
        });

        // --- booking_activity_log (new table for detailed activity tracking) ---
        Schema::create('booking_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action');           // 'created', 'updated', 'comment', etc.
            $table->text('comment');              // mandatory comment
            $table->json('details')->nullable();  // optional extra data
            $table->timestamps();
        });

        // --- booking_comments: add metadata ---
        Schema::table('booking_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_comments', 'action')) {
                $table->string('action')->nullable()->after('comment');
            }
            if (!Schema::hasColumn('booking_comments', 'is_mandatory')) {
                $table->boolean('is_mandatory')->default(false)->after('action');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payment_history');
        Schema::dropIfExists('booking_activity_log');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['lead_nature', 'is_returning_or_referral', 'old_booking_reference', 'last_payment_date', 'last_issue_date', 'activity_log']);
        });
        Schema::table('booking_passengers', function (Blueprint $table) {
            $table->dropColumn(['frequent_flyer_number', 'e_ticket_number', 'ptc']);
        });
        Schema::table('booking_flight_details', function (Blueprint $table) {
            $table->dropColumn(['folder_number', 'locator', 'airline_locator', 'type_issuer', 'reservation_status']);
        });
        Schema::table('booking_flight_costs', function (Blueprint $table) {
            $table->dropColumn('sold_price');
        });
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_mode_2', 'installment_first_amount', 'debit_card_change', 'cc_charges', 'margin_without_cc', 'total_margin_amount']);
        });
        Schema::table('booking_comments', function (Blueprint $table) {
            $table->dropColumn(['action', 'is_mandatory']);
        });
    }
};
