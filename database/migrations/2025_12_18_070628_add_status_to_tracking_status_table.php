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
        Schema::table('tracking_status', function (Blueprint $table) {
            // name -> title
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `tracking_status` CHANGE `name` `title` VARCHAR(255) NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracking_status', function (Blueprint $table) {
            // title -> name
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `tracking_status` CHANGE `title` `name` VARCHAR(255) NOT NULL");
        });
    }
};
