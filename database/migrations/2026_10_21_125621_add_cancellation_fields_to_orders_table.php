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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'cancelled_module')) {
                $table->string('cancelled_module')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'cancelled_reason')) {
                $table->text('cancelled_reason')->nullable()->after('cancelled_module');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'cancelled_reason')) {
                $table->dropColumn('cancelled_reason');
            }
            if (Schema::hasColumn('orders', 'cancelled_module')) {
                $table->dropColumn('cancelled_module');
            }
        });
    }
};
