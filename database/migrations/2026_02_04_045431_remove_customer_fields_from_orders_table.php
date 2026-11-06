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
            $table->dropColumn([
                'name',
                'email',
                'phone',
                'country',
                'post_code',
                'address_1',
                'address_2',
                'remarks'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('name')->after('tracking_status_id');
            $table->string('email')->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('country')->nullable()->after('phone');
            $table->string('post_code')->nullable()->after('country');
            $table->string('address_1')->nullable()->after('post_code');
            $table->string('address_2')->nullable()->after('address_1');
            $table->text('remarks')->nullable()->after('address_2');
        });
    }
};
