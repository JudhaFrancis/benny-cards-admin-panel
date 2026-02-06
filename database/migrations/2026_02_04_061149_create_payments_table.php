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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->dateTime('payment_date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'upi', 'net_banking', 'qr_code', 'bank_transfer', 'cheque', 'wallet'])->default('cash');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->enum('payment_gateway', ['razorpay', 'stripe', 'paypal', 'phonepe', 'paytm', 'manual'])->default('manual');
            $table->json('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('payment_number');
            $table->index('payment_status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
