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
        Schema::table('orders', function (Blueprint $table) {
            // Try to drop foreign key if it exists
            try {
                $table->dropForeign(['tracking_status_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            // Drop columns that exist
            $columnsToDrop = [];
            if (Schema::hasColumn('orders', 'paid_amount'))
                $columnsToDrop[] = 'paid_amount';
            if (Schema::hasColumn('orders', 'payment_method'))
                $columnsToDrop[] = 'payment_method';
            if (Schema::hasColumn('orders', 'payment_status'))
                $columnsToDrop[] = 'payment_status';
            if (Schema::hasColumn('orders', 'tracking_status_id'))
                $columnsToDrop[] = 'tracking_status_id';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add columns back
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            $table->enum('payment_method', ['cash', 'qr_code', 'upi', 'card', 'net_banking'])->nullable()->after('paid_amount');
            $table->enum('payment_status', ['paid', 'due', 'unpaid'])->default('unpaid')->after('payment_method');
            $table->foreignId('tracking_status_id')->nullable()->after('status')->constrained('tracking_status')->nullOnDelete();
        });
    }
};
