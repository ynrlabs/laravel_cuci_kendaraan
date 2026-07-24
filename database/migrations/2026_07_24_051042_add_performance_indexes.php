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
        // Performance-critical indexes from PRD §8.3
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['booking_date', 'branch_id'], 'idx_bookings_date_branch');
            $table->index(['status'], 'idx_bookings_status');
            $table->index(['customer_id'], 'idx_bookings_customer');
            $table->index(['booking_code'], 'idx_bookings_code');
            $table->index(['booking_date', 'slot_time', 'branch_id'], 'idx_bookings_slot');
        });

        Schema::table('slot_configurations', function (Blueprint $table) {
            $table->index(['branch_id', 'date'], 'idx_slot_config_branch_date');
        });

        Schema::table('service_pricings', function (Blueprint $table) {
            $table->index(['branch_id', 'service_id'], 'idx_service_pricing_branch');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['booking_id'], 'idx_payments_booking');
            $table->index(['gateway_payment_id'], 'idx_payments_gateway');
            $table->index(['status'], 'idx_payments_status');
        });

        Schema::table('notifications_log', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'idx_notifications_user_read');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id'], 'idx_activity_logs_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_date_branch');
            $table->dropIndex('idx_bookings_status');
            $table->dropIndex('idx_bookings_customer');
            $table->dropIndex('idx_bookings_code');
            $table->dropIndex('idx_bookings_slot');
        });

        Schema::table('slot_configurations', function (Blueprint $table) {
            $table->dropIndex('idx_slot_config_branch_date');
        });

        Schema::table('service_pricings', function (Blueprint $table) {
            $table->dropIndex('idx_service_pricing_branch');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_booking');
            $table->dropIndex('idx_payments_gateway');
            $table->dropIndex('idx_payments_status');
        });

        Schema::table('notifications_log', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_activity_logs_subject');
        });
    }
};
