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
        // 1. Update/Create Individual Stage Table
        $stage = $order->designing()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'added_by' => $order->designing ? $order->designing->added_by : auth()->id(),
                'modified_by' => auth()->id()
            ])
        );

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
            'data' => $order->load(['designing'])
        ]);
    }
}
