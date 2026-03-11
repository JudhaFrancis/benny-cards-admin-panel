<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPackaging;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackagingController extends Controller
{
    /**
     * Update Packaging stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $data = $request->only(['packaging_logistics', 'packaging_status', 'status']);
        $auditData = [
            'updated_by' => auth()->user()->name ?? 'Unknown',
            'updated_at' => now()->toDateTimeString(),
        ];
        $data['audit_details'] = $auditData;

        // 1. Update/Create Individual Stage Table
        $order->packaging()->updateOrCreate(
            ['order_id' => $order->id],
            $data
        );

        // 2. Sync with order_trackings (JSON) for frontend compatibility
        $trackingData = [];
        $sections = ['packaging_logistics', 'packaging_status'];
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

        // 3. Auto-trigger Next Stage: Dispatch & Delivery
        if ($request->status === 'Completed') {
            $order->dispatchDelivery()->firstOrCreate(
                ['order_id' => $order->id],
                ['status' => 'Pending']
            );
        }

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Packaging details updated successfully.',
            'data' => $order->load(['tracking', 'packaging'])
        ]);
    }
}
