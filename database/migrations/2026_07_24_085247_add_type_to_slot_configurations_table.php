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
        Schema::table('slot_configurations', function (Blueprint $table) {
            $table->string('type')->default('specific')->after('day_of_week'); // 'specific' or 'recurring'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slot_configurations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};