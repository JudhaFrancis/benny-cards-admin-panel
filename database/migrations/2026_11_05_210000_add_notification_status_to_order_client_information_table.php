<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026_10_22_170000 added notification_status to the other four stage tables but not to
 * order_client_information, while OrderTrackingService and WorkNotificationService use it there too.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_client_information', 'notification_status')) {
            Schema::table('order_client_information', function (Blueprint $table) {
                $table->string('notification_status', 20)->default('unread')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_client_information', 'notification_status')) {
            Schema::table('order_client_information', function (Blueprint $table) {
                $table->dropColumn('notification_status');
            });
        }
    }
};
