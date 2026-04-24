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
        Schema::table('order_customer_details', function (Blueprint $table) {
            $table->dropColumn([
                'city_1',
                'state_1',
                'post_code_1',
                'country',
                'city_2',
                'state_2',
                'post_code_2',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_customer_details', function (Blueprint $table) {
            $table->string('city_1')->nullable()->after('phone');
            $table->string('state_1')->nullable()->after('city_1');
            $table->string('post_code_1')->nullable()->after('state_1');
            $table->string('country')->nullable()->after('post_code_1');
            $table->string('city_2')->nullable()->after('address_2');
            $table->string('state_2')->nullable()->after('city_2');
            $table->string('post_code_2')->nullable()->after('state_2');
        });
    }
};
