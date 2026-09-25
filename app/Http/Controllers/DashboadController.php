<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboadController extends Controller
{
    public function view()
    {
        $totalOrders = Order::count();
        //2. إجمالي الإيرادات (Total Revenue)
        $totalRevenue = Order::where('status', 'delivered')->sum('total') ?? 0;
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $shippedOrders = Order::where('status', 'shipped')->count();
        $totalProducts = Product::count();
        $totalActiveProducts = Product::where('status', 'active')->count();
        $totalInActiveProducts = Product::where('status', 'inactive')->count();
        $totalCustomers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        // عدد الطلبات في آخر 30 يوم
        $monthlyOrders = Order::where('created_at', '>=', now()->subDays(30))->count();
        // الإيرادات لآخر 30 يوم (للطالبات المدفوعة فقط)
        $monthlyRevenue = Order::where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total') ?? 0;
            
        return response()->json([
            'success' => true,
            'message' => 'Dashboard statistics retrieved successfully',
            'data'    => [
                'financial' => [
                    'total_revenue'   => (float) $totalRevenue,
                    'monthly_revenue' => (float) $monthlyRevenue,
                ],
                'orders' => [
                    'total'        => $totalOrders,
                    'delivered'    => $deliveredOrders,
                    'shipped'      => $shippedOrders,
                    'pending'      => $pendingOrders,
                    'last_30_days' => $monthlyOrders,
                ],
                'products' => [
                    'total'    => $totalProducts,
                    'active'   => $totalActiveProducts,
                    'inactive' => $totalInActiveProducts,
                ],
                'users' => [
                    'total_customers' => $totalCustomers,
                    'total_admins'    => $totalAdmins,
                    'total_users'     => $totalCustomers + $totalAdmins,
                ],
            ]
        ], 200);
    }
}
