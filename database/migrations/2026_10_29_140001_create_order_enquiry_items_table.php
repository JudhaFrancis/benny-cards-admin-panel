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
        if (!Schema::hasTable('order_enquiry_items')) {
            Schema::create('order_enquiry_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enquiry_id')->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('product_name', 191);
                $table->string('product_image', 255)->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0.00);
                $table->decimal('total_price', 10, 2)->default(0.00);
                $table->text('specifications')->nullable();
                $table->timestamps();

                $table->foreign('enquiry_id')->references('id')->on('order_enquiries')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_enquiry_items');
    }
};
