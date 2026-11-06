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
            $table->foreignId('added_by')->nullable()->after('tracking_status_id')->constrained('users')->nullOnDelete();
            $table->foreignId('modified_by')->nullable()->after('added_by')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['added_by']);
            $table->dropForeign(['modified_by']);
            $table->dropColumn(['added_by', 'modified_by']);
        });
    }
};
