<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderIds = Order::pluck('id')->toArray();
        $adminId = User::where('role', 'admin')->first()?->id ?: 1;

        if (empty($orderIds)) {
            return;
        }

        $methods = ['cash', 'card', 'upi', 'net_banking', 'qr_code'];
        $statuses = ['completed', 'pending', 'failed'];

        for ($i = 0; $i < 3; $i++) {
            Payment::create([
                'order_id' => $orderIds[array_rand($orderIds)],
                'payment_number' => 'PAY-' . strtoupper(uniqid()),
                'payment_date' => Carbon::now()->subDays(rand(0, 30)),
                'amount' => rand(50, 2000),
                'payment_method' => $methods[array_rand($methods)],
                'payment_status' => $statuses[array_rand($statuses)],
                'transaction_id' => 'TXN' . rand(100000, 999999),
                'notes' => 'Dummy payment record ' . ($i + 1),
                'added_by' => $adminId,
            ]);
        }
    }
}
