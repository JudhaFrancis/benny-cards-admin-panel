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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('id');
            $table->string('facebook_url')->nullable()->after('email');
            $table->string('instagram_url')->nullable()->after('facebook_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'facebook_url', 'instagram_url']);
        });
    }
};
