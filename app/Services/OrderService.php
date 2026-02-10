<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Get paginated orders with filters.
     */
    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails', 'payments', 'coupon', 'tracking'])
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where('order_number', 'like', "%{$filters['search']}%")
                    ->orWhereHas('customerDetails', function (Builder $q) use ($filters) {
                        $q->where('name', 'like', "%{$filters['search']}%")
                            ->orWhere('email', 'like', "%{$filters['search']}%");
                    });
            })
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get a single order by ID.
     */
    public function getOrder(int $id): Order
    {
        return Order::with(['user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails', 'payments', 'coupon', 'tracking'])
            ->findOrFail($id);
    }

    /**
     * Create a new order with items and customer details.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Fetch product details and calculate totals
            $itemsData = $this->prepareOrderItems($data['items'] ?? []);
            $totals = $this->calculateOrderTotals($itemsData);

            // Calculate discount and final total
            $discount = $data['discount'] ?? 0;
            $totalAmount = (float) max(0, $totals['net_amount'] - $discount);

            // Create Order with temporary numbers
            $order = Order::create([
                'order_number' => 'TEMP-' . uniqid(),
                'tracking_number' => 'TEMP-' . uniqid(),
                'order_date' => $data['order_date'] ?? now(),
                'user_id' => $data['user_id'] ?? null,
                'items_count' => $totals['items_count'],
                'total_quantity' => $totals['total_quantity'],
                'net_amount' => $totals['net_amount'],
                'coupons_id' => $data['coupon_id'] ?? null,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0, // Force 0 on creation
                'balance_due' => (float) $totalAmount, // All due on creation
                'payment_status' => 'unpaid',
                'status' => $data['status'] ?? 'pending',
                'added_by' => auth()->id(),
                'modified_by' => auth()->id(),
            ]);

            // Update with real structured numbers using the order ID
            $order->update([
                'order_number' => 'ORD' . $order->id . '-' . date('dmY'),
                'tracking_number' => 'TRK' . $order->id . '-' . date('dmY'),
            ]);

            // Create Order Items
            foreach ($itemsData as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);
            }

            // Create or Update Customer Details (Always save address details)
            if (!empty($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks'])) {
                    $customerData['remarks'] = $data['remarks'];
                }

                $order->customerDetails()->updateOrCreate(
                    ['order_id' => $order->id],
                    $customerData
                );
            }

            // Payments are not created on order creation anymore based on "make paid_amount 0"

            return $order->load(['items.product', 'customerDetails', 'payments', 'coupon']);
        });
    }

    /**
     * Update an existing order.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            // Update Order Items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $order->items()->delete();

                // Prepare and create new items
                $itemsData = $this->prepareOrderItems($data['items']);
                foreach ($itemsData as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'total_price' => $item['total_price'],
                    ]);
                }

                // Recalculate totals
                $totals = $this->calculateOrderTotals($itemsData);
                $order->items_count = $totals['items_count'];
                $order->total_quantity = $totals['total_quantity'];
                $order->net_amount = $totals['net_amount'];
            }

            // Update discount and recalculate total
            if (isset($data['discount'])) {
                $order->discount = $data['discount'];
            }

            // Recalculate total amount
            $order->total_amount = max(0, $order->net_amount - $order->discount);

            // Update coupon
            if (isset($data['coupon_id'])) {
                $order->coupons_id = $data['coupon_id'];
            }

            // Update Customer Details
            if (isset($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks'])) {
                    $customerData['remarks'] = $data['remarks'];
                }

                $order->customerDetails()->updateOrCreate(
                    ['order_id' => $order->id],
                    $customerData
                );
            }

            // Update other fields
            if (isset($data['order_date'])) {
                $order->order_date = $data['order_date'];
            }
            if (isset($data['status'])) {
                $order->status = $data['status'];
            }

            // Update payment information from payments
            $order->updatePaymentStatus();
            $order->updatePaymentMethod();

            $order->modified_by = auth()->id();
            $order->save();

            return $order->load(['items.product', 'customerDetails', 'payments', 'coupon']);
        });
    }

    /**
     * Soft delete an order.
     */
    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            // Delete order items
            $order->items()->delete();

            // Delete customer details
            $order->customerDetails()->delete();

            // Delete payments (soft delete)
            $order->payments()->delete();

            // Delete order
            return $order->delete();
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update([
            'status' => $status,
            'modified_by' => auth()->id()
        ]);
        return $order;
    }

    /**
     * Update customer details given an order and data.
     */
    public function updateCustomerDetails(Order $order, array $data): Order
    {
        $order->customerDetails()->updateOrCreate(
            ['order_id' => $order->id],
            $data
        );
        $order->modified_by = auth()->id();
        $order->save();
        return $order->load(['items.product', 'customerDetails', 'payments', 'coupon', 'tracking']);
    }

    /**
     * Update order tracking details.
     */
    public function updateTracking(Order $order, array $data): Order
    {
        // Allowed tracking sections (columns in order_trackings table)
        $allowedSections = [
            'job_details',
            'client_info',
            'card_specs',
            'work_assign',
            'design_print',
            'printing_status',
            'packaging_logistics',
            'packaging_status',
            'delivery_location',
            'dispatch_mode',
            'dispatch_details',
            'payment_info',
        ];

        // Prepare data for update
        $updateData = [];
        foreach ($data as $section => $content) {
            if (in_array($section, $allowedSections)) {
                // Inject Audit Info
                $content['_audit'] = [
                    'updated_by' => auth()->user()->name ?? 'Unknown',
                    'updated_at' => now()->toDateTimeString(),
                ];
                $updateData[$section] = $content;
            }
        }

        if (!empty($updateData)) {
            $order->tracking()->updateOrCreate(
                ['order_id' => $order->id],
                $updateData
            );

            // Sync payment_info with payments table if present
            if (isset($updateData['payment_info'])) {
                $paymentInfo = $updateData['payment_info'];
                $payments = $paymentInfo['payments'] ?? $paymentInfo;

                // Ensure $payments is an array
                if (!is_array($payments)) {
                    $payments = [$payments];
                }

                // Get current completed payment IDs for this order to handle deletions
                $existingPaymentIds = $order->payments()
                    ->where('payment_status', 'completed')
                    ->pluck('id')
                    ->toArray();

                $processedIds = [];

                foreach ($payments as $payInfo) {
                    $amount = $payInfo['amount'] ?? 0;

                    // Skip empty or zero amount payments
                    if ($amount <= 0) {
                        continue;
                    }

                    $paymentData = [
                        'payment_method' => $payInfo['payment_method'] ?? 'cash',
                        'transaction_id' => $payInfo['transaction_id'] ?? null,
                        'signature_name' => $payInfo['signature_name'] ?? null,
                        'payment_status' => 'completed',
                        'payment_date' => $payInfo['payment_date'] ?? now(),
                        'amount' => $amount,
                        'added_by' => auth()->id(),
                        'modified_by' => auth()->id(),
                    ];

                    if (isset($payInfo['id'])) {
                        $payment = $order->payments()->find($payInfo['id']);
                        if ($payment) {
                            $payment->update($paymentData);
                            $processedIds[] = $payment->id;
                        }
                    } else {
                        // Generate a temporary payment number to satisfy DB constraints
                        $paymentData['payment_number'] = 'TEMP-' . time() . '-' . rand(1000, 9999);
                        $newPayment = $order->payments()->create($paymentData);
                        $newPayment->update([
                            'payment_number' => 'PAY' . $newPayment->id . '-' . date('dmY')
                        ]);
                        $processedIds[] = $newPayment->id;
                    }
                }

                // Delete payments that were removed from the tracking list
                $idsToDelete = array_diff($existingPaymentIds, $processedIds);
                if (!empty($idsToDelete)) {
                    $order->payments()->whereIn('id', $idsToDelete)->delete();
                }

                // Update order financial status (this also triggers syncTrackingWithPayments)
                $order->updatePaymentStatus();
            }
        }

        return $order->load(['items.product', 'customerDetails', 'payments', 'coupon', 'tracking']);
    }

    /**
     * Prepare order items with product details.
     */
    protected function prepareOrderItems(array $items): array
    {
        $preparedItems = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? $product->price;
            $discountAmount = $item['discount_amount'] ?? 0;
            $totalPrice = max(0, ($quantity * $unitPrice) - $discountAmount);

            $preparedItems[] = [
                'product_id' => $product->id,
                'product_name' => $item['product_name'] ?? $product->title,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_price' => $totalPrice,
            ];
        }

        return $preparedItems;
    }

    /**
     * Calculate order totals from items.
     */
    protected function calculateOrderTotals(array $items): array
    {
        $netAmount = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $netAmount += $item['total_price'];
            $totalQuantity += $item['quantity'];
        }

        return [
            'items_count' => count($items),
            'total_quantity' => $totalQuantity,
            'net_amount' => $netAmount,
        ];
    }


    /**
     * Generate unique order number.
     * @deprecated Use post-creation update logic
     */
    protected function generateOrderNumber(): string
    {
        return 'ORD-' . date('dmY') . '-' . uniqid();
    }

    /**
     * Generate unique tracking number.
     * @deprecated Use post-creation update logic
     */
    protected function generateTrackingNumber(): string
    {
        return 'TRK-' . date('dmY') . '-' . uniqid();
    }

}
