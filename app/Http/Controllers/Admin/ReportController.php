<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function orderReport(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $perPage = $request->query('per_page', 20);

        $query = Order::with(['customerDetails']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Global Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customerDetails', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Column Filters
        if ($request->has('order_number') && !empty($request->order_number)) {
            $query->where('order_number', 'like', "%{$request->order_number}%");
        }
        if ($request->has('customer_name') && !empty($request->customer_name)) {
            $query->whereHas('customerDetails', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->customer_name}%");
            });
        }
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $paginator = $query->oldest()->paginate($perPage);

        $orders = collect($paginator->items())->map(function($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customerDetails ? $order->customerDetails->name : 'N/A',
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at
            ];
        });

        $statsQuery = Order::query();
        if (!empty($startDate)) $statsQuery->whereDate('created_at', '>=', $startDate);
        if (!empty($endDate)) $statsQuery->whereDate('created_at', '<=', $endDate);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'stats' => $stats,
                'meta' => [
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'current_page' => $paginator->currentPage(),
                ]
            ]
        ]);
    }

    public function exportOrders(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $query = Order::with(['customerDetails']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $orders = $query->oldest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_report_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 (Excel friendly)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['S/No.', 'Customer Name', 'Order ID', 'Status', 'Amount (INR)', 'Date']);

            foreach ($orders as $index => $order) {
                fputcsv($file, [
                    $index + 1,
                    $order->customerDetails ? $order->customerDetails->name : 'N/A',
                    '#' . $order->order_number,
                    ucfirst($order->status),
                    $order->total_amount,
                    $order->created_at->format('d M Y')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function invoiceReport(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $perPage = $request->query('per_page', 20);

        $query = Order::with(['customerDetails']);

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Global Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customerDetails', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Column Filters
        if ($request->has('invoice_number') && !empty($request->invoice_number)) {
            $invoiceNum = str_replace('INV-', '', $request->invoice_number);
            $query->where('id', 'like', "%" . (int)$invoiceNum . "%");
        }
        if ($request->has('order_number') && !empty($request->order_number)) {
            $query->where('order_number', 'like', "%{$request->order_number}%");
        }
        if ($request->has('customer_name') && !empty($request->customer_name)) {
            $query->whereHas('customerDetails', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->customer_name}%");
            });
        }
        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $pStatus = $request->payment_status;
            if ($pStatus === 'paid') {
                $query->where('status', 'completed');
            } elseif ($pStatus === 'unpaid') {
                $query->where('status', 'cancelled');
            } elseif ($pStatus === 'outstanding') {
                $query->whereNotIn('status', ['completed', 'cancelled']);
            }
        }

        $paginator = $query->oldest()->paginate($perPage);
        $orders = collect($paginator->items());
        
        $invoices = $orders->map(function($order) {
            return [
                'id' => $order->id,
                'invoice_number' => 'INV-' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                'customer_name' => $order->customerDetails ? $order->customerDetails->name : 'N/A',
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount,
                'status' => $order->status === 'completed' ? 'paid' : ($order->status === 'cancelled' ? 'unpaid' : 'partial'),
                'created_at' => $order->created_at
            ];
        });

        $allOrdersInRange = Order::whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->get();
        $allInvoices = $allOrdersInRange->map(function($order) {
            return [
                'total_amount' => $order->total_amount,
                'status' => $order->status === 'completed' ? 'paid' : ($order->status === 'cancelled' ? 'unpaid' : 'partial'),
            ];
        });

        $stats = [
            'total' => number_format($allInvoices->sum('total_amount'), 2, '.', ''),
            'paid' => number_format($allInvoices->where('status', 'paid')->sum('total_amount'), 2, '.', ''),
            'outstanding' => number_format($allInvoices->where('status', '!=', 'paid')->sum('total_amount'), 2, '.', ''),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'invoices' => $invoices,
                'stats' => $stats,
                'meta' => [
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'current_page' => $paginator->currentPage(),
                ]
            ]
        ]);
    }

    public function profitLossReport(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $query = Order::where('status', 'completed');

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $revenue = (float) $query->sum('total_amount');
        
        // Mocking expenses as 40% of revenue for demonstration
        $expenses = $revenue * 0.4;
        $profit = $revenue - $expenses;

        $stats = [
            'revenue' => number_format($revenue, 2, '.', ''),
            'expenses' => number_format($expenses, 2, '.', ''),
            'profit' => number_format($profit, 2, '.', ''),
        ];

        $ledger = [
          'income' => [
            ['label' => 'Card Sales', 'amount' => number_format($revenue * 0.7, 2, '.', '')],
            ['label' => 'Printing Services', 'amount' => number_format($revenue * 0.2, 2, '.', '')],
            ['label' => 'Custom Design Fees', 'amount' => number_format($revenue * 0.1, 2, '.', '')]
          ],
          'expenses' => [
            ['label' => 'Material Cost', 'amount' => number_format($expenses * 0.6, 2, '.', '')],
            ['label' => 'Ink & Printing', 'amount' => number_format($expenses * 0.2, 2, '.', '')],
            ['label' => 'Shipping', 'amount' => number_format($expenses * 0.2, 2, '.', '')]
          ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'ledger' => $ledger
            ]
        ]);
    }
}
