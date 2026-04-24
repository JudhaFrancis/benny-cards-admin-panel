<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalOrders = Order::count();
            $completedOrders = Order::where('status', 'completed')->count();
            $totalPayments = Payment::count();
            $totalRevenue = Payment::where('payment_status', 'completed')->sum('amount');

            // Get monthly orders trend for the last 6 months
            $monthlyOrders = Order::selectRaw('COUNT(*) as count, MONTHNAME(created_at) as month, MONTH(created_at) as month_num')
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
            $paymentStatusData = Payment::selectRaw('payment_status, COUNT(*) as count')
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

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'total_payments' => $totalPayments,
                    'total_revenue' => $totalRevenue,
                    'monthly_trend' => $monthlyOrders,
                    'payment_distribution' => $paymentDist,
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
