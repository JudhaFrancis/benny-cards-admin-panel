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
        Schema::table('banners', function (Blueprint $table) {
            $table->softDeletes();
            $table->unsignedBigInteger('added_by')->nullable()->after('status');
            $table->unsignedBigInteger('modified_by')->nullable()->after('added_by');

            $table->foreign('added_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['added_by']);
            $table->dropForeign(['modified_by']);
            $table->dropColumn(['deleted_at', 'added_by', 'modified_by']);
        });
    }
};
