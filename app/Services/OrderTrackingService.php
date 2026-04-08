<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class OrderTrackingService
{
    /**
     * Update order tracking details.
     */
    public function updateTracking(Order $order, array $data): Order
    {
        $stageMap = [
            'order_details' => 'clientInformation',
            'client_info' => 'clientInformation',
            'card_specs' => 'clientInformation',
            'work_assign' => 'designing',
            'design_print' => 'designing',
            'printing_status' => 'printing',
            'packaging_logistics' => 'packaging',
            'packaging_status' => 'packaging',
            'delivery_location' => 'dispatchDelivery',
            'dispatch_mode' => 'dispatchDelivery',
            'dispatch_details' => 'dispatchDelivery',
        ];

        // Handle sticker image upload for designing stage
        if (isset($data['sticker_image']) && $data['sticker_image'] instanceof UploadedFile) {
            $file = $data['sticker_image'];
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $uploadPath = public_path('uploads/stickers');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true);
            }
            $file->move($uploadPath, $filename);
            
            $order->designing()->updateOrCreate(
                ['order_id' => $order->id],
                ['sticker_image' => 'uploads/stickers/' . $filename]
            );
        }

        DB::transaction(function () use ($order, $data, $stageMap) {
            $updatesByStage = [];
            foreach ($data as $section => $content) {
                if (isset($stageMap[$section])) {
                    $stageRelation = $stageMap[$section];
                    if (!isset($updatesByStage[$stageRelation])) {
                        $updatesByStage[$stageRelation] = [];
                    }

                    // Handle JSON strings from FormData
                    if (is_string($content)) {
                        $decoded = json_decode($content, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $content = $decoded;
                        }
                    }

                    // Strip audit details from the JSON data as requested
                    if (is_array($content)) {
                        unset($content['_audit']);
                    }
                    
                    $updatesByStage[$stageRelation][$section] = $content;
                }
            }

            // Handle direct status updates for a specific stage
            if (isset($data['status'])) {
                $relation = $data['_stage'] ?? null;
                
                // Fallback induction for _stage if not provided
                if (!$relation) {
                    foreach ($data as $key => $val) {
                        if (isset($stageMap[$key])) {
                            $relation = $stageMap[$key];
                            break;
                        }
                    }
                }

                if ($relation) {
                    $status = $data['status'];
                    $order->{$relation}()->updateOrCreate(
                        ['order_id' => $order->id],
                        [
                            'status' => $status,
                            'modified_by' => auth()->id()
                        ]
                    );

                    // Sequential Stage Triggering
                    if ($status === 'Completed') {
                        if ($relation === 'clientInformation') {
                            $order->designing()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        } elseif ($relation === 'designing') {
                            $order->printing()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        } elseif ($relation === 'printing') {
                            $order->packaging()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        } elseif ($relation === 'packaging') {
                            $order->dispatchDelivery()->firstOrCreate(['order_id' => $order->id], ['status' => 'Pending']);
                        }
                    }
                }
            }

            // Sync delivery date to orders table from either client_info or order_details
            $clientInfoUpdates = $updatesByStage['clientInformation'] ?? [];
            $newDeliveryDate = $clientInfoUpdates['order_details']['expected_delivery_date'] 
                ?? $clientInfoUpdates['client_info']['expected_delivery_date'] 
                ?? null;

            if ($newDeliveryDate) {
                // Ensure date format is correct for database if it's an ISO string from frontend
                $formattedDate = date('Y-m-d H:i:s', strtotime($newDeliveryDate));
                $order->update(['delivery_date' => $formattedDate]);
            }

            foreach ($updatesByStage as $relation => $sectionData) {
                $order->{$relation}()->updateOrCreate(
                    ['order_id' => $order->id],
                    array_merge($sectionData, [
                        'modified_by' => auth()->id()
                    ])
                );
            }

            if (isset($data['payment_info'])) {
                $paymentInfo = $data['payment_info'];
                $payments = $paymentInfo['payments'] ?? $paymentInfo;
                if (!is_array($payments)) $payments = [$payments];

                $existingPaymentIds = $order->payments()->where('payment_status', 'completed')->pluck('id')->toArray();
                $processedIds = [];

                foreach ($payments as $payInfo) {
                    $details = $payInfo['payment_details'] ?? [];
                    if (empty($details) && isset($payInfo['amount'])) {
                        // Fallback/Legacy mapping if frontend sends old structure
                        $details = [[
                            'method' => $payInfo['payment_method'] ?? 'cash',
                            'transaction_id' => $payInfo['transaction_id'] ?? null,
                            'amount' => $payInfo['amount'] ?? 0
                        ]];
                    }

                    if (empty($details)) continue;

                    $paymentData = [
                        'signature_name' => $payInfo['signature_name'] ?? null, 
                        'payment_status' => 'completed', 
                        'payment_date' => $payInfo['payment_date'] ?? now(), 
                        'payment_details' => $details,
                        'added_by' => auth()->id(), 
                        'modified_by' => auth()->id()
                    ];

                    if (isset($payInfo['id'])) {
                        $payment = $order->payments()->find($payInfo['id']);
                        if ($payment) { 
                            $payment->update($paymentData); 
                            $processedIds[] = $payment->id; 
                        }
                    } else {
                        $paymentData['payment_number'] = 'TEMP-' . time() . '-' . rand(1000, 9999);
                        $newPayment = $order->payments()->create($paymentData);
                        $newPayment->update(['payment_number' => 'PAY' . $newPayment->id . '-' . date('dmY')]);
                        $processedIds[] = $newPayment->id;
                    }
                }

                $idsToDelete = array_diff($existingPaymentIds, $processedIds);
                if (!empty($idsToDelete)) $order->payments()->whereIn('id', $idsToDelete)->delete();

                $order->updatePaymentStatus();
            }

            // Update total amount based on dispatch expense and other factors
            $order->refresh();
            $expense = (float) ($order->dispatchDelivery->dispatch_mode['expense'] ?? 0);
            $totalAmount = max(0, $order->net_amount + ($order->extra_charges ?? 0) - $order->discount + $expense);
            
            $order->update([
                'total_amount' => $totalAmount, 
                'modified_by' => auth()->id()
            ]);

            // Sync all financial fields (paid_amount, balance_due, payment_status)
            $order->updatePaymentStatus();
        });

        return $order->load([
            'items.product', 'customerDetails', 'coupon',
            'clientInformation.modifiedBy',
            'designing.modifiedBy',
            'printing.modifiedBy',
            'packaging.modifiedBy',
            'dispatchDelivery.modifiedBy',
            'payments.addedBy', 'payments.modifiedBy'
        ]);
    }
}