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
        // 1. Update/Create Individual Stage Table
        $data = $request->all();
        $stage = $order->packaging()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'added_by' => $order->packaging ? $order->packaging->added_by : auth()->id(),
                'modified_by' => auth()->id()
            ])
        );

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
            'data' => $order->load(['packaging'])
        ]);
    }
}
