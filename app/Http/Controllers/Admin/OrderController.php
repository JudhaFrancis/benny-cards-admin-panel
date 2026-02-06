<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listOrders(
            $request->only(['status', 'search']),
            $request->get('limit', 15)
        );

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer.name' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.address_1' => 'required|string',
            'customer.city_1' => 'required|string',
            'customer.state_1' => 'required|string',
            'customer.country' => 'required|string',
            'customer.post_code_1' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = $this->orderService->createOrder($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data' => $order,
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);

        $updatedOrder = $this->orderService->updateOrder($order, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data' => $updatedOrder,
        ]);
    }

    /**
     * Remove the specified order from storage.
     */
    /**
     * Remove the specified order from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);

        $this->orderService->deleteOrder($order);

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully.',
        ]);
    }

    /**
     * Update customer details for an order.
     */
    public function updateCustomerDetails(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->getOrder($id);

        $updatedOrder = $this->orderService->updateCustomerDetails($order, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer details updated successfully.',
            'data' => $updatedOrder,
        ]);
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required'],
        ]);

        $order = $this->orderService->getOrder($id);
        $order = $this->orderService->updateStatus($order, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data' => $order->refresh()->load(['user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails', 'payments']),
        ]);
    }
}
