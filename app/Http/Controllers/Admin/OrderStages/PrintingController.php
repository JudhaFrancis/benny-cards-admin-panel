<?php

namespace App\Http\Controllers\Admin\OrderStages;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPrinting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PrintingController extends Controller
{
    /**
     * Update Printing stage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        // 1. Update/Create Individual Stage Table
        $stage = $order->printing()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'added_by' => $order->printing ? $order->printing->added_by : auth()->id(),
                'modified_by' => auth()->id()
            ])
        );

        // 3. Auto-trigger Next Stage: Packaging
        if ($request->status === 'Completed') {
            $order->packaging()->firstOrCreate(
                ['order_id' => $order->id],
                ['status' => 'Pending']
            );
        }

        $order->modified_by = auth()->id();
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Printing details updated successfully.',
            'data' => $order->load(['printing'])
        ]);
    }
}
