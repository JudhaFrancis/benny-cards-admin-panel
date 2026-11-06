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
        // Drop foreign keys
        $fks = ['orders_tracking_status_id_foreign', 'orders_coupons_id_foreign'];
        foreach ($fks as $fk) {
            try {
                DB::statement("ALTER TABLE orders DROP FOREIGN KEY {$fk}");
            } catch (\Throwable $e) {
                // Ignore if FK doesn't exist
            }
        }

        // Drop columns using Schema if possible to be cleaner, but DB::statement if we want to be raw.
        // Let's us DB::statement to avoid any Doctrine trigger.
        // We build the DROP list dynamically based on what exists to avoid errors?
        // Or just let it fail if columns missing?
        // User asked to drop them. 

        $columns = [
            'tracking_id',
            'tracking_status_id',
            'net_amount',
            'coupons_id',
            'discount',
            'total_amount'
        ];

        $dropList = [];
        foreach ($columns as $col) {
            if (Schema::hasColumn('orders', $col)) {
                $dropList[] = "DROP COLUMN `{$col}`";
            }
        }

        if (!empty($dropList)) {
            $sql = "ALTER TABLE orders " . implode(', ', $dropList);
            DB::statement($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_id')->nullable();
            $table->unsignedBigInteger('tracking_status_id')->nullable();
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('coupons_id')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
        });
    }
};
