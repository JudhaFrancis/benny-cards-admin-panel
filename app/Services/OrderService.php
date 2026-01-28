<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class OrderService
{
    /**
     * Get paginated orders with filters.
     */
    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['customer', 'items'])
            ->when(isset($filters['status']), function (Builder $query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['search']), function (Builder $query) use ($filters) {
                $query->where('order_number', 'like', "%{$filters['search']}%")
                    ->orWhereHas('customer', function ($q) use ($filters) {
                        $q->where('name', 'like', "%{$filters['search']}%");
                    });
            })
            ->latest('placed_at')
            ->paginate($perPage);
    }

    /**
     * Get a single order by ID.
     */
    public function getOrder(int $id): Order
    {
        return Order::with(['customer', 'items.product'])->findOrFail($id);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, OrderStatus $status): Order
    {
        $order->update(['status' => $status]);

        // Potential logic: Send email notification, update stock, etc.

        return $order;
    }
}
