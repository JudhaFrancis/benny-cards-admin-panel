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
        if (!Schema::hasTable('order_enquiry_activities')) {
            Schema::create('order_enquiry_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enquiry_id')->index();
                $table->string('type', 50)->default('note'); // note, call, whatsapp, quote, status_change, converted
                $table->string('from_status', 50)->nullable();
                $table->string('to_status', 50)->nullable();
                $table->decimal('quoted_amount', 10, 2)->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('enquiry_id')->references('id')->on('order_enquiries')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_enquiry_activities');
    }
};
