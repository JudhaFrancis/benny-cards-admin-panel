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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }

            // Note: 'type' column already exists from migration 2025_11_19_114526
            // We will use 'card' for Invitation Card and 'gift' for Gifts as per existing schema.

            if (!Schema::hasColumn('products', 'added_by')) {
                $table->unsignedBigInteger('added_by')->nullable()->after('brand_id');
                $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL');
            }

            if (!Schema::hasColumn('products', 'modified_by')) {
                $table->unsignedBigInteger('modified_by')->nullable()->after('added_by');
                $table->foreign('modified_by')->references('id')->on('users')->onDelete('SET NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['added_by']);
            $table->dropForeign(['modified_by']);
            $table->dropColumn(['deleted_at', 'added_by', 'modified_by']);
        });
    }
};
