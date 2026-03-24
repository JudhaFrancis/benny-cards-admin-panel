<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispatchDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DispatchDeliveryController extends Controller
{
    /**
     * Update Dispatch and Delivery stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        // 1. Update/Create Individual Stage Table
        $stage = $order->dispatchDelivery()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'added_by' => $order->dispatchDelivery ? $order->dispatchDelivery->added_by : auth()->id(),
                'modified_by' => auth()->id()
            ])
        );

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Dispatch and Delivery details updated successfully.',
            'data' => $order->load(['dispatchDelivery'])
        ]);
    }
}
