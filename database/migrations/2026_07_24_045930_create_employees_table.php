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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('position'); // cashier, washer, supervisor
            $table->string('phone');
            $table->date('hire_date');
            $table->string('salary_type'); // monthly, per_wash, hourly
            $table->decimal('base_salary', 12, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->string('avatar')->nullable();
            $table->json('skills')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
