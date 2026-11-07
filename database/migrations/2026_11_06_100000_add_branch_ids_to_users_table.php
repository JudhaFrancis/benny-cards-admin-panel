<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Branch access per user: users.branch_ids = JSON array of branch ids the user may see.
 * Access is read only from this column (never from the role), so changing a role does not change lists.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'branch_ids')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('branch_ids')->nullable()->after('role_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'branch_ids')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('branch_ids');
            });
        }
    }
};
