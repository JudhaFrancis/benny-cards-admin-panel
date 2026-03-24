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
        // 1. Update/Create Individual Stage Table
        $stage = $order->clientInformation()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'added_by' => $order->clientInformation ? $order->clientInformation->added_by : auth()->id(),
                'modified_by' => auth()->id()
            ])
        );

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
            'data' => $order->load(['clientInformation'])
        ]);
    }
}
