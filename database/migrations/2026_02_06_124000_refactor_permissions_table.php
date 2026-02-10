<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Truncate table to avoid foreign key constraints on existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('permissions', function (Blueprint $table) {
            // Drop Spatie specific columns
            $table->dropUnique('permissions_name_guard_name_unique');
            $table->dropColumn(['name', 'guard_name']);

            // Add Action FK
            $table->foreignId('action_id')->constrained('actions')->onDelete('cascade');

            // Add new Unique constraint
            $table->unique(['module_id', 'action_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['action_id']);
            $table->dropUnique(['module_id', 'action_id']);
            $table->dropColumn('action_id');

            $table->string('name');
            $table->string('guard_name');
            $table->unique(['name', 'guard_name']);
        });
    }
};
