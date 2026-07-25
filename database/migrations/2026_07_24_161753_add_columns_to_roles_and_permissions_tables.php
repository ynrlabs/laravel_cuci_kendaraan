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
        Schema::table('roles', function (Blueprint $table) {
            $table->text('description')->nullable()->after('guard_name');
            $table->boolean('is_default')->default(false)->after('description');
            $table->boolean('is_active')->default(true)->after('is_default');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('group')->nullable()->after('guard_name');
            $table->text('description')->nullable()->after('group');
            $table->boolean('is_dangerous')->default(false)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_default', 'is_active']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['group', 'description', 'is_dangerous']);
        });
    }
};