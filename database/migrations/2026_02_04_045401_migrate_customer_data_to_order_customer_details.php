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
        // Copy customer data from orders to order_customer_details
        DB::table('orders')->orderBy('id')->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                DB::table('order_customer_details')->insert([
                    'order_id' => $order->id,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                    'country' => $order->country,
                    'post_code' => $order->post_code,
                    'address_1' => $order->address_1,
                    'address_2' => $order->address_2,
                    'remarks' => $order->remarks,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Copy data back from order_customer_details to orders
        DB::table('order_customer_details')->orderBy('id')->chunk(100, function ($customerDetails) {
            foreach ($customerDetails as $detail) {
                DB::table('orders')
                    ->where('id', $detail->order_id)
                    ->update([
                        'name' => $detail->name,
                        'email' => $detail->email,
                        'phone' => $detail->phone,
                        'country' => $detail->country,
                        'post_code' => $detail->post_code,
                        'address_1' => $detail->address_1,
                        'address_2' => $detail->address_2,
                        'remarks' => $detail->remarks,
                    ]);
            }
        });

        // Clear the order_customer_details table
        DB::table('order_customer_details')->truncate();
    }
};