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
            if (!Schema::hasColumn('orders', 'added_by')) {
                $table->foreignId('added_by')->nullable()->after('tracking_status_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'modified_by')) {
                $table->foreignId('modified_by')->nullable()->after('added_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'added_by')) {
                $table->dropForeign(['added_by']);
                $table->dropColumn('added_by');
            }
            if (Schema::hasColumn('orders', 'modified_by')) {
                $table->dropForeign(['modified_by']);
                $table->dropColumn('modified_by');
            }
        });
    }
};
