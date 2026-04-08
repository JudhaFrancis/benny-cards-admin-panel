<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure existing data is migrated before dropping columns
        $payments = DB::table('payments')->get();
        foreach ($payments as $payment) {
            $details = [];
            // If payment_details already has data, we append to it (unlikely in this context but safe)
            if ($payment->payment_details) {
                $details = json_decode($payment->payment_details, true);
                if (!is_array($details)) $details = [];
            }

            // Map old columns to the first entry in the new JSON array
            if (empty($details)) {
                $details[] = [
                    'method' => $payment->payment_method ?? 'cash',
                    'transaction_id' => $payment->transaction_id ?? '',
                    'amount' => $payment->amount ?? 0,
                ];
            }

            DB::table('payments')->where('id', $payment->id)->update([
                'payment_details' => json_encode($details)
            ]);
        }

        Schema::table('payments', function (Blueprint $table) {
            // 2. Drop old columns
            $table->dropColumn(['payment_method', 'transaction_id', 'payment_gateway', 'amount']);
            
            // 3. Ensure payment_details is text/longText for maximum compatibility if needed
            // It's already 'json' in the original migration, but user requested 'text'.
            $table->text('payment_details')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'card', 'upi', 'net_banking', 'qr_code', 'bank_transfer', 'cheque', 'wallet'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->enum('payment_gateway', ['razorpay', 'stripe', 'paypal', 'phonepe', 'paytm', 'manual'])->default('manual');
            $table->decimal('amount', 10, 2)->default(0);
            $table->json('payment_details')->nullable()->change();
        });

        // Optional: Re-populate old columns from JSON if rolling back
        $payments = DB::table('payments')->get();
        foreach ($payments as $payment) {
            $details = json_decode($payment->payment_details, true);
            if (is_array($details) && !empty($details)) {
                $first = $details[0];
                DB::table('payments')->where('id', $payment->id)->update([
                    'payment_method' => $first['method'] ?? 'cash',
                    'transaction_id' => $first['transaction_id'] ?? '',
                    'amount' => $first['amount'] ?? 0,
                ]);
            }
        }
    }
};
