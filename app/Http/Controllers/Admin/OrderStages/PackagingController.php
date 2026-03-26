<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackagingController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Update Packaging stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        // Use central service for tracking updates
        $this->orderService->updateTracking($order, array_merge($request->all(), ['_stage' => 'packaging']));

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Packaging details updated successfully.',
            'data' => $order->load(['packaging'])
        ]);
    }
}
