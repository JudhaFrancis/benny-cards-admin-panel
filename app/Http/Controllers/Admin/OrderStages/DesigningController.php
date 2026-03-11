<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDesigning;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesigningController extends Controller
{
    /**
     * Update Designing stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $data = $request->only(['work_assign', 'design_print', 'status']);
        $auditData = [
            'updated_by' => auth()->user()->name ?? 'Unknown',
            'updated_at' => now()->toDateTimeString(),
        ];
        $data['audit_details'] = $auditData;

        // 1. Update/Create Individual Stage Table
        $order->designing()->updateOrCreate(
            ['order_id' => $order->id],
            $data
        );

        // 2. Sync with order_trackings (JSON) for frontend compatibility
        $trackingData = [];
        $sections = ['work_assign', 'design_print'];
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

        // 3. Auto-trigger Next Stage: Printing
        if ($request->status === 'Completed') {
            $order->printing()->firstOrCreate(
                ['order_id' => $order->id],
                ['status' => 'Pending']
            );
        }

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Designing details updated successfully.',
            'data' => $order->load(['tracking', 'designing'])
        ]);
    }
}
