<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_designing', function (Blueprint $table) {
            $table->string('sticker_image')->nullable()->after('design_print');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_designing', function (Blueprint $table) {
            $table->dropColumn('sticker_image');
        });
    }
};
