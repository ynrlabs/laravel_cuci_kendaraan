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
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('booking_code')->unique();
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignUuid('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignUuid('assigned_employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('vehicle_type'); // sedan, suv, mpv, hatchback, motor, truck
            $table->string('vehicle_color')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->json('vehicle_photos')->nullable();
            $table->date('booking_date');
            $table->time('slot_time');
            $table->string('status'); // pending_payment, confirmed, in_queue, washing, drying, completed, cancelled, no_show
            $table->string('payment_method'); // qris, gopay, ovo, dana, shopeepay, va_bca, va_bri, va_mandiri, va_bni, credit_card, cash
            $table->string('payment_status'); // unpaid, paid, refund_pending, refunded
            $table->string('payment_reference')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('notes')->nullable();
            $table->boolean('is_walk_in')->default(false);
            $table->tinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->json('review_photos')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->foreignUuid('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['booking_date', 'branch_id']);
            $table->index(['status']);
            $table->index(['customer_id']);
            $table->index(['booking_date', 'slot_time', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
