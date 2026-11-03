<?php

namespace App\Services;

use App\Models\OrderEnquiry;
use App\Models\OrderEnquiryItem;
use App\Models\OrderEnquiryActivity;
use App\Models\Order;
use App\Enums\EnquiryStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnquiryService
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Generate sequential enquiry number: ENQ-OCT26001
     */
    public function generateEnquiryNumber(): string
    {
        $prefix = 'ENQ-';
        $monthYear = strtoupper(date('My')); // e.g., OCT26

        $latestEnquiry = OrderEnquiry::withTrashed()
            ->where('enquiry_number', 'LIKE', $prefix . $monthYear . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestEnquiry) {
            $sequence = 1;
        } else {
            $prefixLength = strlen($prefix . $monthYear);
            $sequenceStr = substr($latestEnquiry->enquiry_number, $prefixLength);
            $sequence = (int) $sequenceStr + 1;
        }

        return $prefix . $monthYear . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * List enquiries with filtering and status counts.
     */
    public function listEnquiries(array $filters = [], int $perPage = 20): array
    {
        $query = OrderEnquiry::with(['items.product', 'activities.creator', 'addedBy', 'modifiedBy', 'convertedOrder'])
            ->orderBy('id', 'desc');

        // Status counts across all enquiries
        $statusCounts = [
            'all' => OrderEnquiry::count(),
            'new' => OrderEnquiry::where('status', 'new')->count(),
            'under_review' => OrderEnquiry::where('status', 'under_review')->count(),
            'quoted' => OrderEnquiry::where('status', 'quoted')->count(),
            'approved' => OrderEnquiry::where('status', 'approved')->count(),
            'confirmed' => OrderEnquiry::where('status', 'confirmed')->count(),
            'not_approved' => OrderEnquiry::where('status', 'not_approved')->count(),
            'follow_up_due' => OrderEnquiry::whereNotNull('next_follow_up_at')
                ->whereDate('next_follow_up_at', '<=', now()->toDateString())
                ->whereNotIn('status', ['confirmed', 'not_approved'])
                ->count(),
        ];

        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'follow_up_due') {
                $query->whereNotNull('next_follow_up_at')
                    ->whereDate('next_follow_up_at', '<=', now()->toDateString())
                    ->whereNotIn('status', ['confirmed', 'not_approved']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['source'])) {
            $src = trim($filters['source']);
            $query->where(function ($q) use ($src) {
                $q->where('source', $src)
                  ->orWhere('order_placed_in', 'like', "%{$src}%");
            });
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', 'like', "%{$filters['assigned_to']}%");
        }

        if (!empty($filters['enquiry_number'])) {
            $enqNo = trim($filters['enquiry_number']);
            $query->where('enquiry_number', 'like', "%{$enqNo}%");
        }

        if (!empty($filters['customer'])) {
            $customer = trim($filters['customer']);
            $query->where(function ($q) use ($customer) {
                $q->where('customer_name', 'like', "%{$customer}%")
                  ->orWhere('customer_phone', 'like', "%{$customer}%")
                  ->orWhere('customer_email', 'like', "%{$customer}%")
                  ->orWhere('occasion', 'like', "%{$customer}%");
            });
        }

        if (!empty($filters['requirements'])) {
            $req = trim($filters['requirements']);
            $query->where(function ($q) use ($req) {
                $q->where('card_type', 'like', "%{$req}%")
                  ->orWhere('specifications', 'like', "%{$req}%")
                  ->orWhere('card_size', 'like', "%{$req}%")
                  ->orWhere('budget_tier', 'like', "%{$req}%")
                  ->orWhere('message', 'like', "%{$req}%")
                  ->orWhereHas('items', function ($itemQ) use ($req) {
                      $itemQ->where('product_name', 'like', "%{$req}%");
                  });
            });
        }

        if (!empty($filters['quoted'])) {
            $quoted = trim($filters['quoted']);
            $cleanQuoted = preg_replace('/[^0-9.]/', '', $quoted);
            $query->where(function ($q) use ($quoted, $cleanQuoted) {
                if ($cleanQuoted !== '') {
                    $q->where('quoted_amount', 'like', "%{$cleanQuoted}%");
                }
                $q->orWhere('reference', 'like', "%{$quoted}%");
            });
        }

        if (!empty($filters['date'])) {
            $date = trim($filters['date']);
            $query->where(function ($q) use ($date) {
                $q->whereDate('created_at', $date)
                  ->orWhereDate('expected_delivery_date', $date)
                  ->orWhereDate('next_follow_up_at', $date);
            });
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('enquiry_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $startDate = $filters['start_date'] ?? $filters['date_from'] ?? null;
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        $endDate = $filters['end_date'] ?? $filters['date_to'] ?? null;
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return [
            'paginator' => $query->paginate($perPage),
            'status_counts' => $statusCounts,
        ];
    }

    /**
     * Create a new enquiry with items and initial activity.
     */
    public function createEnquiry(array $data, ?int $userId = null): OrderEnquiry
    {
        return DB::transaction(function () use ($data, $userId) {
            $enquiryNumber = $this->generateEnquiryNumber();
            $status = $data['status'] ?? 'new';

            $enquiry = OrderEnquiry::create([
                'enquiry_number' => $enquiryNumber,
                'source' => $data['source'] ?? 'walk_in',
                'reference' => $data['reference'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'] ?? null,
                'occasion' => $data['occasion'] ?? 'wedding',
                'order_placed_in' => $data['order_placed_in'] ?? 'Chennai',
                'card_type' => $data['card_type'] ?? 'customize',
                'card_size' => $data['card_size'] ?? null,
                'specifications' => $data['specifications'] ?? null,
                'budget_tier' => $data['budget_tier'] ?? null,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'message' => $data['message'] ?? null,
                'attachments' => $data['attachments'] ?? null,
                'status' => $status,
                'priority' => $data['priority'] ?? 'P4',
                'assigned_user_ids' => $data['assigned_user_ids'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'next_follow_up_at' => $data['next_follow_up_at'] ?? null,
                'quoted_amount' => $data['quoted_amount'] ?? null,
                'added_by' => $userId ?? auth()->id(),
                'modified_by' => $userId ?? auth()->id(),
            ]);

            // Save items
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = $item['quantity'] ?? 1;
                    $unitPrice = $item['unit_price'] ?? 0;
                    $totalPrice = $item['total_price'] ?? ($qty * $unitPrice);

                    $enquiry->items()->create([
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'] ?? 'Custom Card',
                        'product_image' => $item['product_image'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'specifications' => $item['specifications'] ?? null,
                    ]);
                }
            }

            // Log activity
            $sourceLabel = ucfirst(str_replace('_', ' ', $enquiry->source));
            $enquiry->activities()->create([
                'type' => 'status_change',
                'from_status' => null,
                'to_status' => $status,
                'note' => "Enquiry created via {$sourceLabel}",
                'created_by' => $userId ?? auth()->id(),
            ]);

            return $enquiry->load(['items.product', 'activities.creator', 'addedBy']);
        });
    }

    /**
     * Update an enquiry.
     */
    public function updateEnquiry(OrderEnquiry $enquiry, array $data): OrderEnquiry
    {
        return DB::transaction(function () use ($enquiry, $data) {
            $enquiry->update(array_merge($data, [
                'modified_by' => auth()->id(),
            ]));

            // Update items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                $enquiry->items()->delete();
                foreach ($data['items'] as $item) {
                    $qty = $item['quantity'] ?? 1;
                    $unitPrice = $item['unit_price'] ?? 0;
                    $totalPrice = $item['total_price'] ?? ($qty * $unitPrice);

                    $enquiry->items()->create([
                        'product_id' => $item['product_id'] ?? null,
                        'product_name' => $item['product_name'] ?? 'Custom Card',
                        'product_image' => $item['product_image'] ?? null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'specifications' => $item['specifications'] ?? null,
                    ]);
                }
            }

            return $enquiry->load(['items.product', 'activities.creator', 'addedBy', 'convertedOrder']);
        });
    }

    /**
     * Change enquiry status and log activity.
     */
    public function changeStatus(
        OrderEnquiry $enquiry,
        string $newStatus,
        ?string $note = null,
        ?float $quotedAmount = null,
        ?string $lostReason = null
    ): OrderEnquiry {
        return DB::transaction(function () use ($enquiry, $newStatus, $note, $quotedAmount, $lostReason) {
            $oldStatus = $enquiry->status;
            $updates = [
                'status' => $newStatus,
                'modified_by' => auth()->id(),
            ];

            if ($quotedAmount !== null) {
                $updates['quoted_amount'] = $quotedAmount;
            }

            if ($lostReason !== null) {
                $updates['lost_reason'] = $lostReason;
            }

            $enquiry->update($updates);

            $activityType = match($newStatus) {
                'quoted' => 'quote',
                'confirmed' => 'converted',
                default => 'status_change',
            };

            $enquiry->activities()->create([
                'type' => $activityType,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'quoted_amount' => $quotedAmount,
                'note' => $note ?: "Status updated to " . ucfirst(str_replace('_', ' ', $newStatus)),
                'created_by' => auth()->id(),
            ]);

            return $enquiry->load(['items.product', 'activities.creator', 'convertedOrder']);
        });
    }

    /**
     * Add a follow-up activity (call, WhatsApp, note).
     */
    public function addActivity(OrderEnquiry $enquiry, array $data): OrderEnquiryActivity
    {
        return DB::transaction(function () use ($enquiry, $data) {
            $activity = $enquiry->activities()->create([
                'type' => $data['type'] ?? 'note',
                'note' => $data['note'] ?? '',
                'from_status' => $enquiry->status,
                'to_status' => $data['to_status'] ?? $enquiry->status,
                'quoted_amount' => $data['quoted_amount'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['next_follow_up_at'])) {
                $enquiry->update([
                    'next_follow_up_at' => $data['next_follow_up_at'],
                    'modified_by' => auth()->id(),
                ]);
            }

            if (!empty($data['to_status']) && $data['to_status'] !== $enquiry->status) {
                $enquiry->update([
                    'status' => $data['to_status'],
                    'modified_by' => auth()->id(),
                ]);
            }

            return $activity->load('creator');
        });
    }

    /**
     * Convert an Approved enquiry into an Order.
     */
    public function convertToOrder(OrderEnquiry $enquiry, array $overrides = []): Order
    {
        if ($enquiry->converted_order_id) {
            throw new \Exception("This enquiry has already been converted to Order #{$enquiry->convertedOrder?->order_number}.");
        }

        return DB::transaction(function () use ($enquiry, $overrides) {
            // 1. Prepare items
            $itemsData = [];
            $sourceItems = !empty($overrides['items']) ? $overrides['items'] : $enquiry->items;

            foreach ($sourceItems as $item) {
                $itemArr = is_array($item) ? $item : $item->toArray();
                $qty = $itemArr['quantity'] ?? 1;
                $unitPrice = $itemArr['unit_price'] ?? 0;
                $totalPrice = $itemArr['total_price'] ?? ($qty * $unitPrice);

                $itemsData[] = [
                    'product_id' => $itemArr['product_id'] ?? null,
                    'product_name' => $itemArr['product_name'] ?? 'Custom Card',
                    'product_image' => $itemArr['product_image'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            if (empty($itemsData)) {
                $itemsData[] = [
                    'product_id' => null,
                    'product_name' => $enquiry->specifications ?: ($enquiry->card_type === 'customize' ? 'Custom Wedding Card' : 'Wedding Card'),
                    'quantity' => 1,
                    'unit_price' => $enquiry->quoted_amount ?? 0,
                    'total_price' => $enquiry->quoted_amount ?? 0,
                ];
            }

            // 2. Prepare order payload for OrderService
            $orderPayload = [
                'customer' => [
                    'name' => $overrides['customer_name'] ?? $enquiry->customer_name,
                    'phone' => $overrides['customer_phone'] ?? $enquiry->customer_phone,
                    'email' => $overrides['customer_email'] ?? $enquiry->customer_email,
                    'address_1' => $overrides['customer_address'] ?? ($enquiry->customer_address ?: 'N/A'),
                    'remarks' => "Converted from {$enquiry->enquiry_number}",
                ],
                'items' => $itemsData,
                'priority' => $overrides['priority'] ?? $enquiry->priority ?? 'P4',
                'budget_tier' => $overrides['budget_tier'] ?? $enquiry->budget_tier,
                'delivery_date' => $overrides['delivery_date'] ?? $enquiry->expected_delivery_date,
                'discount' => $overrides['discount'] ?? 0,
                'extra_charges' => $overrides['extra_charges'] ?? 0,
                'paid_amount' => $overrides['paid_amount'] ?? 0,
                'skip_enquiry_creation' => true,
            ];

            // 3. Create order via existing OrderService
            $order = $this->orderService->createOrder($orderPayload);

            // 4. Fill in Client Information stage
            $order->clientInformation()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'order_details' => [
                        'order_taken_by' => auth()->user()?->name ?? 'Staff',
                        'expected_delivery_date' => $enquiry->expected_delivery_date?->format('Y-m-d'),
                        'order_placed_in' => $enquiry->order_placed_in ?? 'Chennai',
                        'reference' => ucfirst(str_replace('_', ' ', $enquiry->source)),
                    ],
                    'client_info' => [
                        'name' => $enquiry->customer_name,
                        'phone' => $enquiry->customer_phone,
                        'address' => $enquiry->customer_address,
                        'occasion' => $enquiry->occasion ?? 'wedding',
                    ],
                    'card_specs' => [
                        'type' => $enquiry->card_type ?? 'customize',
                        'card_size' => $enquiry->card_size,
                        'specifications' => $enquiry->specifications,
                        'quantity' => array_sum(array_column($itemsData, 'quantity')),
                    ],
                    'status' => 'Pending',
                    'modified_by' => auth()->id(),
                ]
            );

            // 5. Update enquiry status and reference
            $oldStatus = $enquiry->status;
            $enquiry->update([
                'converted_order_id' => $order->id,
                'converted_at' => now(),
                'status' => 'confirmed',
                'modified_by' => auth()->id(),
            ]);

            // 6. Log conversion activity
            $enquiry->activities()->create([
                'type' => 'converted',
                'from_status' => $oldStatus,
                'to_status' => 'confirmed',
                'note' => "Successfully converted to Order #{$order->order_number}",
                'created_by' => auth()->id(),
            ]);

            return $order->load(['items', 'customerDetails', 'clientInformation']);
        });
    }

    /**
     * Create a confirmed enquiry record from a direct order.
     */
    public function createEnquiryFromOrder(Order $order, array $data = []): OrderEnquiry
    {
        return DB::transaction(function () use ($order, $data) {
            // Check if already linked
            $existing = OrderEnquiry::where('converted_order_id', $order->id)->first();
            if ($existing) {
                return $existing;
            }

            $enquiryNumber = $this->generateEnquiryNumber();
            $customer = $order->customerDetails;
            $customerAddress = $customer ? trim(($customer->address_1 ?? '') . (!empty($customer->address_2) ? (', ' . $customer->address_2) : '')) : null;

            $enquiry = OrderEnquiry::create([
                'enquiry_number' => $enquiryNumber,
                'source' => $data['source'] ?? 'website',
                'user_id' => $order->user_id,
                'customer_name' => $customer?->name ?? 'Customer',
                'customer_phone' => $customer?->phone ?? '-',
                'customer_email' => $customer?->email ?? null,
                'customer_address' => $customerAddress ?: null,
                'occasion' => $data['occasion'] ?? 'wedding',
                'order_placed_in' => $data['order_placed_in'] ?? 'Website',
                'card_type' => $data['card_type'] ?? 'website',
                'status' => 'confirmed',
                'priority' => $order->priority ?? 'P4',
                'budget_tier' => $order->budget_tier,
                'expected_delivery_date' => $order->delivery_date,
                'quoted_amount' => $order->total_amount,
                'converted_order_id' => $order->id,
                'converted_at' => now(),
                'added_by' => $order->added_by ?: $order->user_id,
                'modified_by' => $order->modified_by ?: $order->user_id,
            ]);

            // Copy items to order_enquiry_items
            foreach ($order->items as $item) {
                $enquiry->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?? 'Custom Card',
                    'product_image' => $item->product_image ?? null,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            // Log activity
            $sourceLabel = ucfirst(str_replace('_', ' ', $enquiry->source));
            $enquiry->activities()->create([
                'type' => 'converted',
                'from_status' => null,
                'to_status' => 'confirmed',
                'quoted_amount' => $order->total_amount,
                'note' => "Direct order #{$order->order_number} placed via {$sourceLabel}",
                'created_by' => $order->added_by ?: $order->user_id,
            ]);

            return $enquiry->load(['items.product', 'activities.creator', 'convertedOrder']);
        });
    }
}
