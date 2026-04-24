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
    ) {
    }

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
                    $query->whereHas($stageRelation, function ($sub) use ($filters, $stage) {
                        // Capture the date from any possible key name
                        $assignedDate = data_get($filters, 'computed_assigned_date') 
                                     ?: data_get($filters, 'assigned_date')
                                     ?: data_get($filters, 'date');

                        if ($assignedDate) {
                            $time = strtotime($assignedDate);
                            // Try every possible formatting of the date
                            $vals = [
                                date('d-m-Y', $time),
                                date('Y-m-d', $time),
                                date('d/m/Y', $time),
                                date('m-d-Y', $time)
                            ];
                            
                            $col = match ($stage) {
                                'designing' => 'work_assign',
                                'printing' => 'printing_status',
                                'packaging' => 'packaging_logistics',
                                'delivery' => 'dispatch_mode',
                                default => null
                            };
                            
                            if ($col) {
                                $sub->where(function($q) use ($col, $vals) {
                                    foreach ($vals as $v) {
                                        $q->orWhere($col, 'LIKE', "%{$v}%");
                                    }
                                });
                            }
                        }

                        // Printing Days Status Filter (Delayed / On Time)
                        $printingDays = data_get($filters, 'printing_days_status');
                        if ($stage === 'printing' && $printingDays && $printingDays !== 'all') {
                            if ($printingDays === 'delayed') {
                                $sub->whereRaw("DATEDIFF(STR_TO_DATE(json_unquote(json_extract(printing_status, '$.assigned_date')), '%d-%m-%Y'), CURDATE()) < 0");
                            } else {
                                $sub->whereRaw("DATEDIFF(STR_TO_DATE(json_unquote(json_extract(printing_status, '$.assigned_date')), '%d-%m-%Y'), CURDATE()) >= 0");
                            }
                        }

                        // Packaging Start/End Time Filters
                        // Packaging Start/End Time Filters (Hyper-Robust)
                        // Packaging Start/End Time Filters (Hybrid Format Match)
                        if ($stage === 'packaging') {
                            $startTime = data_get($filters, 'start_time');
                            if ($startTime) {
                                $time = strtotime($startTime);
                                $sub->where(function($q) use ($startTime, $time) {
                                    $q->orWhere('packaging_logistics', 'LIKE', '%' . date('H:i', $time) . '%')
                                      ->orWhere('packaging_logistics', 'LIKE', '%' . date('h:i A', $time) . '%')
                                      ->orWhere('packaging_logistics', 'LIKE', '%' . date('h:i a', $time) . '%')
                                      ->orWhere('packaging_logistics', 'LIKE', '%' . preg_replace('/[^0-9]/', '%', $startTime) . '%');
                                });
                            }
                            $endTime = data_get($filters, 'end_time');
                            if ($endTime) {
                                $time = strtotime($endTime);
                                $sub->where(function($q) use ($endTime, $time) {
                                    $q->orWhere('packaging_logistics', 'LIKE', '%' . date('H:i', $time) . '%')
                                      ->orWhere('packaging_logistics', 'LIKE', '%' . date('h:i A', $time) . '%')
                                      ->orWhere('packaging_logistics', 'LIKE', '%' . date('h:i a', $time) . '%')
                                      ->orWhere('packaging_logistics', 'LIKE', '%' . preg_replace('/[^0-9]/', '%', $endTime) . '%');
                                });
                            }
                        }
                    });
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
            ->when(data_get($filters, 'resolved_status') ?: data_get($filters, 'status'), function (Builder $query, $value) {
                if ($value !== 'all') {
                    $query->where(function ($q) use ($value) {
                        // 1. Check main table status first
                        $q->where('status', '=', $value);

                        // 2. High-Precision Stage Mapping (matches the model's getResolvedStatusAttribute logic)
                        if (stripos('Delivered', $value) !== false) {
                            $q->orWhereHas('dispatchDelivery', fn($sq) => $sq->where('status', 'Completed'));
                        }
                        
                        if (stripos('Out for Delivery', $value) !== false) {
                            $q->orWhereHas('dispatchDelivery', fn($sq) => $sq->where('status', 'Process'));
                        }

                        if (stripos('Packed', $value) !== false) {
                            $q->orWhereHas('packaging', fn($sq) => $sq->where('status', 'Completed'))
                              ->whereDoesntHave('dispatchDelivery');
                        }

                        if (stripos('Packing in Progress', $value) !== false) {
                            $q->orWhereHas('packaging', fn($sq) => $sq->where('status', 'Process'));
                        }

                        if (stripos('Printed', $value) !== false) {
                            $q->orWhereHas('printing', fn($sq) => $sq->where('status', 'Completed'))
                              ->whereDoesntHave('packaging');
                        }

                        if (stripos('Printing in Progress', $value) !== false) {
                            $q->orWhereHas('printing', fn($sq) => $sq->where('status', 'Process'));
                        }

                        if (stripos('Designed', $value) !== false) {
                            $q->orWhereHas('designing', fn($sq) => $sq->where('status', 'Completed'))
                              ->whereDoesntHave('printing');
                        }

                        if (stripos('Designing in Progress', $value) !== false) {
                            $q->orWhereHas('designing', fn($sq) => $sq->where('status', 'Process'));
                        }

                        if (stripos('Confirmed', $value) !== false) {
                            $q->orWhereHas('clientInformation', fn($sq) => $sq->where('status', 'Completed'))
                              ->whereDoesntHave('designing');
                        }

                        if (stripos('New Order', $value) !== false) {
                            $q->orWhereDoesntHave('clientInformation');
                        }
                    });
                }
            })

            ->when(data_get($filters, 'order_date'), function (Builder $query, $value) {
                $query->whereDate('order_date', $value);
            })
            ->when(data_get($filters, 'delivery_date'), function (Builder $query, $value) {
                $query->whereDate('delivery_date', $value);
            })
            ->when(data_get($filters, 'items_count'), function (Builder $query, $value) {
                $query->where('items_count', $value);
            })
            ->when(data_get($filters, 'order_number'), function (Builder $query, $value) {
                $query->where('order_number', 'like', "%{$value}%");
            })
            ->when(data_get($filters, 'customer_details_name') ?: data_get($filters, 'customer_details.name'), function (Builder $query, $value) {
                $query->whereHas('customerDetails', function (Builder $sub) use ($value) {
                    $sub->where('name', 'like', "%{$value}%");
                });
            })
            ->when(data_get($filters, 'customer_details_phone') ?: data_get($filters, 'customer_details.phone'), function (Builder $query, $value) {
                $query->whereHas('customerDetails', function (Builder $sub) use ($value) {
                    $sub->where('phone', 'like', "%{$value}%");
                });
            })
            ->when(data_get($filters, 'created_at'), function (Builder $query, $value) {
                $query->whereDate('created_at', $value);
            })
            ->when(data_get($filters, 'computed_modified_at') ?: data_get($filters, 'updated_at'), function (Builder $query, $value) {
                $query->where(function ($q) use ($value) {
                    // Check main table
                    $q->whereDate('updated_at', $value)
                      // Check all stage tables
                      ->orWhereHas('clientInformation', fn($sub) => $sub->whereDate('updated_at', $value))
                      ->orWhereHas('designing', fn($sub) => $sub->whereDate('updated_at', $value))
                      ->orWhereHas('printing', fn($sub) => $sub->whereDate('updated_at', $value))
                      ->orWhereHas('packaging', fn($sub) => $sub->whereDate('updated_at', $value))
                      ->orWhereHas('dispatchDelivery', fn($sub) => $sub->whereDate('updated_at', $value));
                });
            })
            // Client Information Stage Filters
            // Stage-Aware Assignment Filters (Assigned Name)
            ->when(data_get($filters, 'computed_assigned_name'), function (Builder $query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->whereHas('clientInformation', function ($sub) use ($value) {
                        $sub->where('order_details->order_taken_by', 'like', "%{$value}%");
                    })
                        ->orWhereHas('designing', function ($sub) use ($value) {
                            $sub->where('work_assign->assigned_to', 'like', "%{$value}%");
                        })
                        ->orWhereHas('printing', function ($sub) use ($value) {
                            $sub->where('printing_status->assigned_to', 'like', "%{$value}%");
                        })
                        ->orWhereHas('packaging', function ($sub) use ($value) {
                            $sub->where('packaging_logistics->crafted_by', 'like', "%{$value}%");
                        })
                        ->orWhereHas('dispatchDelivery', function ($sub) use ($value) {
                            $sub->where('dispatch_mode->signature_name', 'like', "%{$value}%");
                        });
                });
            })
            // Process Status (Designing)
            ->when(data_get($filters, 'computed_process_status'), function (Builder $query, $value) {
                if ($value === 'Content Received') {
                    $query->whereHas('designing', fn($sub) => $sub->where('work_assign->content_received', true));
                } elseif ($value === 'Content Not Received') {
                    $query->whereHas('designing', fn($sub) => $sub->where('work_assign->content_received', false));
                }
            })
            // Completed By (Designing)
            ->when(data_get($filters, 'computed_completed_by'), function (Builder $query, $value) {
                $query->whereHas('designing', fn($sub) => $sub->where('work_assign->completed_by', 'like', "%{$value}%"));
            })
            // Printing Days Status (Printing)
            ->when(data_get($filters, 'computed_printing_days_status'), function (Builder $query, $value) {
                // This logic mirrors the frontend date calculation roughly
                if ($value === 'Delayed') {
                    $query->whereHas('printing', function ($sub) {
                        $sub->whereRaw('DATEDIFF(NOW(), json_unquote(json_extract(printing_status, "$.assigned_date"))) > 7');
                    });
                } elseif ($value === 'On Time') {
                    $query->whereHas('printing', function ($sub) {
                        $sub->whereRaw('DATEDIFF(NOW(), json_unquote(json_extract(printing_status, "$.assigned_date"))) <= 7');
                    });
                }
            })

            ->when(data_get($filters, 'printing_days_status') && data_get($filters, 'printing_days_status') !== 'all', function (Builder $query) use ($filters) {
                $status = data_get($filters, 'printing_days_status');
                $query->whereHas('printing', function ($sub) use ($status) {
                    if ($status === 'delayed') {
                        $sub->whereRaw("DATEDIFF(STR_TO_DATE(json_unquote(json_extract(printing_status, '$.assigned_date')), '%d-%m-%Y'), CURDATE()) < 0");
                    } else {
                        $sub->whereRaw("DATEDIFF(STR_TO_DATE(json_unquote(json_extract(printing_status, '$.assigned_date')), '%d-%m-%Y'), CURDATE()) >= 0");
                    }
                });
            })
            ->when(data_get($filters, 'client_information.order_details.order_placed_in') ?: data_get($filters, 'client_information_order_details_order_placed_in'), function (Builder $query, $value) {
                $query->whereHas('clientInformation', function (Builder $sub) use ($value) {
                    $sub->where('order_details->order_placed_in', $value);
                });
            })
            // Stage-Aware Status Filter (Strict to current stage if provided)
            ->when(data_get($filters, 'computed_stage_status'), function (Builder $query, $value) use ($filters) {
                $stage = data_get($filters, 'stage');

                $query->where(function ($q) use ($value, $stage) {
                    if ($stage) {
                        $relationMapping = [
                            'client-information' => 'clientInformation',
                            'designing' => 'designing',
                            'printing' => 'printing',
                            'packaging' => 'packaging',
                            'delivery' => 'dispatchDelivery'
                        ];

                        $relation = $relationMapping[$stage] ?? null;
                        if ($relation) {
                            $q->whereHas($relation, fn($sub) => $sub->where('status', 'like', "%{$value}%"));
                            return;
                        }
                    }

                    // Fallback for global search or unknown stage
                    $q->whereHas('clientInformation', fn($sub) => $sub->where('status', 'like', "%{$value}%"))
                        ->orWhereHas('designing', fn($sub) => $sub->where('status', 'like', "%{$value}%"))
                        ->orWhereHas('printing', fn($sub) => $sub->where('status', 'like', "%{$value}%"))
                        ->orWhereHas('packaging', fn($sub) => $sub->where('status', 'like', "%{$value}%"))
                        ->orWhereHas('dispatchDelivery', fn($sub) => $sub->where('status', 'like', "%{$value}%"));
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
                    'product_image' => $item['product_image'],
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
            if (isset($data['delivery_date'])) {
                $order->delivery_date = $data['delivery_date'];
            } elseif (isset($data['customer']['expected_delivery_date'])) {
                $order->delivery_date = $data['customer']['expected_delivery_date'];
            }

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




    protected function prepareOrderItems(array $items): array
    {
        $preparedItems = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;

            // If it's not a catalog product, we must have a product_name
            if (!$product && empty($item['product_name']))
                continue;

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
        $netAmount = 0;
        $totalQuantity = 0;
        foreach ($items as $item) {
            $netAmount += $item['total_price'];
            $totalQuantity += $item['quantity'];
        }
        return ['items_count' => count($items), 'total_quantity' => $totalQuantity, 'net_amount' => $netAmount];
    }
}