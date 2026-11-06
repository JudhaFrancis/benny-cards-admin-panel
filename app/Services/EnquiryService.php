<?php

namespace App\Services;

use App\Models\OrderEnquiry;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Enquiry flow:
 *   Sales staff create an enquiry with the same form/payload as Create Order → status "pending"
 *   An admin (Enquiry → Approve permission) approves it                      → order is created, status "approved"
 *   …or rejects it with a reason                                              → status "rejected"
 * Website orders and direct admin orders get an auto-created "approved" enquiry for reporting.
 */
class EnquiryService
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const DETAIL_RELATIONS = [
        'items.product',
        'branch:id,name',
        'addedBy',
        'modifiedBy',
        'approvedBy',
        'rejectedBy',
        'convertedOrder',
    ];

    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Whether the user can approve/reject enquiries and see everyone's enquiries.
     */
    public function canApprove(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $user->loadMissing(['role.permissions.module', 'role.permissions.action']);

        if (strtolower($user->role?->name ?? '') === 'super-admin') {
            return true;
        }

        return collect($user->permission_names)->contains('enquiry-approve');
    }

    /**
     * Sales staff only see the enquiries they created; approvers see all.
     */
    public function visibleQuery(?User $user)
    {
        $query = OrderEnquiry::query();

        if (!$this->canApprove($user)) {
            $query->where('added_by', $user?->id ?? 0);
        }

        return $query;
    }

    /**
     * Enquiry number: EQ-NOV26001 (same format and process as order numbers)
     */
    public function generateEnquiryNumber(): string
    {
        return SequenceNumberGenerator::next(OrderEnquiry::class, 'enquiry_number', 'EQ-');
    }

    /**
     * List enquiries with filtering and status counts.
     */
    public function listEnquiries(array $filters = [], int $perPage = 20, ?User $user = null): array
    {
        $user = $user ?? auth()->user();

        $query = $this->visibleQuery($user)
            ->with(['items.product', 'branch:id,name', 'addedBy', 'modifiedBy', 'approvedBy', 'convertedOrder'])
            ->orderBy('id', 'desc');

        $base = fn () => $this->visibleQuery($user);

        $statusCounts = [
            'all' => $base()->count(),
            'pending' => $base()->where('status', self::STATUS_PENDING)->count(),
            'approved' => $base()->where('status', self::STATUS_APPROVED)->count(),
            'rejected' => $base()->where('status', self::STATUS_REJECTED)->count(),
        ];

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['enquiry_number'])) {
            $trimmed = trim($filters['enquiry_number']);
            $query->where(function ($q) use ($trimmed) {
                $q->where('enquiry_number', 'like', "%{$trimmed}%");
                // The UI hides the year (EQ-NOV001), so also match EQ-NOV26001 — same as the order search
                if (preg_match('/^(?:EQ-)?([A-Za-z]{3})(\d+)$/i', $trimmed, $m)) {
                    $q->orWhere('enquiry_number', 'like', "%{$m[1]}%{$m[2]}%");
                }
            });
        }

        if (!empty($filters['customer'])) {
            $customer = trim($filters['customer']);
            $query->where(function ($q) use ($customer) {
                $q->where('customer_name', 'like', "%{$customer}%")
                  ->orWhere('customer_phone', 'like', "%{$customer}%")
                  ->orWhere('customer_email', 'like', "%{$customer}%")
                  ->orWhereHas('items', fn ($itemQ) => $itemQ->where('product_name', 'like', "%{$customer}%"));
            });
        }

        if (!empty($filters['total'])) {
            $cleanTotal = preg_replace('/[^0-9.]/', '', trim($filters['total']));
            if ($cleanTotal !== '') {
                $query->where('total_amount', 'like', "%{$cleanTotal}%");
            }
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', trim($filters['date']));
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
            'can_approve' => $this->canApprove($user),
        ];
    }

    /**
     * Map the Create Order payload (customer[...], remarks, budget_tier) to enquiry columns.
     */
    protected function extractFields(array $data): array
    {
        $out = [];
        $c = $data['customer'] ?? null;

        if (is_array($c)) {
            foreach (['name' => 'customer_name', 'phone' => 'customer_phone', 'email' => 'customer_email',
                      'address_1' => 'address_1', 'address_2' => 'address_2'] as $from => $to) {
                if (array_key_exists($from, $c)) {
                    $out[$to] = ($c[$from] === '' ? null : $c[$from]);
                }
            }
        }

        if (array_key_exists('remarks', $data)) {
            $out['remarks'] = $data['remarks'] ?: null;
        }

        if (array_key_exists('branch_id', $data)) {
            $out['branch_id'] = !empty($data['branch_id']) ? (int) $data['branch_id'] : null;
        }

        if (array_key_exists('budget_tier', $data)) {
            $out['budget_tier'] = (!empty($data['budget_tier']) && $data['budget_tier'] !== 'None') ? $data['budget_tier'] : null;
        }

        return $out;
    }

    /**
     * Save items with the same rules as order items (catalog price, manual items, image upload)
     * and recalculate the grand total.
     */
    protected function syncItemsAndTotals(OrderEnquiry $enquiry, array $items, array $data): void
    {
        $itemsData = $this->orderService->prepareOrderItems($items);

        $enquiry->items()->delete();
        foreach ($itemsData as $item) {
            $enquiry->items()->create($item);
        }

        $this->recalculateTotals($enquiry, $itemsData, $data);
    }

    protected function recalculateTotals(OrderEnquiry $enquiry, ?array $itemsData, array $data): void
    {
        $itemsData = $itemsData ?? $enquiry->items()->get()->toArray();
        $totals = $this->orderService->calculateOrderTotals($itemsData);

        $discount = (float) ($data['discount'] ?? $enquiry->discount ?? 0);
        $extra = (float) ($data['extra_charges'] ?? $enquiry->extra_charges ?? 0);

        $enquiry->update([
            'discount' => $discount,
            'extra_charges' => $extra,
            'total_amount' => max(0, $totals['net_amount'] + $extra - $discount),
        ]);
    }

    /**
     * Create a new enquiry (status pending) with items.
     */
    public function createEnquiry(array $data, ?int $userId = null): OrderEnquiry
    {
        return DB::transaction(function () use ($data, $userId) {
            $userId = $userId ?? auth()->id();

            $enquiry = OrderEnquiry::create(array_merge($this->extractFields($data), [
                'enquiry_number' => $this->generateEnquiryNumber(),
                'user_id' => $data['user_id'] ?? null,
                'status' => self::STATUS_PENDING,
                'added_by' => $userId,
                'modified_by' => $userId,
            ]));

            $this->syncItemsAndTotals($enquiry, $data['items'] ?? [], $data);

            return $enquiry->load(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Update a pending enquiry. Items are replaced when sent.
     */
    public function updateEnquiry(OrderEnquiry $enquiry, array $data): OrderEnquiry
    {
        if ($enquiry->status !== self::STATUS_PENDING) {
            throw new \RuntimeException('Only pending enquiries can be edited.');
        }

        return DB::transaction(function () use ($enquiry, $data) {
            $enquiry->update(array_merge($this->extractFields($data), [
                'modified_by' => auth()->id(),
            ]));

            if (isset($data['items']) && is_array($data['items'])) {
                $this->syncItemsAndTotals($enquiry, $data['items'], $data);
            } else {
                $this->recalculateTotals($enquiry, null, $data);
            }

            return $enquiry->fresh(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Approve a pending enquiry: creates the order exactly like Create Order does,
     * then locks and links the enquiry.
     */
    public function approve(OrderEnquiry $enquiry): Order
    {
        if ($enquiry->converted_order_id) {
            throw new \RuntimeException("This enquiry has already been approved as Order #{$enquiry->convertedOrder?->order_number}.");
        }
        if ($enquiry->status !== self::STATUS_PENDING) {
            throw new \RuntimeException('Only pending enquiries can be approved.');
        }

        $enquiry->loadMissing(['items', 'addedBy']);
        if ($enquiry->items->isEmpty()) {
            throw new \RuntimeException('Add at least one item to the enquiry before approving.');
        }

        return DB::transaction(function () use ($enquiry) {
            $itemsData = $enquiry->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_image' => $item->product_image,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount ?? 0,
            ])->all();

            $remarks = trim(($enquiry->remarks ? $enquiry->remarks . "\n" : '') . "From enquiry {$enquiry->enquiry_number}");

            $order = $this->orderService->createOrder([
                'customer' => [
                    'name' => $enquiry->customer_name,
                    'phone' => $enquiry->customer_phone,
                    'email' => $enquiry->customer_email,
                    'address_1' => $enquiry->address_1 ?: 'N/A',
                    'address_2' => $enquiry->address_2,
                ],
                'remarks' => $remarks,
                'items' => $itemsData,
                'branch_id' => $enquiry->branch_id,
                'budget_tier' => $enquiry->budget_tier,
                'discount' => $enquiry->discount ?? 0,
                'extra_charges' => $enquiry->extra_charges ?? 0,
                'user_id' => $enquiry->user_id ?? $enquiry->added_by,
                'skip_enquiry_creation' => true,
            ]);

            // Record who took the order (the sales person) on the first tracking stage
            if ($enquiry->addedBy) {
                $order->clientInformation()->first()?->update([
                    'order_details' => ['order_taken_by' => $enquiry->addedBy->name],
                ]);
            }

            $enquiry->update([
                'status' => self::STATUS_APPROVED,
                'converted_order_id' => $order->id,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'modified_by' => auth()->id(),
            ]);

            return $order->load(['items', 'customerDetails', 'clientInformation']);
        });
    }

    /**
     * Reject a pending enquiry with a reason. No order is created.
     */
    public function reject(OrderEnquiry $enquiry, string $reason): OrderEnquiry
    {
        if ($enquiry->status !== self::STATUS_PENDING) {
            throw new \RuntimeException('Only pending enquiries can be rejected.');
        }

        return DB::transaction(function () use ($enquiry, $reason) {
            $enquiry->update([
                'status' => self::STATUS_REJECTED,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'modified_by' => auth()->id(),
            ]);

            return $enquiry->fresh(self::DETAIL_RELATIONS);
        });
    }

    /**
     * Create an already-approved enquiry record for a direct order (website or admin).
     */
    public function createEnquiryFromOrder(Order $order, array $data = []): OrderEnquiry
    {
        return DB::transaction(function () use ($order) {
            $existing = OrderEnquiry::where('converted_order_id', $order->id)->first();
            if ($existing) {
                return $existing;
            }

            $customer = $order->customerDetails;
            $userId = $order->added_by ?: $order->user_id;

            $enquiry = OrderEnquiry::create([
                'enquiry_number' => $this->generateEnquiryNumber(),
                'user_id' => $order->user_id,
                'branch_id' => $order->branch_id,
                'customer_name' => $customer?->name ?? 'Customer',
                'customer_phone' => $customer?->phone ?? '-',
                'customer_email' => $customer?->email ?? null,
                'address_1' => $customer?->address_1,
                'address_2' => $customer?->address_2,
                'remarks' => $customer?->remarks,
                'budget_tier' => $order->budget_tier,
                'status' => self::STATUS_APPROVED,
                'total_amount' => $order->total_amount,
                'discount' => $order->discount ?? 0,
                'extra_charges' => $order->extra_charges ?? 0,
                'converted_order_id' => $order->id,
                'approved_by' => $userId,
                'approved_at' => now(),
                'added_by' => $userId,
                'modified_by' => $order->modified_by ?: $order->user_id,
            ]);

            foreach ($order->items as $item) {
                $enquiry->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?? 'Custom Card',
                    'product_image' => $item->product_image ?? null,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount ?? 0,
                    'total_price' => $item->total_price,
                ]);
            }

            return $enquiry->load(self::DETAIL_RELATIONS);
        });
    }
}
