<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderClientInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientInformationController extends Controller
{
    /**
     * Update Client Information stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $data = $request->only(['job_details', 'client_info', 'card_specs', 'status']);
        $auditData = [
            'updated_by' => auth()->user()->name ?? 'Unknown',
            'updated_at' => now()->toDateTimeString(),
        ];
        $data['audit_details'] = $auditData;

        // 1. Update/Create Individual Stage Table
        $order->clientInformation()->updateOrCreate(
            ['order_id' => $order->id],
            $data
        );

        // 2. Sync with order_trackings (JSON) for frontend compatibility
        $trackingData = [];
        $sections = ['job_details', 'client_info', 'card_specs'];
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

        // 3. Auto-trigger Next Stage: Designing
        if ($request->status === 'Completed') {
            $order->designing()->firstOrCreate(
                ['order_id' => $order->id],
                ['status' => 'Pending']
            );
        }

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Client Information updated successfully.',
            'data' => $order->load(['tracking', 'clientInformation'])
        ]);
    }
}
