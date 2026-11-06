<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['order', 'addedBy']);

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('payment_number', 'like', "%{$search}%")
                        ->orWhere('payment_details', 'like', "%{$search}%")
                        ->orWhereHas('order', function ($o) use ($search) {
                            $o->where('order_number', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('payment_status', $request->status);
            }

            $payments = $query->latest()->paginate($request->get('limit', 10));

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_date' => 'required|date',
            'payment_status' => 'nullable|in:pending,completed,failed,refunded,cancelled',
            'payment_details' => 'required|array',
            'payment_details.*.method' => 'required|in:cash,card,upi,net_banking,qr_code,bank_transfer,cheque,wallet',
            'payment_details.*.amount' => 'required|numeric|min:0',
            'payment_details.*.transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'order_id' => $request->order_id,
                'payment_number' => 'TEMP-' . uniqid(),
                'payment_date' => $request->payment_date,
                'payment_status' => $request->payment_status ?? 'completed',
                'payment_details' => $request->payment_details,
                'notes' => $request->notes,
                'added_by' => auth()->id(),
            ]);

            // Update with real structured number using the payment ID
            $payment->update([
                'payment_number' => 'PAY' . $payment->id . '-' . date('dmY')
            ]);

            // Update order financial fields and status
            if ($payment->order_id) {
                $order = \App\Models\Order::find($payment->order_id);
                if ($order) {
                    $order->updatePaymentStatus();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $payment = Payment::with(['order', 'addedBy'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_date' => 'required|date',
            'payment_details' => 'required|array',
            'payment_details.*.method' => 'required|in:cash,card,upi,net_banking,qr_code,bank_transfer,cheque,wallet',
            'payment_details.*.amount' => 'required|numeric|min:0',
            'payment_details.*.transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::findOrFail($id);
            $payment->update($request->only([
                'payment_date',
                'payment_details',
                'notes'
            ]) + ['modified_by' => auth()->id()]);

            // Update order financial fields and status
            if ($payment->order_id) {
                $order = \App\Models\Order::find($payment->order_id);
                if ($order) {
                    $order->updatePaymentStatus();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
                'data' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            $orderId = $payment->order_id;
            $payment->delete();

            // Update order financial fields and status after deletion
            if ($orderId) {
                $order = \App\Models\Order::find($orderId);
                if ($order) {
                    $order->updatePaymentStatus();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment'
            ], 500);
        }
    }
}
