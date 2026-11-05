<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enquiries record the branch picked in the Create Enquiry form (order_enquiries.branch_id).
 * On approval it is copied to orders.branch_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_enquiries', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('user_id')
                ->constrained('branches')->nullOnDelete();
        });

        // Enquiries already converted to an order take that order's branch
        DB::statement('
            UPDATE order_enquiries e
            JOIN orders o ON o.id = e.converted_order_id
            SET e.branch_id = o.branch_id
            WHERE e.branch_id IS NULL AND o.branch_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('order_enquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
