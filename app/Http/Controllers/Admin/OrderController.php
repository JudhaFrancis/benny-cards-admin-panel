<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\OrderTrackingService;
use App\Services\OrderListingService;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderTrackingService $orderTrackingService,
        protected OrderListingService $orderListingService
    ) {
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page') ?: $request->get('limit') ?: 20;

        $result = $this->orderListingService->listOrdersWithCounts(
            $request->all(),
            (int) $perPage
        );

        $response = $result['paginator']->toArray();
        $response['status_counts'] = $result['status_counts'];

        if (!$request->has('stage')) {
            $response['stage_summaries'] = $this->orderListingService->getStageSummaries($request->all());
        }

        return response()->json([
            'success' => true,
            'data' => $response,
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
            'priority' => 'nullable|string|in:P1,P2,P3,P4',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required_without:items.*.product_id|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.product_image' => 'nullable',
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
        $order = $this->orderListingService->getOrder($id);

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
        $order = $this->orderListingService->getOrder($id);
        \Log::info("Order update payload for ID $id: ", $request->all());

        $request->validate([
            'customer.name' => 'nullable|string',
            'customer.phone' => 'nullable|string',
            'customer.address_1' => 'nullable|string',
            'priority' => 'nullable|string|in:P1,P2,P3,P4',
            'items' => 'nullable|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required_without:items.*.product_id|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.product_image' => 'nullable',
        ]);

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
        $order = $this->orderListingService->getOrder($id);

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
        $order = $this->orderListingService->getOrder($id);

        $updatedOrder = $this->orderService->updateCustomerDetails($order, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer details updated successfully.',
            'data' => $updatedOrder,
        ]);
    }

    /**
     * Update tracking details for an order.
     */
    public function updateTracking(Request $request, int $id): JsonResponse
    {
        $order = $this->orderListingService->getOrder($id);

        $updatedOrder = $this->orderTrackingService->updateTracking($order, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Order tracking updated successfully.',
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

        $order = $this->orderListingService->getOrder($id);
        $order = $this->orderService->updateStatus($order, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
        ]);
    }
}
