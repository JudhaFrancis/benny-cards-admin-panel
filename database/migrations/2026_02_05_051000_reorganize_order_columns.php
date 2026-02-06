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
        // Drop tracking fields from orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Drop tracking_id if it exists
                if (Schema::hasColumn('orders', 'tracking_id')) {
                    $table->dropColumn('tracking_id');
                }

                // Drop tracking_status_id if it exists (no foreign key to drop)
                if (Schema::hasColumn('orders', 'tracking_status_id')) {
                    $table->dropColumn('tracking_status_id');
                }
            });

            // Reorder columns in orders table by dropping and recreating
            // Note: MySQL doesn't support direct column reordering, so we need to recreate them
            Schema::table('orders', function (Blueprint $table) {
                // Drop existing columns (no foreign keys to drop as they may not exist)
                if (Schema::hasColumn('orders', 'net_amount')) {
                    $table->dropColumn('net_amount');
                }
                if (Schema::hasColumn('orders', 'coupons_id')) {
                    $table->dropColumn('coupons_id');
                }
                if (Schema::hasColumn('orders', 'discount')) {
                    $table->dropColumn('discount');
                }
                if (Schema::hasColumn('orders', 'total_amount')) {
                    $table->dropColumn('total_amount');
                }
                if (Schema::hasColumn('orders', 'balance_due')) {
                    $table->dropColumn('balance_due');
                }
            });

            // Add columns back in the correct order (after total_quantity)
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('net_amount', 10, 2)->default(0)->after('total_quantity');
                $table->unsignedBigInteger('coupons_id')->nullable()->after('net_amount');
                $table->decimal('discount', 10, 2)->default(0)->after('coupons_id');
                $table->decimal('total_amount', 10, 2)->default(0)->after('discount');
                $table->decimal('balance_due', 10, 2)->default(0)->after('total_amount');
            });
        }

        // Reorder discount_amount in order_items table
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                // Drop discount_amount
                if (Schema::hasColumn('order_items', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
            });

            // Add it back after unit_price
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('unit_price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add tracking fields back to orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('tracking_id')->nullable();
                $table->foreignId('tracking_status_id')->nullable()->constrained('tracking_status')->nullOnDelete();
            });
        }

        // Note: Column order cannot be easily reversed in down migration
        // The columns will still exist, just not in the original order
    }
};
