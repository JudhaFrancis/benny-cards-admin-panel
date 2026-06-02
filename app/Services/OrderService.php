<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;



class OrderService
{
    public function __construct(
        protected OrderTrackingService $trackingService
    ) {
    }

    /**
     * Update order tracking details.
     */
    public function updateTracking(Order $order, array $data): Order
    {
        return $this->trackingService->updateTracking($order, $data);
    }
    /**
     * Get paginated orders with filters.
     */
    public function listOrders(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $user = auth()->user();
        $userRole = strtolower($user->role?->name ?? '');
        $isSuperAdmin = $userRole === 'super-admin';
        $isAdmin = $userRole === 'admin';
        $isStaff = $user && !$isSuperAdmin && !$isAdmin;

        return Order::query()
            ->with([
                'user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails',
                'coupon', 'clientInformation.modifiedBy', 'designing.modifiedBy',
                'printing.modifiedBy', 'packaging.modifiedBy', 'dispatchDelivery.modifiedBy',
                'payments.addedBy', 'payments.modifiedBy'
            ])
            ->when(isset($filters['stage']), function (Builder $query) use ($filters, $isSuperAdmin, $isAdmin, $user) {
                $stage = $filters['stage'];
                $stageRelation = match ($stage) {
                    'client-information' => 'clientInformation',
                    'designing' => 'designing',
                    'printing' => 'printing',
                    'packaging' => 'packaging',
                    'delivery' => 'dispatchDelivery',
                    default => null
                };

                $stageTable = match ($stage) {
                    'client-information' => 'order_client_information',
                    'designing' => 'order_designing',
                    'printing' => 'order_printing',
                    'packaging' => 'order_packaging',
                    'delivery' => 'order_dispatch_delivery',
                    default => null
                };

                if ($stageTable) {
                    $query->leftJoin($stageTable, 'orders.id', '=', $stageTable . '.order_id')
                          ->select('orders.*')
                          ->orderByRaw("CASE 
                                WHEN {$stageTable}.status = 'Pending' OR {$stageTable}.status IS NULL THEN 1 
                                WHEN {$stageTable}.status = 'Process' THEN 2 
                                ELSE 3 END");
                }

                if ($stageRelation) {
                    $query->where(function($q) use ($stageRelation, $filters, $stage) {
                        // 1. Existing records in the stage
                        $q->whereHas($stageRelation, function ($sub) use ($filters, $stage) {
                            // Status Filter (Multi-select)
                            $statusValue = data_get($filters, 'computed_stage_status') ?: (data_get($filters, 'status') ?: data_get($filters, 'resolved_status'));
                            $statusValues = array_filter(is_array($statusValue) ? $statusValue : ($statusValue ? [$statusValue] : []));
                            
                            if (!empty($statusValues) && !in_array('all', $statusValues)) {
                                $sub->whereIn('status', $statusValues);
                            } else {
                                // Default: Exclude Completed if no specific filter is selected
                                $sub->where('status', '!=', 'Completed');
                            }

                            // Date Filter (Stage-specific)
                            $assignedDate = data_get($filters, 'computed_assigned_date') ?: data_get($filters, 'assigned_date') ?: data_get($filters, 'date');
                            if ($assignedDate) {
                                if ($stage === 'designing') {
                                    $sub->where('work_assign->assigned_date', $assignedDate);
                                } elseif ($stage === 'printing') {
                                    $sub->where(function($q) use ($assignedDate) {
                                        $q->where('printing_status->confirmed_date', $assignedDate)
                                          ->orWhere('printing_status->assigned_date', $assignedDate);
                                    });
                                } elseif ($stage === 'packaging') {
                                    $sub->where('packaging_logistics->date', $assignedDate);
                                } elseif ($stage === 'delivery') {
                                    $sub->where('dispatch_mode->date', $assignedDate);
                                } else {
                                    $sub->whereDate('updated_at', $assignedDate);
                                }
                            }
                        });

                        // 2. Client Info Special: Include "New Orders"
                        if ($stage === 'client-information') {
                            $statusValue = data_get($filters, 'computed_stage_status') ?: (data_get($filters, 'resolved_status') ?: data_get($filters, 'status'));
                            $statusValues = array_filter(is_array($statusValue) ? $statusValue : ($statusValue ? [$statusValue] : []));
                            
                            $includeNew = empty($statusValues) || in_array('all', $statusValues) || 
                                         in_array('Pending', $statusValues) || in_array('New Order', $statusValues);
                            
                            if ($includeNew) {
                                $q->orWhereDoesntHave($stageRelation);
                            }
                        }
                    });
                }

                // Restriction for Staff
                if (!$isSuperAdmin && !$isAdmin) {
                    $userId = $user->id;
                    $query->where(function ($q) use ($userId, $stageRelation) {
                        $q->where('added_by', $userId);
                        if ($stageRelation) {
                            $q->orWhereHas($stageRelation, fn($sub) => $sub->whereJsonContains('assigned_user_ids', (int) $userId));
                        }
                    });
                }
            })
            ->when(!isset($filters['stage']), function (Builder $query) use ($filters) {
                $statusValue = data_get($filters, 'resolved_status') ?: data_get($filters, 'status');
                if ($statusValue && $statusValue !== 'all') {
                    $statusLower = strtolower($statusValue);
                    
                    match ($statusLower) {
                        'delivered' => $query->whereHas('dispatchDelivery', fn($q) => $q->where('status', 'Completed')),
                        'out for delivery' => $query->whereHas('dispatchDelivery', fn($q) => $q->where('status', 'Process')),
                        
                        'packed' => $query->whereHas('packaging', fn($q) => $q->where('status', 'Completed'))
                                          ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                        
                        'packing in progress' => $query->whereHas('packaging', fn($q) => $q->where('status', 'Process'))
                                                       ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                        
                        'printed' => $query->whereHas('printing', fn($q) => $q->where('status', 'Completed'))
                                           ->whereDoesntHave('packaging', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                           ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                        
                        'printing in progress' => $query->whereHas('printing', fn($q) => $q->where('status', 'Process'))
                                                        ->whereDoesntHave('packaging', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                                        ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                                                        
                        'designed' => $query->whereHas('designing', fn($q) => $q->where('status', 'Completed'))
                                            ->whereDoesntHave('printing', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                            ->whereDoesntHave('packaging', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                            ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                                            
                        'designing in progress' => $query->whereHas('designing', fn($q) => $q->where('status', 'Process'))
                                                         ->whereDoesntHave('printing', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                                         ->whereDoesntHave('packaging', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                                         ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                                                         
                        'confirmed' => $query->whereHas('clientInformation', fn($q) => $q->where('status', 'Completed'))
                                             ->whereDoesntHave('designing', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                             ->whereDoesntHave('printing', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                             ->whereDoesntHave('packaging', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                                             ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process'])),
                                             
                        'new order' => $query->where(function($q) {
                            $q->where(function($sub) {
                                $sub->whereDoesntHave('clientInformation')
                                    ->orWhereHas('clientInformation', fn($ss) => $ss->where('status', '!=', 'Completed'));
                            })
                            ->whereDoesntHave('designing', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                            ->whereDoesntHave('printing', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                            ->whereDoesntHave('packaging', fn($q) => $q->whereIn('status', ['Completed', 'Process']))
                            ->whereDoesntHave('dispatchDelivery', fn($q) => $q->whereIn('status', ['Completed', 'Process']));
                        }),
                        default => $query->where('status', $statusValue)
                    };
                }
            })
            ->when(data_get($filters, 'payment_status'), function (Builder $query, $value) {
                if ($value !== 'all') $query->where('payment_status', $value);
            })
            ->when(data_get($filters, 'branch') ?: data_get($filters, 'client_information_order_details_order_placed_in'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                if ($isNa) {
                    $query->whereHas('clientInformation', fn($sub) => $sub->whereNull('order_details->order_placed_in')->orWhere('order_details->order_placed_in', ''));
                } elseif ($value !== 'All Branches' && $value !== 'all') {
                    $query->whereHas('clientInformation', fn($sub) => $sub->where('order_details->order_placed_in', $value));
                }
            })
            ->when(data_get($filters, 'customer_details_phone') ?: data_get($filters, 'customer_details.phone'), function (Builder $query, $value) {
                $query->whereHas('customerDetails', fn($sub) => $sub->where('phone', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'search'), function (Builder $query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('order_number', 'like', "%{$value}%")
                        ->orWhereHas('customerDetails', fn($sub) => $sub->where('name', 'like', "%{$value}%")->orWhere('phone', 'like', "%{$value}%"));
                });
            })
            ->when(data_get($filters, 'start_date'), fn($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when(data_get($filters, 'end_date'), fn($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->when(data_get($filters, 'order_number'), fn($q, $v) => $q->where('order_number', 'like', "%{$v}%"))
            ->when(data_get($filters, 'customer_details_name') ?: data_get($filters, 'customer_details.name'), function (Builder $query, $value) {
                $query->whereHas('customerDetails', fn($q) => $q->where('name', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'order_date'), fn($q, $v) => $q->whereDate('order_date', $v))
            ->when(data_get($filters, 'items_count'), fn($q, $v) => $q->where('items_count', $v))
            ->when(data_get($filters, 'client_information_card_specs_quantity') ?: data_get($filters, 'client_information.card_specs.quantity'), function (Builder $query, $value) {
                $query->whereHas('clientInformation', fn($sub) => $sub->where('card_specs->quantity', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'client_information_card_specs_type') ?: data_get($filters, 'client_information.card_specs.type'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                if ($isNa) {
                    $query->whereHas('clientInformation', fn($sub) => $sub->whereNull('card_specs->type')->orWhere('card_specs->type', ''));
                } elseif ($value !== 'all' && $value !== 'All Types') {
                    $query->whereHas('clientInformation', function($sub) use ($value) {
                        $sub->where(function($q) use ($value) {
                            $q->where('card_specs->type', $value)
                              ->orWhere('card_specs->type', 'like', "{$value},%")
                              ->orWhere('card_specs->type', 'like', "%,{$value}")
                              ->orWhere('card_specs->type', 'like', "%,{$value},%");
                        });
                    });
                }
            })
            ->when(data_get($filters, 'client_information_card_specs_card_options') ?: data_get($filters, 'client_information.card_specs.card_options'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('clientInformation', fn($sub) => $isNa ? $sub->whereNull('card_specs->card_options')->orWhere('card_specs->card_options', '') : $sub->where('card_specs->card_options', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'designing_work_assign_deadline_hours'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->deadline_hours')->orWhere('work_assign->deadline_hours', '') : $sub->where('work_assign->deadline_hours', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'designing_work_assign_assigned_to') ?: data_get($filters, 'designing.work_assign.assigned_to'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->assigned_to')->orWhere('work_assign->assigned_to', '') : $sub->where('work_assign->assigned_to', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'designing_work_assign_assigned_date') ?: data_get($filters, 'designing.work_assign.assigned_date'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->assigned_date')->orWhere('work_assign->assigned_date', '') : $sub->where('work_assign->assigned_date', $value));
            })
            ->when(data_get($filters, 'designing_work_assign_completed_date') ?: data_get($filters, 'designing.work_assign.completed_date'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->completed_date')->orWhere('work_assign->completed_date', '') : $sub->where('work_assign->completed_date', $value));
            })
            ->when(data_get($filters, 'client_information_order_details_order_taken_by') ?: data_get($filters, 'client_information.order_details.order_taken_by'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('clientInformation', fn($sub) => $isNa ? $sub->whereNull('order_details->order_taken_by')->orWhere('order_details->order_taken_by', '') : $sub->where('order_details->order_taken_by', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'printing_printing_status_assigned_to') ?: data_get($filters, 'printing.printing_status.assigned_to'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->where(function($q) use ($value, $isNa) {
                    if ($isNa) {
                        $q->whereHas('printing', fn($sub) => $sub->whereNull('printing_status->assigned_to')->orWhere('printing_status->assigned_to', ''))
                          ->orWhereHas('designing', fn($sub) => $sub->whereNull('work_assign->assigned_to')->orWhere('work_assign->assigned_to', ''));
                    } else {
                        $q->whereHas('printing', fn($sub) => $sub->where('printing_status->assigned_to', 'like', "%{$value}%"))
                          ->orWhereHas('designing', fn($sub) => $sub->where('work_assign->assigned_to', 'like', "%{$value}%"));
                    }
                });
            })
            ->when(data_get($filters, 'printing_printing_status_assigned_date') ?: data_get($filters, 'printing.printing_status.assigned_date'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->where(function($q) use ($value, $isNa) {
                    $q->whereHas('printing', fn($sub) => $sub->where(function($subQ) use ($value, $isNa) {
                        if ($isNa) {
                            $subQ->whereNull('printing_status->confirmed_date')->orWhere('printing_status->confirmed_date', '')
                                 ->orWhereNull('printing_status->assigned_date')->orWhere('printing_status->assigned_date', '');
                        } else {
                            $subQ->where('printing_status->confirmed_date', $value)->orWhere('printing_status->assigned_date', $value);
                        }
                    }));
                });
            })
            ->when(data_get($filters, 'computed_printing_days_status'), function (Builder $query, $value) {
                $query->whereHas('printing', function ($q) use ($value) {
                    $operator = $value === 'On Time' ? '<=' : '>';
                    $q->whereRaw("DATEDIFF(CURDATE(), COALESCE(JSON_UNQUOTE(JSON_EXTRACT(printing_status, '$.confirmed_date')), JSON_UNQUOTE(JSON_EXTRACT(printing_status, '$.assigned_date')))) {$operator} 7");
                });
            })
            ->when(data_get($filters, 'computed_sent_to_print_date'), function (Builder $query, $value) {
                $query->whereHas('printing', function ($q) use ($value) {
                    $q->where(function ($sub) use ($value) {
                        $sub->where('printing_status->customize_sent_to_print_date', $value)
                            ->orWhere('printing_status->semi_customize_sent_to_print_date', $value)
                            ->orWhere('printing_status->ready_made_sent_to_print_date', $value)
                            ->orWhere('printing_status->digital_local_sent_to_print_date', $value);
                    });
                });
            })
            ->when(data_get($filters, 'packaging_packaging_logistics_crafted_by') ?: data_get($filters, 'packaging.packaging_logistics.crafted_by'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                if ($isNa) {
                    $query->where(function($q) {
                        $q->whereDoesntHave('packaging')
                          ->orWhereHas('packaging', function($sub) {
                              $sub->where(function($subQ) {
                                  $subQ->whereNull('packaging_logistics->crafted_by')
                                       ->orWhere('packaging_logistics->crafted_by', '');
                              })->where(function($subQ) {
                                  $subQ->whereNull('packaging_logistics->assigned_by_multiple')
                                       ->orWhere('packaging_logistics->assigned_by_multiple', '[]')
                                       ->orWhere('packaging_logistics->assigned_by_multiple', '""');
                              })->where(function($subQ) {
                                  $subQ->whereNull('packaging_logistics->crafted_by_multiple')
                                       ->orWhere('packaging_logistics->crafted_by_multiple', '[]')
                                       ->orWhere('packaging_logistics->crafted_by_multiple', '""');
                              });
                          });
                    });
                } else {
                    $query->whereHas('packaging', function($sub) use ($value) {
                        $sub->where('packaging_logistics->crafted_by', 'like', "%{$value}%")
                            ->orWhere('packaging_logistics->assigned_by_multiple', 'like', "%{$value}%")
                            ->orWhere('packaging_logistics->crafted_by_multiple', 'like', "%{$value}%");
                    });
                }
            })
            ->when(data_get($filters, 'packaging_packaging_logistics_date') ?: data_get($filters, 'packaging.packaging_logistics.date'), function (Builder $query, $value) {
                $query->whereHas('packaging', fn($sub) => $sub->where('packaging_logistics->date', $value));
            })
            ->when(data_get($filters, 'dispatch_delivery_dispatch_mode_signature_name') ?: data_get($filters, 'dispatch_delivery.dispatch_mode.signature_name'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('dispatchDelivery', fn($sub) => $isNa ? $sub->whereNull('dispatch_mode->signature_name')->orWhere('dispatch_mode->signature_name', '') : $sub->where('dispatch_mode->signature_name', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'dispatch_delivery_dispatch_mode_packed_by') ?: data_get($filters, 'dispatch_delivery.dispatch_mode.packed_by'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('dispatchDelivery', fn($sub) => $isNa ? $sub->whereNull('dispatch_mode->packed_by')->orWhere('dispatch_mode->packed_by', '') : $sub->where('dispatch_mode->packed_by', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'dispatch_delivery_dispatch_mode_date') ?: data_get($filters, 'dispatch_delivery.dispatch_mode.date'), function (Builder $query, $value) {
                $query->whereHas('dispatchDelivery', fn($sub) => $sub->where('dispatch_mode->date', $value));
            })
            ->when(data_get($filters, 'designing_design_print_design_outputs'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('design_print->design_outputs')->orWhere('design_print->design_outputs', '') : $sub->where('design_print->design_outputs', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'printing_printing_status_company_name'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('printing', fn($sub) => $isNa ? $sub->whereNull('printing_status->company_name')->orWhere('printing_status->company_name', '') : $sub->where('printing_status->company_name', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'delivery_date'), function (Builder $query, $value) {
                $query->whereDate('delivery_date', $value);
            })
            ->when(data_get($filters, 'packaging_packaging_logistics_qty_cards'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('packaging', fn($sub) => $isNa ? $sub->whereNull('packaging_logistics->qty_cards')->orWhere('packaging_logistics->qty_cards', '') : $sub->where('packaging_logistics->qty_cards', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'dispatch_delivery_dispatch_mode_gift_type'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('dispatchDelivery', fn($sub) => $isNa ? $sub->whereNull('dispatch_mode->gift_type')->orWhere('dispatch_mode->gift_type', '') : $sub->where('dispatch_mode->gift_type', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'dispatch_delivery_delivery_location_place_name'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->where(function($q) use ($value, $isNa) {
                    if ($isNa) {
                        $q->whereHas('dispatchDelivery', fn($sub) => $sub->whereNull('delivery_location->place_name')->orWhere('delivery_location->place_name', ''))
                          ->orWhereHas('clientInformation', fn($sub) => $sub->whereNull('client_info->address')->orWhere('client_info->address', ''));
                    } else {
                        $q->whereHas('dispatchDelivery', fn($sub) => $sub->where('delivery_location->place_name', 'like', "%{$value}%"))
                          ->orWhereHas('clientInformation', fn($sub) => $sub->where('client_info->address', 'like', "%{$value}%"));
                    }
                });
            })
            ->when(data_get($filters, 'dispatch_delivery_dispatch_mode_modes'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('dispatchDelivery', fn($sub) => $isNa ? $sub->whereNull('dispatch_mode->modes')->orWhere('dispatch_mode->modes', '') : $sub->where('dispatch_mode->modes', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'computed_assigned_name'), function (Builder $query, $value) use ($filters) {
                $stage = $filters['stage'] ?? null;
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                
                if ($stage === 'client-information') {
                    $query->whereHas('clientInformation', fn($sub) => $isNa ? $sub->whereNull('order_details->order_taken_by')->orWhere('order_details->order_taken_by', '') : $sub->where('order_details->order_taken_by', 'like', "%{$value}%"));
                } elseif ($stage === 'designing') {
                    $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->assigned_to')->orWhere('work_assign->assigned_to', '') : $sub->where('work_assign->assigned_to', 'like', "%{$value}%"));
                } elseif ($stage === 'printing') {
                    $query->where(function($q) use ($value, $isNa) {
                        if ($isNa) {
                            $q->whereHas('printing', fn($sub) => $sub->whereNull('printing_status->assigned_to')->orWhere('printing_status->assigned_to', ''))
                              ->orWhereHas('designing', fn($sub) => $sub->whereNull('work_assign->assigned_to')->orWhere('work_assign->assigned_to', ''));
                        } else {
                            $q->whereHas('printing', fn($sub) => $sub->where('printing_status->assigned_to', 'like', "%{$value}%"))
                              ->orWhereHas('designing', fn($sub) => $sub->where('work_assign->assigned_to', 'like', "%{$value}%"));
                        }
                    });
                } elseif ($stage === 'packaging') {
                    if ($isNa) {
                        $query->where(function($q) {
                            $q->whereDoesntHave('packaging')
                              ->orWhereHas('packaging', function($sub) {
                                  $sub->where(function($subQ) {
                                      $subQ->whereNull('packaging_logistics->crafted_by')
                                           ->orWhere('packaging_logistics->crafted_by', '');
                                  })->where(function($subQ) {
                                      $subQ->whereNull('packaging_logistics->assigned_by_multiple')
                                           ->orWhere('packaging_logistics->assigned_by_multiple', '[]')
                                           ->orWhere('packaging_logistics->assigned_by_multiple', '""');
                                  })->where(function($subQ) {
                                      $subQ->whereNull('packaging_logistics->crafted_by_multiple')
                                           ->orWhere('packaging_logistics->crafted_by_multiple', '[]')
                                           ->orWhere('packaging_logistics->crafted_by_multiple', '""');
                                  });
                              });
                        });
                    } else {
                        $query->whereHas('packaging', function($sub) use ($value) {
                            $sub->where('packaging_logistics->crafted_by', 'like', "%{$value}%")
                                ->orWhere('packaging_logistics->assigned_by_multiple', 'like', "%{$value}%")
                                ->orWhere('packaging_logistics->crafted_by_multiple', 'like', "%{$value}%");
                        });
                    }
                } elseif ($stage === 'delivery') {
                    $query->whereHas('dispatchDelivery', fn($sub) => $isNa ? $sub->whereNull('dispatch_mode->packed_by')->orWhere('dispatch_mode->packed_by', '') : $sub->where('dispatch_mode->packed_by', 'like', "%{$value}%"));
                }
            })
            ->when(data_get($filters, 'computed_completed_by'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->completed_by')->orWhere('work_assign->completed_by', '') : $sub->where('work_assign->completed_by', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'computed_process_status'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                if ($isNa) {
                    $query->whereHas('designing', function($sub) {
                        $sub->where(function($q) {
                            $q->whereNull('work_assign->content_received')
                              ->orWhere('work_assign->content_received', false)
                              ->orWhere('work_assign->content_received', '0');
                        })->where(function($q) {
                            $q->whereNull('work_assign->content_not_received')
                              ->orWhere('work_assign->content_not_received', false)
                              ->orWhere('work_assign->content_not_received', '0');
                        });
                    });
                } elseif ($value === 'Content Received') {
                    $query->whereHas('designing', fn($sub) => $sub->where('work_assign->content_received', true)->orWhere('work_assign->content_received', '1'));
                } elseif ($value === 'Content Not Received') {
                    $query->whereHas('designing', fn($sub) => $sub->where('work_assign->content_not_received', true)->orWhere('work_assign->content_not_received', '1'));
                }
            })
            ->when(data_get($filters, 'created_at'), function (Builder $query, $value) {
                $query->whereDate('created_at', $value);
            })
            ->when(data_get($filters, 'computed_modified_at'), function (Builder $query, $value) {
                $query->whereDate('updated_at', $value);
            })
            ->when(data_get($filters, 'client_information_updated_at') ?: data_get($filters, 'client_information.updated_at'), function (Builder $query, $value) {
                $query->whereHas('clientInformation', fn($sub) => $sub->whereDate('updated_at', $value));
            })
            ->when(data_get($filters, 'designing_updated_at') ?: data_get($filters, 'designing.updated_at'), function (Builder $query, $value) {
                $query->whereHas('designing', fn($sub) => $sub->whereDate('updated_at', $value));
            })
            ->when(data_get($filters, 'printing_updated_at') ?: data_get($filters, 'printing.updated_at'), function (Builder $query, $value) {
                $query->whereHas('printing', fn($sub) => $sub->whereDate('updated_at', $value));
            })
            ->when(data_get($filters, 'packaging_updated_at') ?: data_get($filters, 'packaging.updated_at'), function (Builder $query, $value) {
                $query->whereHas('packaging', fn($sub) => $sub->whereDate('updated_at', $value));
            })
            ->when(data_get($filters, 'dispatch_delivery_updated_at') ?: data_get($filters, 'dispatch_delivery.updated_at'), function (Builder $query, $value) {
                $query->whereHas('dispatchDelivery', fn($sub) => $sub->whereDate('updated_at', $value));
            })
            ->when(!isset($filters['stage']), fn($q) => $q->latest())
            ->when(isset($filters['stage']), fn($q) => $q->orderBy('orders.created_at', 'desc'))
            ->paginate($perPage);
    }

    /**
     * Get a single order with its relations.
     */
    public function getOrder(int $id): Order
    {
        return Order::with([
            'user', 'items.product', 'addedBy', 'modifiedBy', 'customerDetails',
            'coupon', 'clientInformation.modifiedBy', 'designing.modifiedBy',
            'printing.modifiedBy', 'packaging.modifiedBy', 'dispatchDelivery.modifiedBy',
            'payments.addedBy', 'payments.modifiedBy'
        ])->findOrFail($id);
    }



    /**
     * Create a new order with items and customer details.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $itemsData = $this->prepareOrderItems($data['items'] ?? []);
            $totals = $this->calculateOrderTotals($itemsData);

            $discount = $data['discount'] ?? 0;
            $extraCharges = $data['extra_charges'] ?? 0;
            $totalAmount = (float) max(0, $totals['net_amount'] + $extraCharges - $discount);
            $paidAmount = $data['paid_amount'] ?? 0;

            $order = Order::create([
                'order_number' => 'TEMP-' . uniqid(),
                'order_date' => $data['order_date'] ?? now(),
                'delivery_date' => $data['delivery_date'] ?? ($data['customer']['expected_delivery_date'] ?? null),
                'user_id' => $data['user_id'] ?? auth()->id(),
                'items_count' => $totals['items_count'],
                'total_quantity' => $totals['total_quantity'],
                'net_amount' => $totals['net_amount'],
                'coupons_id' => $data['coupon_id'] ?? null,
                'discount' => $discount,
                'extra_charges' => $extraCharges,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_due' => (float) max(0, $totalAmount - $paidAmount),
                'payment_status' => $paidAmount <= 0 ? 'unpaid' : ($paidAmount < $totalAmount ? 'due' : 'paid'),
                'status' => $data['status'] ?? 'pending',
                'added_by' => auth()->id(),
                'modified_by' => auth()->id(),
            ]);

            $order->update([
                'order_number' => 'ORD' . $order->id . '-' . date('dmY'),
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_image' => $item['product_image'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total_price' => $item['total_price'],
                ]);
            }

            if (!empty($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks']))
                    $customerData['remarks'] = $data['remarks'];
                $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $customerData);
            }

            // Initialize first stage tracking
            $order->clientInformation()->create([
                'status' => 'Pending',
                'modified_by' => auth()->id()
            ]);

            return $order->load([
                'items.product',
                'customerDetails',
                'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy',
                'payments.modifiedBy'
            ]);
        });
    }

    /**
     * Update an existing order.
     */
    public function updateOrder(Order $order, array $data): Order
    {
        return DB::transaction(function () use ($order, $data) {
            if (isset($data['items'])) {
                $order->items()->delete();
                $itemsData = $this->prepareOrderItems($data['items']);
                foreach ($itemsData as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'product_image' => $item['product_image'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'total_price' => $item['total_price'],
                    ]);
                }
                $totals = $this->calculateOrderTotals($itemsData);
                $order->items_count = $totals['items_count'];
                $order->total_quantity = $totals['total_quantity'];
                $order->net_amount = $totals['net_amount'];
            }

            if (isset($data['discount']))
                $order->discount = $data['discount'];
            if (isset($data['extra_charges']))
                $order->extra_charges = $data['extra_charges'];
            if (isset($data['paid_amount']))
                $order->paid_amount = $data['paid_amount'];

            $expense = (float) ($order->dispatchDelivery->dispatch_mode['expense'] ?? 0);
            $order->total_amount = max(0, $order->net_amount + ($order->extra_charges ?? 0) - $order->discount + $expense);

            if (isset($data['coupon_id']))
                $order->coupons_id = $data['coupon_id'];

            if (isset($data['customer'])) {
                $customerData = $data['customer'];
                if (isset($data['remarks']))
                    $customerData['remarks'] = $data['remarks'];
                $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $customerData);
            }

            if (isset($data['order_date']))
                $order->order_date = $data['order_date'];
            if (isset($data['delivery_date'])) {
                $order->delivery_date = $data['delivery_date'];
            } elseif (isset($data['customer']['expected_delivery_date'])) {
                $order->delivery_date = $data['customer']['expected_delivery_date'];
            }

            $order->updatePaymentStatus();
            $order->modified_by = auth()->id();
            $order->save();

            return $order->load([
                'items.product',
                'customerDetails',
                'coupon',
                'clientInformation.modifiedBy',
                'designing.modifiedBy',
                'printing.modifiedBy',
                'packaging.modifiedBy',
                'dispatchDelivery.modifiedBy',
                'payments.addedBy',
                'payments.modifiedBy'
            ]);
        });
    }

    /**
     * Soft delete an order.
     */
    public function deleteOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->customerDetails()->delete();
            $order->payments()->delete();
            return $order->delete();
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status, 'modified_by' => auth()->id()]);
        return $order;
    }

    /**
     * Update customer details.
     */
    public function updateCustomerDetails(Order $order, array $data): Order
    {
        $order->customerDetails()->updateOrCreate(['order_id' => $order->id], $data);
        $order->modified_by = auth()->id();
        $order->save();
        return $order->load(['items.product', 'customerDetails', 'payments', 'coupon', 'clientInformation', 'designing', 'printing', 'packaging', 'dispatchDelivery']);
    }




    protected function prepareOrderItems(array $items): array
    {
        $preparedItems = [];
        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $product = $productId ? Product::find($productId) : null;

            // If it's not a catalog product, we must have a product_name
            if (!$product && empty($item['product_name']))
                continue;

            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? ($product ? $product->price : 0);
            $discountAmount = $item['discount_amount'] ?? 0;
            $totalPrice = max(0, ($quantity * $unitPrice) - $discountAmount);

            $productImage = $item['product_image'] ?? ($product ? $product->image : null);

            // Handle manual item image upload
            if (isset($item['product_image']) && $item['product_image'] instanceof UploadedFile) {
                $file = $item['product_image'];
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                $uploadPath = public_path('uploads/orders');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true);
                }

                $file->move($uploadPath, $filename);
                $productImage = 'uploads/orders/' . $filename;
            }

            $preparedItems[] = [
                'product_id' => $product ? $product->id : null,
                'product_name' => $item['product_name'] ?? ($product ? $product->title : 'Unknown Product'),
                'product_image' => $productImage,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_price' => $totalPrice
            ];
        }
        return $preparedItems;
    }

    protected function calculateOrderTotals(array $items): array
    {
        $netAmount = 0;
        $totalQuantity = 0;
        foreach ($items as $item) {
            $netAmount += $item['total_price'];
            $totalQuantity += $item['quantity'];
        }
        return ['items_count' => count($items), 'total_quantity' => $totalQuantity, 'net_amount' => $netAmount];
    }
}