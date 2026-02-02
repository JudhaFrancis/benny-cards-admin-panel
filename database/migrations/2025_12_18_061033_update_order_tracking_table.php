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
        Schema::table('order_tracking', function (Blueprint $table) {
            // remarks -> tracking_details
            // use raw SQL for older MariaDB/MySQL compatibility if renameColumn fails
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `order_tracking` CHANGE `remarks` `tracking_details` TEXT NULL");

            // tracking_date remove
            $table->dropColumn('tracking_date');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            // tracking_details -> remarks
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `order_tracking` CHANGE `tracking_details` `remarks` TEXT NULL");
            $table->dateTime('tracking_date')->nullable();


        });
    }
};
