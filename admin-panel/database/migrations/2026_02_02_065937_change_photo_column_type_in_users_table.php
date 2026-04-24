<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Use raw SQL for older MariaDB/MySQL compatibility if change() fails
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` MODIFY `photo` LONGTEXT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `users` MODIFY `photo` VARCHAR(191) NULL");
        });
    }
};
