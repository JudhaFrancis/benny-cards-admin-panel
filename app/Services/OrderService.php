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
        $user = auth()->user();
        $isAdmin = $user && $user->role && in_array($user->role->name, ['super-admin', 'Admin']);
        $isStaff = $user && !$isAdmin;

        return Order::query()
            ->with([
                'user',
                'items.product',
                'addedBy',
                'modifiedBy',
                'customerDetails',
                'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy',
                'payments.modifiedBy'
            ])
            ->when($isStaff && isset($filters['stage']), function (Builder $query) use ($user) {
                $userName = $user->name;
                $userId = $user->id;

                $query->where(function ($q) use ($userName, $userId) {
                    $q->where('added_by', $userId)
                        ->orWhereHas('clientInformation', function ($sub) use ($userName) {
                            $sub->where('order_details->order_taken_by', $userName);
                        })
                        ->orWhereHas('designing', function ($sub) use ($userName) {
                            $sub->where('work_assign->assigned_to', $userName)
                                ->orWhere('work_assign->completed_by', $userName);
                        })
                        ->orWhereHas('printing', function ($sub) use ($userName) {
                            $sub->where('printing_status->assigned_to', $userName);
                        })
                        ->orWhereHas('packaging', function ($sub) use ($userName) {
                            $sub->where('packaging_logistics->crafted_by', $userName)
                                ->orWhere('packaging_status->packed_by', $userName);
                        })
                        ->orWhereHas('dispatchDelivery', function ($sub) use ($userName) {
                            $sub->where('dispatch_mode->signature_name', $userName);
                        });
                });
            })
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('order_number', 'like', "%{$filters['search']}%")
                        ->orWhereHas('customerDetails', function (Builder $sub) use ($filters) {
                            $sub->where('name', 'like', "%{$filters['search']}%")
                                ->orWhere('email', 'like', "%{$filters['search']}%");
                        });
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
        return Order::with([
            'user',
            'items.product',
            'addedBy',
            'modifiedBy',
            'customerDetails',
            'coupon',
            'clientInformation.modifiedBy',
            'designing.modifiedBy',
            'printing.modifiedBy',
            'packaging.modifiedBy',
            'dispatchDelivery.modifiedBy',
            'payments.addedBy',
            'payments.modifiedBy'
        ])
            ->findOrFail($id);
    }

    /**
     * Create a new order with items and customer details.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $this->prepareOrderItems($data['items'] ?? []);
            $totals = $this->calculateOrderTotals($itemsData);

            $discount = $data['discount'] ?? 0;
            $extraCharges = $data['extra_charges'] ?? 0;
            $totalAmount = (float) max(0, $totals['net_amount'] + $extraCharges - $discount);
            $paidAmount = $data['paid_amount'] ?? 0;

            $order = Order::create([
                'order_number' => 'TEMP-' . uniqid(),
                'order_date' => $data['order_date'] ?? now(),
                'delivery_date' => $data['delivery_date'] ?? ($data['customer']['expected_delivery_date'] ?? null),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'items_count' => $totals['items_count'],
                'total_quantity' => $totals['total_quantity'],
                'net_amount' => $totals['net_amount'],
                'coupons_id' => $data['coupon_id'] ?? null,
                'discount' => $discount,
                'extra_charges' => $extraCharges,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_due' => (float) max(0, $totalAmount - $paidAmount),
                'payment_status' => $paidAmount <= 0 ? 'unpaid' : ($paidAmount < $totalAmount ? 'due' : 'paid'),
                'status' => $data['status'] ?? 'pending',
                'added_by' => auth()->id(),
                'modified_by' => auth()->id(),
            ]);

            $order->update([
                'order_number' => 'ORD' . $order->id . '-' . date('dmY'),
            ]);

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

            if (!empty($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks']))
                    $customerData['remarks'] = $data['remarks'];
                $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $customerData);
            }

            // Initialize first stage tracking
            $order->clientInformation()->create([
                'status' => 'Pending',
                'modified_by' => auth()->id()
            ]);

            return $order->load([
                'items.product',
                'customerDetails',
                'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy',
                'payments.modifiedBy'
            ]);
        });
    }

    /**
     * Update an existing order.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (isset($data['items'])) {
                $order->items()->delete();
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
                $totals = $this->calculateOrderTotals($itemsData);
                $order->items_count = $totals['items_count'];
                $order->total_quantity = $totals['total_quantity'];
                $order->net_amount = $totals['net_amount'];
            }

            if (isset($data['discount']))
                $order->discount = $data['discount'];
            if (isset($data['extra_charges']))
                $order->extra_charges = $data['extra_charges'];
            if (isset($data['paid_amount']))
                $order->paid_amount = $data['paid_amount'];

            $expense = (float) ($order->dispatchDelivery->dispatch_mode['expense'] ?? 0);
            $order->total_amount = max(0, $order->net_amount + ($order->extra_charges ?? 0) - $order->discount + $expense);

            if (isset($data['coupon_id']))
                $order->coupons_id = $data['coupon_id'];

            if (isset($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks']))
                    $customerData['remarks'] = $data['remarks'];
                $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $customerData);
            }

            if (isset($data['order_date']))
                $order->order_date = $data['order_date'];

            $order->updatePaymentStatus();
            $order->modified_by = auth()->id();
            $order->save();

            return $order->load([
                'items.product',
                'customerDetails',
                'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy',
                'payments.modifiedBy'
            ]);
        });
    }

    /**
     * Soft delete an order.
     */
    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->customerDetails()->delete();
            $order->payments()->delete();
            return $order->delete();
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status, 'modified_by' => auth()->id()]);
        return $order;
    }

    /**
     * Update customer details.
     */
    public function updateCustomerDetails(Order $order, array $data): Order
    {
        $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $data);
        $order->modified_by = auth()->id();
        $order->save();
        return $order->load(['items.product', 'customerDetails', 'payments', 'coupon', 'clientInformation', 'designing', 'printing', 'packaging', 'dispatchDelivery']);
    }

    /**
     * Update order tracking details.
     */
    public function updateTracking(Order $order, array $data): Order
    {
        $stageMap = [
            'order_details' => 'clientInformation',
            'client_info' => 'clientInformation',
            'card_specs' => 'clientInformation',
            'work_assign' => 'designing',
            'design_print' => 'designing',
            'printing_status' => 'printing',
            'packaging_logistics' => 'packaging',
            'packaging_status' => 'packaging',
            'delivery_location' => 'dispatchDelivery',
            'dispatch_mode' => 'dispatchDelivery',
            'dispatch_details' => 'dispatchDelivery',
        ];

        DB::transaction(function () use ($order, $data, $stageMap) {
            $updatesByStage = [];
            foreach ($data as $section => $content) {
                if (isset($stageMap[$section])) {
                    $stageRelation = $stageMap[$section];
                    if (!isset($updatesByStage[$stageRelation])) {
                        $updatesByStage[$stageRelation] = [];
                    }

                    // Strip audit details from the JSON data as requested
                    if (is_array($content)) {
                        unset($content['_audit']);
                    }

                    $updatesByStage[$stageRelation][$section] = $content;
                }
            }

            // Handle direct status updates for a specific stage
            if (isset($data['status'])) {
                $relation = $data['_stage'] ?? null;

                // Fallback induction for _stage if not provided
                if (!$relation) {
                    foreach ($data as $key => $val) {
                        if (isset($stageMap[$key])) {
                            $relation = $stageMap[$key];
                            break;
                        }
                    }
                }

                if ($relation) {
                    $status = $data['status'];
                    $order->{$relation}()->updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'status' => $status,
                            'modified_by' => auth()->id()
                        ]
                    );

                    // Sequential Stage Triggering
                    if ($status === 'Completed') {
                        if ($relation === 'clientInformation') {
                            $order->designing()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        } elseif ($relation === 'designing') {
                            $order->printing()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        } elseif ($relation === 'printing') {
                            $order->packaging()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        } elseif ($relation === 'packaging') {
                            $order->dispatchDelivery()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        }
                    }
                }
            }

            // Sync delivery date to orders table from either client_info or order_details
            $newDeliveryDate = $data['order_details']['expected_delivery_date']
                ?? $data['client_info']['expected_delivery_date']
                ?? null;

            if ($newDeliveryDate) {
                $order->update(['delivery_date' => $newDeliveryDate]);
            }

            foreach ($updatesByStage as $relation => $sectionData) {
                $order->{$relation}()->updateOrCreate(
                    ['order_id' => $order->id],
                    array_merge($sectionData, [
                        'modified_by' => auth()->id()
                    ])
                );
            }

            if (isset($data['payment_info'])) {
                $paymentInfo = $data['payment_info'];
                $payments = $paymentInfo['payments'] ?? $paymentInfo;
                if (!is_array($payments))
                    $payments = [$payments];

                $existingPaymentIds = $order->payments()->where('payment_status', 'completed')->pluck('id')->toArray();
                $processedIds = [];

                foreach ($payments as $payInfo) {
                    $amount = $payInfo['amount'] ?? 0;
                    if ($amount <= 0)
                        continue;

                    $paymentData = [
                        'payment_method' => $payInfo['payment_method'] ?? 'cash',
                        'transaction_id' => $payInfo['transaction_id'] ?? null,
                        'signature_name' => $payInfo['signature_name'] ?? null,
                        'payment_status' => 'completed',
                        'payment_date' => $payInfo['payment_date'] ?? now(),
                        'amount' => $amount,
                        'added_by' => auth()->id(),
                        'modified_by' => auth()->id()
                    ];

                    if (isset($payInfo['id'])) {
                        $payment = $order->payments()->find($payInfo['id']);
                        if ($payment) {
                            $payment->update($paymentData);
                            $processedIds[] = $payment->id;
                        }
                    } else {
                        $paymentData['payment_number'] = 'TEMP-' . time() . '-' . rand(1000, 9999);
                        $newPayment = $order->payments()->create($paymentData);
                        $newPayment->update(['payment_number' => 'PAY' . $newPayment->id . '-' . date('dmY')]);
                        $processedIds[] = $newPayment->id;
                    }
                }

                $idsToDelete = array_diff($existingPaymentIds, $processedIds);
                if (!empty($idsToDelete))
                    $order->payments()->whereIn('id', $idsToDelete)->delete();

                $order->updatePaymentStatus();
            }

            // Update total amount based on dispatch expense
            $order->refresh();
            $expense = (float) ($order->dispatchDelivery->dispatch_mode['expense'] ?? 0);
            $totalAmount = max(0, $order->net_amount + ($order->extra_charges ?? 0) - $order->discount + $expense);
            $order->update([
                'total_amount' => $totalAmount,
                'balance_due' => max(0, $totalAmount - $order->paid_amount),
                'modified_by' => auth()->id()
            ]);
        });

        return $order->load([
            'items.product',
            'customerDetails',
            'coupon',
            'clientInformation.modifiedBy',
            'designing.modifiedBy',
            'printing.modifiedBy',
            'packaging.modifiedBy',
            'dispatchDelivery.modifiedBy',
            'payments.addedBy',
            'payments.modifiedBy'
        ]);
    }


    protected function prepareOrderItems(array $items): array
    {
        $preparedItems = [];
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product)
                continue;
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? $product->price;
            $discountAmount = $item['discount_amount'] ?? 0;
            $totalPrice = max(0, ($quantity * $unitPrice) - $discountAmount);
            $preparedItems[] = ['product_id' => $product->id, 'product_name' => $item['product_name'] ?? $product->title, 'quantity' => $quantity, 'unit_price' => $unitPrice, 'discount_amount' => $discountAmount, 'total_price' => $totalPrice];
        }
        return $preparedItems;
    }

    protected function calculateOrderTotals(array $items): array
    {
        $netAmount = 0;
        $totalQuantity = 0;
        foreach ($items as $item) {
            $netAmount += $item['total_price'];
            $totalQuantity += $item['quantity'];
        }
        return ['items_count' => count($items), 'total_quantity' => $totalQuantity, 'net_amount' => $netAmount];
    }
}
