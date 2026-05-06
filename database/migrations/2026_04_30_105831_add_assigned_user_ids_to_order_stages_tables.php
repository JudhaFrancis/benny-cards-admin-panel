<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_client_information', function (Blueprint $table) {
            $table->json('assigned_user_ids')->default('[1, 2]')->after('status')->comment('Default: Super Admin (1), Admin (2). Source: order_details->order_taken_by');
        });

        Schema::table('order_designing', function (Blueprint $table) {
            $table->json('assigned_user_ids')->default('[1, 2]')->after('status')->comment('Default: Super Admin (1), Admin (2). Source: work_assign->assigned_to');
        });

        Schema::table('order_printing', function (Blueprint $table) {
            $table->json('assigned_user_ids')->default('[1, 2]')->after('status')->comment('Default: Super Admin (1), Admin (2). Source: printing_status->assigned_to');
        });

        Schema::table('order_packaging', function (Blueprint $table) {
            $table->json('assigned_user_ids')->default('[1, 2]')->after('status')->comment('Default: Super Admin (1), Admin (2). Source: packaging_status->packed_by');
        });

        Schema::table('order_dispatch_delivery', function (Blueprint $table) {
            $table->json('assigned_user_ids')->default('[1, 2]')->after('status')->comment('Default: Super Admin (1), Admin (2). Source: dispatch_mode->signature_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'order_client_information',
            'order_designing',
            'order_printing',
            'order_packaging',
            'order_dispatch_delivery'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('assigned_user_ids');
            });
        }
    }
};
