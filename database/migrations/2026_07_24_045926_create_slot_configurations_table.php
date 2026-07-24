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
        Schema::create('slot_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('day_of_week')->nullable(); // monday..sunday
            $table->time('slot_time');
            $table->integer('max_capacity');
            $table->integer('current_booked')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['branch_id', 'date', 'slot_time']);
            $table->unique(['branch_id', 'day_of_week', 'slot_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_configurations');
    }
};
