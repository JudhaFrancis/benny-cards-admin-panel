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
        $data = $request->only(['delivery_location', 'dispatch_mode', 'dispatch_details', 'status']);
        $auditData = [
            'updated_by' => auth()->user()->name ?? 'Unknown',
            'updated_at' => now()->toDateTimeString(),
        ];
        $data['audit_details'] = $auditData;

        // 1. Update/Create Individual Stage Table
        $order->dispatchDelivery()->updateOrCreate(
            ['order_id' => $order->id],
            $data
        );

        // 2. Sync with order_trackings (JSON) for frontend compatibility
        $trackingData = [];
        $sections = ['delivery_location', 'dispatch_mode', 'dispatch_details'];
        foreach ($sections as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                $val['_audit'] = $auditData;
                $trackingData[$key] = $val;
            }
        }

        if (!empty($trackingData)) {
            $order->tracking()->updateOrCreate(
                ['order_id' => $order->id],
                $trackingData
            );
        }

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Dispatch and Delivery details updated successfully.',
            'data' => $order->load(['tracking', 'dispatchDelivery'])
        ]);
    }
}
