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
        // Remove invoice_id from payments table first (to avoid foreign key constraint issues)
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'invoice_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            });
        }

        // Now drop invoice_items table (due to foreign key to invoices)
        Schema::dropIfExists('invoice_items');

        // Drop invoices table
        Schema::dropIfExists('invoices');

        // Restore financial fields to orders table if they were removed
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'tracking_id')) {
                    $table->string('tracking_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'tracking_status_id')) {
                    $table->unsignedBigInteger('tracking_status_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'net_amount')) {
                    $table->decimal('net_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'coupons_id')) {
                    $table->unsignedBigInteger('coupons_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'discount')) {
                    $table->decimal('discount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'total_amount')) {
                    $table->decimal('total_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'balance_due')) {
                    $table->decimal('balance_due', 10, 2)->default(0);
                }
            });
        }

        // Ensure order_items has all necessary financial fields
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'quantity')) {
                    $table->integer('quantity')->default(1);
                }
                if (!Schema::hasColumn('order_items', 'unit_price')) {
                    $table->decimal('unit_price', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('order_items', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->default(0);
                }
                if (!Schema::hasColumn('order_items', 'total_price')) {
                    $table->decimal('total_price', 10, 2)->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is intentionally left empty as we don't want to recreate invoice tables
        // If you need to rollback, you would need to manually restore the invoice migrations
    }
};
