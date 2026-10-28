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
        $tables = [
            'order_designing',
            'order_printing',
            'order_packaging',
            'order_dispatch_delivery',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'notification_status')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('notification_status', 20)->default('unread')->after('status');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'order_designing',
            'order_printing',
            'order_packaging',
            'order_dispatch_delivery',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'notification_status')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('notification_status');
                });
            }
        }
    }
};
