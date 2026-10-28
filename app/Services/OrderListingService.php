<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderListingService
{
    /**
     * Get paginated orders with filters.
     */
    public function listOrders(array $filters = [], int $perPage = 20)
    {
        // Normalize process status if passed inside computed_stage_status
        $stageStatus = data_get($filters, 'computed_stage_status');
        if (is_array($stageStatus)) {
            $processOptions = ['Content Received', 'Content Not Received'];
            $foundProcess = array_values(array_intersect($stageStatus, $processOptions));
            if (!empty($foundProcess)) {
                if (count($foundProcess) === 1 && !isset($filters['computed_process_status'])) {
                    $filters['computed_process_status'] = $foundProcess[0];
                }
                $filters['computed_stage_status'] = array_values(array_diff($stageStatus, $processOptions));
            }
        } elseif (is_string($stageStatus) && in_array($stageStatus, ['Content Received', 'Content Not Received'])) {
            if (!isset($filters['computed_process_status'])) {
                $filters['computed_process_status'] = $stageStatus;
            }
            unset($filters['computed_stage_status']);
        }

        $user = auth()->user();
        $userRole = strtolower($user->role?->name ?? '');
        $isSuperAdmin = $userRole === 'super-admin';
        $isAdmin = $userRole === 'admin';
        $isStaff = $user && !$isSuperAdmin && !$isAdmin;

        $query = Order::query()
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
                                ELSE 3 END")
                          ->orderByRaw("CASE 
                                WHEN orders.priority = 'P1' THEN 1
                                WHEN orders.budget_tier IN ('Platinum', 'Gold', 'Silver') OR orders.priority = 'P2' THEN 2
                                WHEN orders.priority = 'P3' THEN 3
                                ELSE 4 END");
                }

                if ($stageRelation) {
                    $query->where(function($q) use ($stageRelation, $filters, $stage) {
                        // 1. Existing records in the stage
                        $q->whereHas($stageRelation, function ($sub) use ($filters, $stage) {
                            // Status Filter (Multi-select)
                            if (!data_get($filters, 'ignore_status_filter')) {
                                $statusValue = data_get($filters, 'computed_stage_status') ?: (data_get($filters, 'status') ?: data_get($filters, 'resolved_status'));
                                $statusValues = array_filter(is_array($statusValue) ? $statusValue : ($statusValue ? [$statusValue] : []));
                                
                                if (!empty($statusValues) && !in_array('all', $statusValues)) {
                                    $sub->whereIn('status', $statusValues);
                                } else {
                                    // Default: Exclude Completed if no specific filter is selected
                                    $sub->where('status', '!=', 'Completed');
                                }
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
                            $includeNew = false;
                            if (data_get($filters, 'ignore_status_filter')) {
                                $includeNew = true;
                            } else {
                                $statusValue = data_get($filters, 'computed_stage_status') ?: (data_get($filters, 'resolved_status') ?: data_get($filters, 'status'));
                                $statusValues = array_filter(is_array($statusValue) ? $statusValue : ($statusValue ? [$statusValue] : []));
                                
                                $includeNew = empty($statusValues) || in_array('all', $statusValues) || 
                                             in_array('Pending', $statusValues) || in_array('New Order', $statusValues);
                            }
                            
                            if ($includeNew) {
                                $q->orWhereDoesntHave($stageRelation);
                            }
                        }
                    });
                }

                // Restriction for Staff
                if ($user && !$isSuperAdmin && !$isAdmin) {
                    $userId = $user->id;
                    $query->where(function ($q) use ($userId, $stageRelation) {
                        $q->where('added_by', $userId);
                        if ($stageRelation) {
                            $q->orWhereHas($stageRelation, fn($sub) => $sub->whereJsonContains('assigned_user_ids', (int) $userId));
                        }
                    });
                }
            })
            ->when(!isset($filters['stage']) && !data_get($filters, 'ignore_status_filter'), function (Builder $query) use ($filters) {
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
                $trimmed = trim($value);
                $cleanQty = trim(preg_replace('/^qty[:\s]*/i', '', $trimmed));
                $words = array_filter(preg_split('/\s+/', $trimmed), fn($w) => mb_strlen($w) > 0);

                $query->where(function ($q) use ($trimmed, $cleanQty, $words) {
                    $q->where('order_number', 'like', "%{$trimmed}%")
                        ->orWhere('budget_tier', 'like', "%{$trimmed}%")
                        ->orWhereHas('customerDetails', function ($sub) use ($trimmed, $words) {
                            $sub->where('name', 'like', "%{$trimmed}%")
                                ->orWhere('phone', 'like', "%{$trimmed}%");

                            if (count($words) > 1) {
                                $sub->orWhere(function ($wQ) use ($words) {
                                    foreach ($words as $w) {
                                        $wQ->where('name', 'like', "%{$w}%");
                                    }
                                });
                            }
                        })
                        ->orWhereHas('items', function ($sub) use ($trimmed, $words) {
                            $sub->where('product_name', 'like', "%{$trimmed}%")
                                ->orWhereHas('product', fn($p) => $p->where('title', 'like', "%{$trimmed}%"));

                            if (count($words) > 1) {
                                $sub->orWhere(function ($wQ) use ($words) {
                                    foreach ($words as $w) {
                                        $wQ->where('product_name', 'like', "%{$w}%");
                                    }
                                });
                            }
                        })
                        ->orWhereHas('clientInformation', fn($sub) => 
                            $sub->where('order_details->order_taken_by', 'like', "%{$trimmed}%")
                                ->orWhere('order_details->order_placed_in', 'like', "%{$trimmed}%")
                        )
                        ->orWhereHas('designing', fn($sub) => 
                            $sub->where('work_assign->assigned_to', 'like', "%{$trimmed}%")
                                ->orWhere('work_assign->content_by', 'like', "%{$trimmed}%")
                                ->orWhere('work_assign->completed_by', 'like', "%{$trimmed}%")
                        )
                        ->orWhereHas('printing', fn($sub) => $sub->where('printing_status->assigned_to', 'like', "%{$trimmed}%"))
                        ->orWhereHas('packaging', fn($sub) => 
                            $sub->where('packaging_logistics->crafted_by', 'like', "%{$trimmed}%")
                                ->orWhere('packaging_logistics->assigned_by_multiple', 'like', "%{$trimmed}%")
                                ->orWhere('packaging_logistics->crafted_by_multiple', 'like', "%{$trimmed}%")
                        )
                        ->orWhereHas('dispatchDelivery', fn($sub) => $sub->where('dispatch_mode->packed_by', 'like', "%{$trimmed}%"));

                    if ($cleanQty !== '' && (is_numeric($cleanQty) || $cleanQty !== $trimmed)) {
                        $q->orWhere('total_quantity', 'like', "%{$cleanQty}%")
                          ->orWhereHas('items', fn($sub) => $sub->where('quantity', 'like', "%{$cleanQty}%"));
                    }
                });
            })
            ->when(data_get($filters, 'start_date'), fn($q, $v) => $q->whereDate('order_date', '>=', $v))
            ->when(data_get($filters, 'end_date'), fn($q, $v) => $q->whereDate('order_date', '<=', $v))
            ->when(data_get($filters, 'order_number'), function (Builder $query, $v) {
                $trimmed = trim($v);
                $query->where(function ($q) use ($trimmed) {
                    $q->where('order_number', 'like', "%{$trimmed}%");
                    if (preg_match('/^(?:OD-)?([A-Za-z]{3})(\d+)$/i', $trimmed, $m)) {
                        $q->orWhere('order_number', 'like', "%{$m[1]}%{$m[2]}%");
                    }
                });
            })
            ->when(data_get($filters, 'customer_details_name') ?: (data_get($filters, 'customer_details.name') ?: (($filters['customer_details.name'] ?? null) ?: data_get($filters, 'customer'))), function (Builder $query, $value) {
                $trimmed = trim($value);
                $cleanQty = trim(preg_replace('/^qty[:\s]*/i', '', $trimmed));
                $words = array_filter(preg_split('/\s+/', $trimmed), fn($w) => mb_strlen($w) > 0);

                $query->where(function ($q) use ($trimmed, $cleanQty, $words) {
                    // 1. Customer Name and Phone (customerDetails table and clientInformation)
                    $q->whereHas('customerDetails', function ($sub) use ($trimmed, $words) {
                        $sub->where('name', 'like', "%{$trimmed}%")
                            ->orWhere('phone', 'like', "%{$trimmed}%");

                        if (count($words) > 1) {
                            $sub->orWhere(function ($wQ) use ($words) {
                                foreach ($words as $w) {
                                    $wQ->where('name', 'like', "%{$w}%");
                                }
                            });
                        }
                    })
                    ->orWhereHas('clientInformation', function ($sub) use ($trimmed, $words) {
                        $sub->where('client_info->name', 'like', "%{$trimmed}%")
                            ->orWhere('client_info->phone', 'like', "%{$trimmed}%")
                            ->orWhere('card_specs->title', 'like', "%{$trimmed}%")
                            ->orWhere('card_specs->specifications', 'like', "%{$trimmed}%")
                            ->orWhere('card_specs->type', 'like', "%{$trimmed}%");

                        if (count($words) > 1) {
                            $sub->orWhere(function ($wQ) use ($words) {
                                foreach ($words as $w) {
                                    $wQ->where('card_specs->title', 'like', "%{$w}%")
                                       ->orWhere('card_specs->specifications', 'like', "%{$w}%");
                                }
                            });
                        }
                    })
                    // 2. Product Name from items and products table
                    ->orWhereHas('items', function ($sub) use ($trimmed, $words) {
                        $sub->where('product_name', 'like', "%{$trimmed}%")
                            ->orWhereHas('product', fn($p) => $p->where('title', 'like', "%{$trimmed}%"));

                        if (count($words) > 1) {
                            $sub->orWhere(function ($wQ) use ($words) {
                                foreach ($words as $w) {
                                    $wQ->where('product_name', 'like', "%{$w}%");
                                }
                            });
                        }
                    });

                    // 3. Quantity matching
                    if ($cleanQty !== '') {
                        $q->orWhere('total_quantity', 'like', "%{$cleanQty}%")
                          ->orWhereHas('items', fn($sub) => $sub->where('quantity', 'like', "%{$cleanQty}%"))
                          ->orWhereHas('clientInformation', fn($sub) => $sub->where('card_specs->quantity', 'like', "%{$cleanQty}%"));
                    }
                });
            })
            ->when(data_get($filters, 'order_date') ?: data_get($filters, 'dates'), function (Builder $query, $value) {
                $trimmed = trim($value);
                $isNa = in_array(strtolower($trimmed), ['n/a', 'na', 'none']);
                $cleanVal = trim(preg_replace('/^(od|df|dd|ad|cd)[:\s]*/i', '', $trimmed));

                $query->where(function ($q) use ($trimmed, $cleanVal, $isNa) {
                    if ($isNa) {
                        $q->whereNull('delivery_date')
                          ->orWhereHas('designing', fn($sub) => $sub->whereNull('work_assign->completed_date')->orWhere('work_assign->completed_date', '')->orWhere('work_assign->completed_date', 'like', '%N/A%'));
                    } else {
                        $searchTerms = array_unique(array_filter([$trimmed, $cleanVal]));
                        foreach ($searchTerms as $term) {
                            if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $term, $m)) {
                                $isoDate = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                                $dmyDate = sprintf('%02d-%02d-%04d', $m[1], $m[2], $m[3]);
                                $q->orWhereDate('order_date', $isoDate)
                                  ->orWhereDate('delivery_date', $isoDate)
                                  ->orWhereHas('designing', fn($sub) => 
                                      $sub->where('work_assign->assigned_date', 'like', "%{$isoDate}%")
                                          ->orWhere('work_assign->assigned_date', 'like', "%{$dmyDate}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$isoDate}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$dmyDate}%")
                                  )
                                  ->orWhereHas('printing', fn($sub) => 
                                      $sub->where('printing_status->assigned_date', 'like', "%{$isoDate}%")
                                          ->orWhere('printing_status->assigned_date', 'like', "%{$dmyDate}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$isoDate}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$dmyDate}%")
                                  )
                                  ->orWhereHas('packaging', fn($sub) => 
                                      $sub->where('packaging_logistics->date', 'like', "%{$isoDate}%")
                                          ->orWhere('packaging_logistics->date', 'like', "%{$dmyDate}%")
                                  )
                                  ->orWhereHas('dispatchDelivery', fn($sub) => 
                                      $sub->where('dispatch_mode->date', 'like', "%{$isoDate}%")
                                          ->orWhere('dispatch_mode->date', 'like', "%{$dmyDate}%")
                                  );
                            } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $term, $m)) {
                                $isoDate = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                                $dmyDate = sprintf('%02d-%02d-%04d', $m[3], $m[2], $m[1]);
                                $q->orWhereDate('order_date', $isoDate)
                                  ->orWhereDate('delivery_date', $isoDate)
                                  ->orWhereHas('designing', fn($sub) => 
                                      $sub->where('work_assign->assigned_date', 'like', "%{$isoDate}%")
                                          ->orWhere('work_assign->assigned_date', 'like', "%{$dmyDate}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$isoDate}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$dmyDate}%")
                                  )
                                  ->orWhereHas('printing', fn($sub) => 
                                      $sub->where('printing_status->assigned_date', 'like', "%{$isoDate}%")
                                          ->orWhere('printing_status->assigned_date', 'like', "%{$dmyDate}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$isoDate}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$dmyDate}%")
                                  )
                                  ->orWhereHas('packaging', fn($sub) => 
                                      $sub->where('packaging_logistics->date', 'like', "%{$isoDate}%")
                                          ->orWhere('packaging_logistics->date', 'like', "%{$dmyDate}%")
                                  )
                                  ->orWhereHas('dispatchDelivery', fn($sub) => 
                                      $sub->where('dispatch_mode->date', 'like', "%{$isoDate}%")
                                          ->orWhere('dispatch_mode->date', 'like', "%{$dmyDate}%")
                                  );
                            } elseif (preg_match('/^(\d{1,2})-(\d{1,2})$/', $term, $m)) {
                                $dmyPart = sprintf('%02d-%02d', $m[1], $m[2]);
                                $ymdPart = sprintf('%02d-%02d', $m[2], $m[1]);
                                $q->orWhereRaw("DATE_FORMAT(order_date, '%d-%m') = ?", [$dmyPart])
                                  ->orWhereRaw("DATE_FORMAT(delivery_date, '%d-%m') = ?", [$dmyPart])
                                  ->orWhereHas('designing', fn($sub) => 
                                      $sub->where('work_assign->assigned_date', 'like', "%{$dmyPart}%")
                                          ->orWhere('work_assign->assigned_date', 'like', "%{$ymdPart}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$dmyPart}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$ymdPart}%")
                                  )
                                  ->orWhereHas('printing', fn($sub) => 
                                      $sub->where('printing_status->assigned_date', 'like', "%{$dmyPart}%")
                                          ->orWhere('printing_status->assigned_date', 'like', "%{$ymdPart}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$dmyPart}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$ymdPart}%")
                                  )
                                  ->orWhereHas('packaging', fn($sub) => 
                                      $sub->where('packaging_logistics->date', 'like', "%{$dmyPart}%")
                                          ->orWhere('packaging_logistics->date', 'like', "%{$ymdPart}%")
                                  )
                                  ->orWhereHas('dispatchDelivery', fn($sub) => 
                                      $sub->where('dispatch_mode->date', 'like', "%{$dmyPart}%")
                                          ->orWhere('dispatch_mode->date', 'like', "%{$ymdPart}%")
                                  );
                            } else {
                                $q->orWhereRaw("DATE_FORMAT(order_date, '%d-%m-%Y') like ?", ["%{$term}%"])
                                  ->orWhereRaw("DATE_FORMAT(delivery_date, '%d-%m-%Y') like ?", ["%{$term}%"])
                                  ->orWhereRaw("DATE_FORMAT(order_date, '%Y-%m-%d') like ?", ["%{$term}%"])
                                  ->orWhereRaw("DATE_FORMAT(delivery_date, '%Y-%m-%d') like ?", ["%{$term}%"])
                                  ->orWhereHas('designing', fn($sub) => 
                                      $sub->where('work_assign->assigned_date', 'like', "%{$term}%")
                                          ->orWhere('work_assign->completed_date', 'like', "%{$term}%")
                                  )
                                  ->orWhereHas('printing', fn($sub) => 
                                      $sub->where('printing_status->assigned_date', 'like', "%{$term}%")
                                          ->orWhere('printing_status->confirmed_date', 'like', "%{$term}%")
                                  )
                                  ->orWhereHas('packaging', fn($sub) => $sub->where('packaging_logistics->date', 'like', "%{$term}%"))
                                  ->orWhereHas('dispatchDelivery', fn($sub) => $sub->where('dispatch_mode->date', 'like', "%{$term}%"));
                            }
                        }
                    }
                });
            })
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
            ->when(data_get($filters, 'designing_work_assign_assign_timings'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->assign_timings')->orWhere('work_assign->assign_timings', '') : $sub->where('work_assign->assign_timings', 'like', "%{$value}%"));
            })
            ->when(data_get($filters, 'designing_work_assign_assigned_to') ?: data_get($filters, 'designing.work_assign.assigned_to'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $cleanVal = trim(preg_replace('/^(assigned by|content by|completed by)[:\s]*/i', '', $value));
                $query->whereHas('designing', function ($sub) use ($value, $cleanVal, $isNa) {
                    if ($isNa) {
                        $sub->where(function ($q) {
                            $q->whereNull('work_assign->assigned_to')->orWhere('work_assign->assigned_to', '');
                        })->where(function ($q) {
                            $q->whereNull('work_assign->content_by')->orWhere('work_assign->content_by', '');
                        })->where(function ($q) {
                            $q->whereNull('work_assign->completed_by')->orWhere('work_assign->completed_by', '');
                        });
                    } else {
                        $sub->where(function ($q) use ($value, $cleanVal) {
                            $q->where('work_assign->assigned_to', 'like', "%{$value}%")
                              ->orWhere('work_assign->content_by', 'like', "%{$value}%")
                              ->orWhere('work_assign->completed_by', 'like', "%{$value}%");
                            if ($cleanVal !== '') {
                                $q->orWhere('work_assign->assigned_to', 'like', "%{$cleanVal}%")
                                  ->orWhere('work_assign->content_by', 'like', "%{$cleanVal}%")
                                  ->orWhere('work_assign->completed_by', 'like', "%{$cleanVal}%");
                            }
                        });
                    }
                });
            })
            ->when(data_get($filters, 'designing_work_assign_assigned_date') ?: data_get($filters, 'designing.work_assign.assigned_date'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->assigned_date')->orWhere('work_assign->assigned_date', '') : $sub->where('work_assign->assigned_date', $value));
            })
            ->when(data_get($filters, 'designing_work_assign_completed_date') ?: data_get($filters, 'designing.work_assign.completed_date'), function (Builder $query, $value) {
                $isNa = strtolower(trim($value)) === 'n/a' || strtolower(trim($value)) === 'na';
                $query->whereHas('designing', fn($sub) => $isNa ? $sub->whereNull('work_assign->completed_date')->orWhere('work_assign->completed_date', '') : $sub->where('work_assign->completed_date', $value));
            })
            ->when(data_get($filters, 'client_information_order_details_order_taken_by') ?: (data_get($filters, 'client_information.order_details.order_taken_by') ?: (data_get($filters, 'orderSrc') ?: data_get($filters, 'order_src'))), function (Builder $query, $value) {
                $trimmed = trim($value);
                $isNa = strtolower($trimmed) === 'n/a' || strtolower($trimmed) === 'na';
                $cleanVal = trim(preg_replace('/^(by|in)[:\s]*/i', '', $trimmed));

                $query->where(function ($q) use ($trimmed, $cleanVal, $isNa) {
                    if ($isNa) {
                        $q->whereHas('clientInformation', function ($sub) {
                            $sub->whereNull('order_details->order_taken_by')->orWhere('order_details->order_taken_by', '')
                                ->orWhereNull('order_details->order_placed_in')->orWhere('order_details->order_placed_in', '');
                        });
                    } else {
                        $searchTerms = array_unique(array_filter([$trimmed, $cleanVal]));
                        foreach ($searchTerms as $term) {
                            $q->orWhereHas('clientInformation', function ($sub) use ($term) {
                                $sub->where('order_details->order_taken_by', 'like', "%{$term}%")
                                  ->orWhere('order_details->order_placed_in', 'like', "%{$term}%");
                            });
                        }
                    }
                });
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
                $query->whereHas('printing', fn($sub) => $isNa ? $sub->whereNull('printing_status->company_names')->orWhere('printing_status->company_names', '[]')->orWhere('printing_status->company_names', 'null') : $sub->where('printing_status->company_names', 'like', "%{$value}%"));
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
            ->when(data_get($filters, 'computed_assigned_name') ?: (data_get($filters, 'assigned_name') ?: (data_get($filters, 'designing_work_assign_assigned_to') ?: data_get($filters, 'designing.work_assign.assigned_to'))), function (Builder $query, $value) {
                $trimmed = trim($value);
                $isNa = strtolower($trimmed) === 'n/a' || strtolower($trimmed) === 'na';
                $cleanVal = trim(preg_replace('/^(assigned by|content by|completed by|crafted by|packed by|assigned to|by)[:\s]*/i', '', $trimmed));

                $query->where(function ($q) use ($trimmed, $cleanVal, $isNa) {
                    if ($isNa) {
                        $q->whereHas('designing', function ($sub) {
                            $sub->whereNull('work_assign->assigned_to')->orWhere('work_assign->assigned_to', '')
                                ->orWhereNull('work_assign->content_by')->orWhere('work_assign->content_by', '')
                                ->orWhereNull('work_assign->completed_by')->orWhere('work_assign->completed_by', '')
                                ->orWhere('work_assign->completed_by', 'like', '%N/A%')
                                ->orWhere('work_assign->content_by', 'like', '%N/A%')
                                ->orWhere('work_assign->assigned_to', 'like', '%N/A%');
                        })
                        ->orWhereHas('clientInformation', fn($sub) => $sub->whereNull('order_details->order_taken_by')->orWhere('order_details->order_taken_by', ''))
                        ->orWhereHas('printing', fn($sub) => $sub->whereNull('printing_status->assigned_to')->orWhere('printing_status->assigned_to', ''))
                        ->orWhereHas('packaging', fn($sub) => $sub->whereNull('packaging_logistics->crafted_by')->orWhere('packaging_logistics->crafted_by', ''))
                        ->orWhereHas('dispatchDelivery', fn($sub) => $sub->whereNull('dispatch_mode->packed_by')->orWhere('dispatch_mode->packed_by', ''));
                    } else {
                        $searchTerms = array_unique(array_filter([$trimmed, $cleanVal]));
                        foreach ($searchTerms as $term) {
                            $q->orWhereHas('designing', function ($sub) use ($term) {
                                $sub->where('work_assign->assigned_to', 'like', "%{$term}%")
                                    ->orWhere('work_assign->content_by', 'like', "%{$term}%")
                                    ->orWhere('work_assign->completed_by', 'like', "%{$term}%");
                            })
                            ->orWhereHas('clientInformation', fn($sub) => $sub->where('order_details->order_taken_by', 'like', "%{$term}%"))
                            ->orWhereHas('printing', fn($sub) => $sub->where('printing_status->assigned_to', 'like', "%{$term}%"))
                            ->orWhereHas('packaging', function ($sub) use ($term) {
                                $sub->where('packaging_logistics->crafted_by', 'like', "%{$term}%")
                                    ->orWhere('packaging_logistics->assigned_by_multiple', 'like', "%{$term}%")
                                    ->orWhere('packaging_logistics->crafted_by_multiple', 'like', "%{$term}%");
                            })
                            ->orWhereHas('dispatchDelivery', function ($sub) use ($term) {
                                $sub->where('dispatch_mode->packed_by', 'like', "%{$term}%")
                                    ->orWhere('dispatch_mode->signature_name', 'like', "%{$term}%");
                            });
                        }
                    }
                });
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
                $trimmed = trim($value);
                $cleanName = trim(preg_replace('/^by[:\s]*/i', '', $trimmed));

                $query->where(function ($q) use ($trimmed, $cleanName) {
                    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $trimmed, $m)) {
                        $isoDate = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                        $q->whereDate('orders.created_at', $isoDate);
                    } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $trimmed, $m)) {
                        $isoDate = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                        $q->whereDate('orders.created_at', $isoDate);
                    } elseif (preg_match('/^\d{1,2}-\d{1,2}$/', $trimmed)) {
                        $q->whereRaw("DATE_FORMAT(orders.created_at, '%d-%m') = ?", [$trimmed]);
                    } else {
                        $q->whereRaw("DATE_FORMAT(orders.created_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"]);
                    }

                    if ($cleanName !== '') {
                        $q->orWhereHas('addedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"));

                        if (stripos($cleanName, 'admin') !== false) {
                            $q->orWhereNull('orders.added_by');
                        }
                    }
                });
            })
            ->when(data_get($filters, 'computed_modified_at') ?: (data_get($filters, 'modified_by') ?: (data_get($filters, 'modified_at') ?: (data_get($filters, 'client_information_updated_at') ?: (data_get($filters, 'designing_updated_at') ?: (data_get($filters, 'printing_updated_at') ?: (data_get($filters, 'packaging_updated_at') ?: data_get($filters, 'dispatch_delivery_updated_at'))))))), function (Builder $query, $value) {
                $trimmed = trim($value);
                $cleanName = trim(preg_replace('/^by[:\s]*/i', '', $trimmed));

                $query->where(function ($q) use ($trimmed, $cleanName) {
                    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $trimmed, $m)) {
                        $isoDate = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                        $q->whereDate('orders.updated_at', $isoDate)
                          ->orWhereHas('clientInformation', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('designing', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('printing', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('packaging', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('dispatchDelivery', fn($sub) => $sub->whereDate('updated_at', $isoDate));
                    } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $trimmed, $m)) {
                        $isoDate = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
                        $q->whereDate('orders.updated_at', $isoDate)
                          ->orWhereHas('clientInformation', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('designing', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('printing', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('packaging', fn($sub) => $sub->whereDate('updated_at', $isoDate))
                          ->orWhereHas('dispatchDelivery', fn($sub) => $sub->whereDate('updated_at', $isoDate));
                    } elseif (preg_match('/^\d{1,2}-\d{1,2}$/', $trimmed)) {
                        $q->whereRaw("DATE_FORMAT(orders.updated_at, '%d-%m') = ?", [$trimmed]);
                    } else {
                        $q->whereRaw("DATE_FORMAT(orders.updated_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"])
                          ->orWhereHas('clientInformation', fn($sub) => $sub->whereRaw("DATE_FORMAT(updated_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"]))
                          ->orWhereHas('designing', fn($sub) => $sub->whereRaw("DATE_FORMAT(updated_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"]))
                          ->orWhereHas('printing', fn($sub) => $sub->whereRaw("DATE_FORMAT(updated_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"]))
                          ->orWhereHas('packaging', fn($sub) => $sub->whereRaw("DATE_FORMAT(updated_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"]))
                          ->orWhereHas('dispatchDelivery', fn($sub) => $sub->whereRaw("DATE_FORMAT(updated_at, '%d-%m-%Y') like ?", ["%{$trimmed}%"]));
                    }

                    if ($cleanName !== '') {
                        $q->orWhereHas('modifiedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('clientInformation.modifiedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('designing.modifiedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('printing.modifiedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('packaging.modifiedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('dispatchDelivery.modifiedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('addedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"))
                          ->orWhereHas('clientInformation.addedBy', fn($sub) => $sub->where('name', 'like', "%{$cleanName}%"));

                        if (stripos($cleanName, 'admin') !== false) {
                            $q->orWhereNull('orders.modified_by');
                        }
                    }
                });
            })
            ->when(!isset($filters['stage']), function ($q) {
                $q->orderByRaw("CASE 
                      WHEN orders.priority = 'P1' THEN 1
                      WHEN orders.budget_tier IN ('Platinum', 'Gold', 'Silver') OR orders.priority = 'P2' THEN 2
                      WHEN orders.priority = 'P3' THEN 3
                      ELSE 4 END")
                  ->orderBy('orders.created_at', 'desc');
            })
            ->when(isset($filters['stage']), fn($q) => $q->orderBy('orders.created_at', 'desc'));

        if (data_get($filters, 'return_builder')) {
            return $query;
        }
        
        return $query->paginate($perPage);
    }


    /**
     * Get paginated orders with status counts.
     */
    public function listOrdersWithCounts(array $filters = [], int $perPage = 20): array
    {
        // 1. Get counts by ignoring status filter
        $countFilters = $filters;
        $countFilters['ignore_status_filter'] = true;
        $countFilters['return_builder'] = true;
        
        $baseQueryForCount = clone $this->listOrders($countFilters, $perPage);
        
        $statusCounts = [
            'Pending' => 0,
            'Process' => 0,
            'Completed' => 0,
            'Cancelled' => 0,
        ];

        if (isset($filters['stage'])) {
            $stage = $filters['stage'];
            $stageTable = match ($stage) {
                'client-information' => 'order_client_information',
                'designing' => 'order_designing',
                'printing' => 'order_printing',
                'packaging' => 'order_packaging',
                'delivery' => 'order_dispatch_delivery',
                default => null
            };

            if ($stageTable) {
                // We use conditional aggregation or simple groupBy
                // Because orders can be joined with stage tables, the status is in the stage table.
                // However, client-information has a special case for "New Orders" where the record might not exist.
                
                // For client-information, let's just get the raw orders that matched the other filters
                if ($stage === 'client-information') {
                    $allMatchingOrders = (clone $baseQueryForCount)
                        ->select('orders.id', "{$stageTable}.status")
                        ->get();
                        
                    foreach ($allMatchingOrders as $order) {
                        $status = $order->status;
                        if (!$status) {
                            $statusCounts['Pending']++;
                        } elseif (isset($statusCounts[$status])) {
                            $statusCounts[$status]++;
                        }
                    }
                } else {
                    $counts = (clone $baseQueryForCount)
                        ->select("{$stageTable}.status", DB::raw('count(*) as aggregate'))
                        ->groupBy("{$stageTable}.status")
                        ->pluck('aggregate', 'status')
                        ->toArray();

                    foreach ($counts as $status => $count) {
                        if ($status && isset($statusCounts[$status])) {
                            $statusCounts[$status] += $count;
                        }
                    }
                }
            }
        }

        // 2. Get paginated results normally
        $paginator = $this->listOrders($filters, $perPage);

        return [
            'paginator' => $paginator,
            'status_counts' => $statusCounts,
        ];
    }


    /**
     * Get pending and processing counts for all 5 stages for the summary cards.
     */
    public function getStageSummaries(array $filters): array
    {
        $stages = [
            'client-information' => 'order_client_information',
            'designing' => 'order_designing',
            'printing' => 'order_printing',
            'packaging' => 'order_packaging',
            'delivery' => 'order_dispatch_delivery'
        ];

        $summaries = [];
        $countFilters = $filters;
        $countFilters['ignore_status_filter'] = true;
        $countFilters['return_builder'] = true;

        foreach ($stages as $stage => $stageTable) {
            $stageFilters = $countFilters;
            $stageFilters['stage'] = $stage;
            
            $query = $this->listOrders($stageFilters, 1);
            
            $pendingCount = 0;
            $processCount = 0;
            
            if ($stage === 'client-information') {
                $allMatchingOrders = (clone $query)->select('orders.id', "{$stageTable}.status")->get();
                foreach ($allMatchingOrders as $order) {
                    $status = $order->status;
                    if (!$status || $status === 'Pending') {
                        $pendingCount++;
                    } elseif ($status === 'Process') {
                        $processCount++;
                    }
                }
            } else {
                $counts = (clone $query)
                    ->select("{$stageTable}.status", \Illuminate\Support\Facades\DB::raw('count(*) as aggregate'))
                    ->groupBy("{$stageTable}.status")
                    ->pluck('aggregate', 'status')
                    ->toArray();

                $pendingCount = $counts['Pending'] ?? 0;
                $processCount = $counts['Process'] ?? 0;
            }
            
            $summaries[$stage] = [
                'Pending' => $pendingCount,
                'Process' => $processCount,
            ];
        }

        return $summaries;
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

}
