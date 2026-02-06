<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('order_customer_details', 'post_code') && !Schema::hasColumn('order_customer_details', 'post_code_1')) {
            DB::statement('ALTER TABLE order_customer_details CHANGE post_code post_code_1 VARCHAR(255)');
        }

        Schema::table('order_customer_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_customer_details', 'city_1')) {
                $table->string('city_1')->nullable()->after('name');
            }
            if (!Schema::hasColumn('order_customer_details', 'state_1')) {
                $table->string('state_1')->nullable()->after('city_1');
            }
            if (!Schema::hasColumn('order_customer_details', 'city_2')) {
                $table->string('city_2')->nullable()->after('address_2');
            }
            if (!Schema::hasColumn('order_customer_details', 'state_2')) {
                $table->string('state_2')->nullable()->after('city_2');
            }
            if (!Schema::hasColumn('order_customer_details', 'post_code_2')) {
                $table->string('post_code_2')->nullable()->after('state_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_customer_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_customer_details', 'post_code_1')) {
                DB::statement('ALTER TABLE order_customer_details CHANGE post_code_1 post_code VARCHAR(255)');
            }
            $table->dropColumn(['city_1', 'state_1', 'city_2', 'state_2', 'post_code_2']);
        });
    }
};
