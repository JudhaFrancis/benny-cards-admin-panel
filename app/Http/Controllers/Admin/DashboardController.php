<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $branch = $request->query('branch');

            $orderQuery = Order::query();
            $paymentQuery = Payment::query();

            if ($branch && $branch !== 'All Branches') {
                $orderQuery->whereHas('clientInformation', function ($q) use ($branch) {
                    $q->where('order_details->order_placed_in', $branch);
                });
                
                // For payments, we need to join with orders to filter by branch
                $paymentQuery->whereHas('order.clientInformation', function ($q) use ($branch) {
                    $q->where('order_details->order_placed_in', $branch);
                });
            }

            $totalOrders = (clone $orderQuery)->count();
            $completedOrders = (clone $orderQuery)->where('status', 'completed')->count();
            $totalPayments = (clone $paymentQuery)->count();
            $totalRevenue = (clone $paymentQuery)->where('payment_status', 'completed')->sum('amount');

            // Get monthly orders trend for the last 6 months
            $monthlyOrders = (clone $orderQuery)->selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as month_num')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month', 'month_num')
                ->orderBy('month_num')
                ->get()
                ->map(function ($item) {
                    return [
                        'month' => substr($item->month, 0, 3),
                        'count' => $item->count
                    ];
                });

            // Get payment status distribution
            $paymentStatusData = (clone $paymentQuery)->selectRaw('payment_status, COUNT(*) as count')
                ->groupBy('payment_status')
                ->get()
                ->pluck('count', 'payment_status')
                ->toArray();

            // Ensure we have common statuses even if 0
            $paymentDist = [
                'paid' => $paymentStatusData['completed'] ?? 0,
                'pending' => $paymentStatusData['pending'] ?? 0,
                'overdue' => $paymentStatusData['failed'] ?? 0, // Mapping failed to overdue for UI purposes or just keeping it as stats
            ];

            // Calculate stage stats using hierarchical logic to match resolved_status
            $stageStats = [
                'new_order' => (clone $orderQuery)->where(function ($q) {
                    $q->whereDoesntHave('clientInformation')
                        ->orWhereHas('clientInformation', fn($sq) => $sq->where('status', '!=', 'Completed'));
                })->whereDoesntHave('designing', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->whereDoesntHave('printing', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->whereDoesntHave('packaging', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->whereDoesntHave('dispatchDelivery', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->count(),

                'designing' => (clone $orderQuery)->whereHas('designing', fn($sq) => $sq->where('status', 'Process'))
                  ->whereDoesntHave('printing', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->whereDoesntHave('packaging', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->whereDoesntHave('dispatchDelivery', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->count(),

                'printing' => (clone $orderQuery)->whereHas('printing', fn($sq) => $sq->where('status', 'Process'))
                  ->whereDoesntHave('packaging', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->whereDoesntHave('dispatchDelivery', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->count(),

                'packaging' => (clone $orderQuery)->whereHas('packaging', fn($sq) => $sq->where('status', 'Process'))
                  ->whereDoesntHave('dispatchDelivery', fn($sq) => $sq->whereIn('status', ['Process', 'Completed']))
                  ->count(),

                'delivered' => (clone $orderQuery)->whereHas('dispatchDelivery', fn($sq) => $sq->where('status', 'Completed'))
                  ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'total_payments' => $totalPayments,
                    'total_revenue' => $totalRevenue,
                    'monthly_trend' => $monthlyOrders,
                    'payment_distribution' => $paymentDist,
                    'stage_stats' => $stageStats,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats: ' . $e->getMessage()
            ], 500);
        }
    }
}
