<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PrintingController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Update Printing stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        
        // Use central service for tracking updates
        $this->orderService->updateTracking($order, array_merge($request->all(), ['_stage' => 'printing']));

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Printing details updated successfully.',
            'data' => $order->load(['printing'])
        ]);
    }
}
