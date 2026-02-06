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
        // Add new column as nullable first (only if it doesn't exist)
        if (!Schema::hasColumn('orders', 'tracking_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('tracking_number')->nullable()->after('order_number');
            });
        }

        // Copy data from tracking_id to tracking_number
        DB::statement('UPDATE orders SET tracking_number = tracking_id WHERE tracking_id IS NOT NULL');

        // Drop old column and make new one unique
        if (Schema::hasColumn('orders', 'tracking_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['tracking_id']);
                $table->dropColumn('tracking_id');
            });
        }

        // Make tracking_number unique
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_id')->unique()->after('order_number');
        });

        DB::statement('UPDATE orders SET tracking_id = tracking_number');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['tracking_number']);
            $table->dropColumn('tracking_number');
        });
    }
};
