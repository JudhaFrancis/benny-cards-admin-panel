<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderCustomerDetails;
use App\Models\OrderClientInformation;
use App\Models\OrderDesigning;
use App\Models\OrderPrinting;
use App\Models\OrderPackaging;
use App\Models\OrderDispatchDelivery;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            echo "No users found. Please seed users first.\n";
            return;
        }

        $userId = $user->id;
        $product = Product::first();
        if (!$product) {
            echo "No products found. Please seed products first.\n";
            return;
        }

        echo "Seeding 120 orders...\n";

        for ($i = 0; $i < 120; $i++) {
            DB::transaction(function () use ($i, $userId, $product) {
                // Determine Stage (20 cada)
                $stageIndex = floor($i / 20); // 0: ClientInfo, 1: Designing, 2: Printing, 3: Packaging, 4: Delivery (P), 5: Delivery (C)
                
                // Determine Payment Status (40 cada)
                $paymentIndex = floor($i / 40); // 0: Unpaid, 1: Due, 2: Paid

                // 1. Create Base Order
                $totalAmount = 5000.00; // Fixed total for easy calculation
                $order = Order::create([
                    'order_number' => 'SEED-ORD-' . Str::padLeft($i + 1, 4, '0'),
                    'order_date' => now()->subDays(rand(1, 30)),
                    'delivery_date' => now()->addDays(rand(1, 10)),
                    'user_id' => $userId,
                    'items_count' => 1,
                    'total_quantity' => 1,
                    'net_amount' => 5000.00,
                    'discount' => 0,
                    'extra_charges' => 0,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0, // Will update via payment
                    'balance_due' => $totalAmount,
                    'payment_status' => 'unpaid',
                    'status' => ($stageIndex == 5) ? 'completed' : 'pending',
                    'added_by' => $userId,
                    'modified_by' => $userId,
                ]);

                // 2. Add Order Item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->title,
                    'product_image' => $product->image,
                    'quantity' => 1,
                    'unit_price' => 5000.00,
                    'total_price' => 5000.00,
                ]);

                // 3. Add Customer Details
                OrderCustomerDetails::create([
                    'order_id' => $order->id,
                    'name' => 'Demo Customer ' . ($i + 1),
                    'phone' => '98765432' . Str::padLeft($i, 2, '0'),
                    'email' => 'customer' . ($i + 1) . '@example.com',
                    'address_1' => 'Street ' . ($i + 1),
                    'address_2' => 'Locality ' . ($i + 1),
                    'remarks' => 'Seeded data for testing.',
                ]);

                // 4. Handle Stages
                $this->createStages($order, $stageIndex, $userId);

                // 5. Handle Payments
                $this->createPayment($order, $paymentIndex, $userId);
            });
        }

        echo "Seeding completed successfully.\n";
    }

    private function createStages(Order $order, int $stageIndex, int $userId): void
    {
        // 0: ClientInfo(P)
        // 1: Designing(P) + ClientInfo(C)
        // 2: Printing(P) + Designing(C), ClientInfo(C)
        // 3: Packaging(P) + Printing(C), etc.
        // 4: Delivery(P) + Packaging(C), etc.
        // 5: Delivery(C) + All(C)

        // Stage 0: Client Information
        OrderClientInformation::create([
            'order_id' => $order->id,
            'status' => ($stageIndex > 0) ? 'Completed' : 'Process',
            'order_details' => [
                'order_taken_by' => 'Staff ' . rand(1, 5),
                'order_placed_in' => 'Online',
                'expected_delivery_date' => $order->delivery_date->format('Y-m-d')
            ],
            'modified_by' => $userId
        ]);

        // Stage 1+: Designing
        if ($stageIndex >= 1) {
            OrderDesigning::create([
                'order_id' => $order->id,
                'status' => ($stageIndex > 1) ? 'Completed' : 'Process',
                'work_assign' => [
                    'assigned_to' => 'Designer ' . rand(1, 3),
                    'assigned_date' => now()->subDays(1)->format('Y-m-d'),
                    'content_received' => true
                ],
                'modified_by' => $userId
            ]);
        }

        // Stage 2+: Printing
        if ($stageIndex >= 2) {
            OrderPrinting::create([
                'order_id' => $order->id,
                'status' => ($stageIndex > 2) ? 'Completed' : 'Process',
                'printing_status' => [
                    'assigned_to' => 'Printer Operator',
                    'assigned_date' => now()->format('Y-m-d')
                ],
                'modified_by' => $userId
            ]);
        }

        // Stage 3+: Packaging
        if ($stageIndex >= 3) {
            OrderPackaging::create([
                'order_id' => $order->id,
                'status' => ($stageIndex > 3) ? 'Completed' : 'Process',
                'packaging_logistics' => [
                    'crafted_by' => 'Packer ' . rand(1, 2),
                    'date' => now()->format('Y-m-d')
                ],
                'modified_by' => $userId
            ]);
        }

        // Stage 4+: Dispatch/Delivery
        if ($stageIndex >= 4) {
            OrderDispatchDelivery::create([
                'order_id' => $order->id,
                'status' => ($stageIndex > 4) ? 'Completed' : 'Process',
                'dispatch_mode' => [
                    'mode' => 'Courier',
                    'date' => now()->format('Y-m-d'),
                    'signature_name' => 'Recipient Name'
                ],
                'modified_by' => $userId
            ]);
        }
    }

    private function createPayment(Order $order, int $paymentIndex, int $userId): void
    {
        // 0: Unpaid (40 orders)
        // 1: Due (40 orders) -> 50% paid
        // 2: Paid (40 orders) -> 100% paid

        if ($paymentIndex == 0) {
            $order->updatePaymentStatus();
            return; // 0 paid
        }

        $paidAmount = ($paymentIndex == 1) ? ($order->total_amount / 2) : $order->total_amount;

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_number' => 'SEED-PAY-' . Str::uuid(),
            'payment_date' => now(),
            'payment_status' => 'completed',
            'payment_details' => [
                ['method' => 'upi', 'amount' => $paidAmount, 'transaction_id' => 'TXN-' . rand(1000, 9999)]
            ],
            'added_by' => $userId,
        ]);

        // Fix payment number
        $payment->update(['payment_number' => 'PAY' . $payment->id . '-' . date('dmY')]);

        // Sync order financial fields
        $order->updatePaymentStatus();
    }
}
