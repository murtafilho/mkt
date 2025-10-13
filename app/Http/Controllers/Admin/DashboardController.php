<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // Sales metrics
        $todaySales = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $weekSales = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $monthSales = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $totalRevenue = Order::where('status', '!=', 'cancelled')
            ->sum('total');

        // Order counts by status
        $ordersByStatus = [
            'total' => Order::count(),
            'awaiting_payment' => Order::where('status', 'awaiting_payment')->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Seller counts by status
        $sellersByStatus = [
            'total' => Seller::count(),
            'pending' => Seller::where('status', 'pending')->count(),
            'active' => Seller::where('status', 'active')->count(),
            'suspended' => Seller::where('status', 'suspended')->count(),
        ];

        // Product counts by status
        $productsByStatus = [
            'total' => Product::count(),
            'published' => Product::where('status', 'published')->count(),
            'draft' => Product::where('status', 'draft')->count(),
        ];

        // Customer count
        $customersCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        })->count();

        // Recent orders (last 10)
        $recentOrders = Order::with(['user', 'seller'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Pending sellers (need approval)
        $pendingSellers = Seller::with(['user', 'media'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Monthly sales data (last 6 months)
        $monthlySales = Order::select(
            DB::raw(match (config('database.default')) {
                'mysql' => 'DATE_FORMAT(created_at, "%Y-%m") as month',
                'sqlite' => 'strftime("%Y-%m", created_at) as month',
                default => 'DATE_FORMAT(created_at, "%Y-%m") as month',
            }),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'weekSales',
            'monthSales',
            'totalRevenue',
            'ordersByStatus',
            'sellersByStatus',
            'productsByStatus',
            'customersCount',
            'recentOrders',
            'pendingSellers',
            'monthlySales'
        ));
    }
}
