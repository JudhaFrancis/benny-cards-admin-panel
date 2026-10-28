<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename notifications to notification_logs
        if (Schema::hasTable('notifications') && !Schema::hasTable('notification_logs')) {
            Schema::rename('notifications', 'notification_logs');
        }

        // Rename sms_templates to notification_templates
        if (Schema::hasTable('sms_templates') && !Schema::hasTable('notification_templates')) {
            Schema::rename('sms_templates', 'notification_templates');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert notification_logs to notifications
        if (Schema::hasTable('notification_logs') && !Schema::hasTable('notifications')) {
            Schema::rename('notification_logs', 'notifications');
        }

        // Revert notification_templates to sms_templates
        if (Schema::hasTable('notification_templates') && !Schema::hasTable('sms_templates')) {
            Schema::rename('notification_templates', 'sms_templates');
        }
    }
};
