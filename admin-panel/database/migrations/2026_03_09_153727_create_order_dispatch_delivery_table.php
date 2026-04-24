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
        Schema::dropIfExists('order_dispatch_delivery');
        Schema::create('order_dispatch_delivery', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->json('delivery_location')->nullable();
            $table->json('dispatch_mode')->nullable();
            $table->json('dispatch_details')->nullable();
            $table->string('status')->default('Pending');
            $table->json('audit_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_dispatch_delivery');
    }
};
