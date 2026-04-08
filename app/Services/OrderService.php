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
    public function __construct(
        protected OrderTrackingService $trackingService
    ) {}

    /**
     * Update order tracking details.
     */
    public function updateTracking(Order $order, array $data): Order
    {
        return $this->trackingService->updateTracking($order, $data);
    }
    /**
     * Get paginated orders with filters.
     */
    public function listOrders(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $user = auth()->user();
        $userRole = strtolower($user->role?->name ?? '');
        $isSuperAdmin = $userRole === 'super-admin';
        $isAdmin = $userRole === 'admin';
        $isStaff = $user && !$isSuperAdmin && !$isAdmin;

        return Order::query()
            ->with([
                'user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails', 'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy', 'payments.modifiedBy'
            ])
            ->when(isset($filters['stage']), function (Builder $query) use ($filters, $isSuperAdmin, $user) {
                $stage = $filters['stage'];
                $stageRelation = match ($stage) {
                    'client-information' => 'clientInformation',
                    'designing' => 'designing',
                    'printing' => 'printing',
                    'packaging' => 'packaging',
                    'delivery' => 'dispatchDelivery',
                    default => null
                };

                // Critical: Always filter by stage existence to fix pagination bugs for everyone.
                if ($stageRelation) {
                    $query->whereHas($stageRelation);
                }

                // If NOT Super Admin, further restrict to assigned records.
                if (!$isSuperAdmin) {
                    $userName = $user->name;
                    $userId = $user->id;

                    $query->where(function ($q) use ($userName, $userId, $stage) {
                        $q->where('added_by', $userId);
                        
                        // Stage-specific assignment checks
                        if ($stage === 'client-information') {
                            $q->orWhereHas('clientInformation', fn($sub) => $sub->where('order_details->order_taken_by', $userName));
                        } elseif ($stage === 'designing') {
                            $q->orWhereHas('designing', fn($sub) => $sub->where('work_assign->assigned_to', $userName));
                        } elseif ($stage === 'printing') {
                            $q->orWhereHas('printing', fn($sub) => $sub->where('printing_status->assigned_to', $userName));
                        } elseif ($stage === 'packaging') {
                            $q->orWhereHas('packaging', fn($sub) => $sub->where('packaging_logistics->crafted_by', $userName)->orWhere('packaging_status->packed_by', $userName));
                        } elseif ($stage === 'delivery') {
                            $q->orWhereHas('dispatchDelivery', fn($sub) => $sub->where('dispatch_mode->signature_name', $userName));
                        }
                    });
                }
            })
            ->when(isset($filters['status']) && $filters['status'] !== 'all', function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['order_number']), function (Builder $query) use ($filters) {
                $query->where('order_number', 'like', "%{$filters['order_number']}%");
            })
            ->when(isset($filters['customer_details.name']), function (Builder $query) use ($filters) {
                $query->whereHas('customerDetails', function (Builder $sub) use ($filters) {
                    $sub->where('name', 'like', "%{$filters['customer_details.name']}%");
                });
            })
            ->when(isset($filters['customer_details.phone']), function (Builder $query) use ($filters) {
                $query->whereHas('customerDetails', function (Builder $sub) use ($filters) {
                    $sub->where('phone', 'like', "%{$filters['customer_details.phone']}%");
                });
            })
            ->when(isset($filters['payment_status']) && $filters['payment_status'] !== '', function (Builder $query) use ($filters) {
                $query->where('payment_status', $filters['payment_status']);
            })
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where(function ($q) use ($filters) {
                    $q->where('order_number', 'like', "%{$filters['search']}%")
                        ->orWhereHas('customerDetails', function (Builder $sub) use ($filters) {
                            $sub->where('name', 'like', "%{$filters['search']}%")
                                ->orWhere('email', 'like', "%{$filters['search']}%")
                                ->orWhere('phone', 'like', "%{$filters['search']}%");
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
                'user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails', 'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy', 'payments.modifiedBy'
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
                    'product_image' => $item['product_image'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);
            }

            if (!empty($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks'])) $customerData['remarks'] = $data['remarks'];
                $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $customerData);
            }

            // Initialize first stage tracking
            $order->clientInformation()->create([
                'status' => 'Pending',
                'modified_by' => auth()->id()
            ]);

        return $order->load([
            'items.product', 'customerDetails', 'coupon',
            'clientInformation.modifiedBy',
            'designing.modifiedBy',
            'printing.modifiedBy',
            'packaging.modifiedBy',
            'dispatchDelivery.modifiedBy',
            'payments.addedBy', 'payments.modifiedBy'
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
                        'product_image' => $item['product_image'],
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

            if (isset($data['discount'])) $order->discount = $data['discount'];
            if (isset($data['extra_charges'])) $order->extra_charges = $data['extra_charges'];
            if (isset($data['paid_amount'])) $order->paid_amount = $data['paid_amount'];

            $expense = (float) ($order->dispatchDelivery->dispatch_mode['expense'] ?? 0);
            $order->total_amount = max(0, $order->net_amount + ($order->extra_charges ?? 0) - $order->discount + $expense);

            if (isset($data['coupon_id'])) $order->coupons_id = $data['coupon_id'];

            if (isset($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks'])) $customerData['remarks'] = $data['remarks'];
                $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $customerData);
            }

            if (isset($data['order_date'])) $order->order_date = $data['order_date'];

            $order->updatePaymentStatus();
            $order->modified_by = auth()->id();
            $order->save();

        return $order->load([
            'items.product', 'customerDetails', 'coupon',
            'clientInformation.modifiedBy',
            'designing.modifiedBy',
            'printing.modifiedBy',
            'packaging.modifiedBy',
            'dispatchDelivery.modifiedBy',
            'payments.addedBy', 'payments.modifiedBy'
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




    protected function prepareOrderItems(array $items): array
    {
        $preparedItems = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;
            
            // If it's not a catalog product, we must have a product_name
            if (!$product && empty($item['product_name'])) continue;

            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? ($product ? $product->price : 0);
            $discountAmount = $item['discount_amount'] ?? 0;
            $totalPrice = max(0, ($quantity * $unitPrice) - $discountAmount);
            
            $productImage = $item['product_image'] ?? ($product ? $product->image : null);

            // Handle manual item image upload
            if (isset($item['product_image']) && $item['product_image'] instanceof UploadedFile) {
                $file = $item['product_image'];
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                
                $uploadPath = public_path('uploads/orders');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true);
                }

                $file->move($uploadPath, $filename);
                $productImage = 'uploads/orders/' . $filename;
            }

            $preparedItems[] = [
                'product_id' => $product ? $product->id : null,
                'product_name' => $item['product_name'] ?? ($product ? $product->title : 'Unknown Product'),
                'product_image' => $productImage,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_price' => $totalPrice
            ];
        }
        return $preparedItems;
    }

    protected function calculateOrderTotals(array $items): array
    {
        $netAmount = 0; $totalQuantity = 0;
        foreach ($items as $item) { $netAmount += $item['total_price']; $totalQuantity += $item['quantity']; }
        return ['items_count' => count($items), 'total_quantity' => $totalQuantity, 'net_amount' => $netAmount];
    }
}
