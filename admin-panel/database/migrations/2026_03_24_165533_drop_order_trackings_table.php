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
        Schema::dropIfExists('order_trackings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('order_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->json('job_details')->nullable();
            $table->json('client_info')->nullable();
            $table->json('card_specs')->nullable();
            $table->json('work_assign')->nullable();
            $table->json('design_print')->nullable();
            $table->json('printing_status')->nullable();
            $table->json('packaging_logistics')->nullable();
            $table->json('packaging_status')->nullable();
            $table->json('delivery_location')->nullable();
            $table->json('dispatch_mode')->nullable();
            $table->json('dispatch_details')->nullable();
            $table->json('payment_info')->nullable();
            $table->timestamps();
        });
    }
};
