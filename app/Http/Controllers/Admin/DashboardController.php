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
            
            // Total Payments
            $totalPayments = Payment::count();
            
            // Calculate total revenue from completed payments
            $totalRevenue = Payment::where('payment_status', 'completed')->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'total_payments' => $totalPayments,
                    'total_revenue' => $totalRevenue,
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
