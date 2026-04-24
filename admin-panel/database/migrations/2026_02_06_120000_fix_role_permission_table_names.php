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
        // Rename model_has_roles to role_user
        if (Schema::hasTable('model_has_roles') && !Schema::hasTable('role_user')) {
            Schema::rename('model_has_roles', 'role_user');
        }

        // Rename role_has_permissions to role_has_permission
        if (Schema::hasTable('role_has_permissions') && !Schema::hasTable('role_has_permission')) {
            Schema::rename('role_has_permissions', 'role_has_permission');
        }

        // Rename model_has_permissions to user_has_permission
        if (Schema::hasTable('model_has_permissions') && !Schema::hasTable('user_has_permission')) {
            Schema::rename('model_has_permissions', 'user_has_permission');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('role_user')) {
            Schema::rename('role_user', 'model_has_roles');
        }

        if (Schema::hasTable('role_has_permission')) {
            Schema::rename('role_has_permission', 'role_has_permissions');
        }

        if (Schema::hasTable('user_has_permission')) {
            Schema::rename('user_has_permission', 'model_has_permissions');
        }
    }
};
