<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index(): View
    {
        return view('admin.reports.index');
    }

    /**
     * Generate sales report.
     */
    public function sales(Request $request): View
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'seller_id' => 'nullable|exists:sellers,id',
        ]);

        $query = Order::query()->where('status', '!=', 'cancelled');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        } elseif (! $request->filled('date_from')) {
            // Default: last 30 days
            $query->where('created_at', '>=', now()->subDays(30));
        }

        // Seller filter
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Calculate metrics
        $totalOrders = $query->count();
        $totalRevenue = $query->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Orders by status
        $ordersByStatus = $query->clone()
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('status')
            ->get();

        // Daily sales (for chart)
        $dailySales = $query->clone()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Top sellers
        $topSellers = $query->clone()
            ->select('seller_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('seller_id')
            ->orderBy('revenue', 'desc')
            ->with('seller:id,store_name')
            ->take(10)
            ->get();

        // Get sellers for filter
        $sellers = Seller::where('status', 'active')
            ->orderBy('store_name')
            ->get(['id', 'store_name']);

        return view('admin.reports.sales', compact(
            'totalOrders',
            'totalRevenue',
            'averageOrderValue',
            'ordersByStatus',
            'dailySales',
            'topSellers',
            'sellers'
        ));
    }

    /**
     * Export sales report to CSV.
     */
    public function exportSales(Request $request): Response
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'seller_id' => 'nullable|exists:sellers,id',
        ]);

        $query = Order::query()
            ->with(['user:id,name,email', 'seller:id,store_name'])
            ->where('status', '!=', 'cancelled');

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        } elseif (! $request->filled('date_from')) {
            // Default: last 30 days
            $query->where('created_at', '>=', now()->subDays(30));
        }

        // Seller filter
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $csv = "Pedido,Data,Cliente,Email,Vendedor,Subtotal,Frete,Desconto,Total,Status\n";

        foreach ($orders as $order) {
            /** @var \App\Models\User $user */
            $user = $order->user;
            /** @var \App\Models\Seller $seller */
            $seller = $order->seller;

            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"'."\n",
                $order->order_number,
                $order->created_at->format('d/m/Y H:i'),
                $user->name,
                $user->email,
                $seller->store_name,
                number_format((float) $order->subtotal, 2, ',', '.'),
                number_format((float) $order->shipping_fee, 2, ',', '.'),
                number_format((float) $order->discount, 2, ',', '.'),
                number_format((float) $order->total, 2, ',', '.'),
                $order->status_label
            );
        }

        $filename = 'relatorio-vendas-'.now()->format('Y-m-d-His').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Generate products report.
     */
    public function products(Request $request): View
    {
        $request->validate([
            'seller_id' => 'nullable|exists:sellers,id',
            'status' => 'nullable|in:published,draft',
        ]);

        $query = Product::query()->with(['seller:id,store_name', 'category:id,name']);

        // Seller filter
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Calculate metrics
        $totalProducts = Product::count();
        $publishedProducts = Product::where('status', 'published')->count();
        $draftProducts = Product::where('status', 'draft')->count();
        $outOfStockProducts = Product::where('stock', 0)->count();
        $lowStockProducts = Product::where('stock', '>', 0)->where('stock', '<=', 10)->where('status', 'published')->count();

        // Sort (new system with whitelist)
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Whitelist sortable columns (security)
        $allowedSorts = ['name', 'price', 'stock', 'created_at'];
        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        // Pagination with per_page support
        $perPage = $request->input('per_page', 20);
        if (! in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $products = $query->paginate($perPage)->withQueryString();

        // Get sellers for filter
        $sellers = Seller::where('status', 'active')
            ->orderBy('store_name')
            ->get(['id', 'store_name']);

        return view('admin.reports.products', compact(
            'totalProducts',
            'publishedProducts',
            'draftProducts',
            'outOfStockProducts',
            'lowStockProducts',
            'products',
            'sellers',
            'sortField',
            'sortDirection'
        ));
    }

    /**
     * Generate sellers report.
     */
    public function sellers(Request $request): View
    {
        $request->validate([
            'status' => 'nullable|in:pending,active,suspended',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Seller::query()->with(['user:id,name,email']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter (registration date)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Calculate metrics
        $totalSellers = Seller::count();
        $activeSellers = Seller::where('status', 'active')->count();
        $pendingSellers = Seller::where('status', 'pending')->count();
        $suspendedSellers = Seller::where('status', 'suspended')->count();

        // Sellers with performance data
        $sellersCollection = $query->withCount('products')
            ->withSum('products', 'stock')
            ->get()
            ->map(function ($seller) {
                /** @var \App\Models\Seller $seller */
                $totalOrders = Order::where('seller_id', $seller->id)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                $totalRevenue = Order::where('seller_id', $seller->id)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total');

                // Add dynamic properties using setAttribute
                $seller->setAttribute('total_orders', $totalOrders);
                $seller->setAttribute('total_revenue', $totalRevenue);

                return $seller;
            });

        // Sort (new system with whitelist)
        $sortField = $request->input('sort', 'revenue');
        $sortDirection = $request->input('direction', 'desc');

        // Whitelist sortable columns (security)
        $allowedSorts = ['store_name', 'products_count', 'revenue'];
        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'revenue';
        }

        // Apply sorting
        if ($sortField === 'store_name') {
            $sellersCollection = $sortDirection === 'asc'
                ? $sellersCollection->sortBy('store_name')
                : $sellersCollection->sortByDesc('store_name');
        } elseif ($sortField === 'products_count') {
            $sellersCollection = $sortDirection === 'asc'
                ? $sellersCollection->sortBy('products_count')
                : $sellersCollection->sortByDesc('products_count');
        } else {
            // Default: sort by revenue
            $sellersCollection = $sortDirection === 'asc'
                ? $sellersCollection->sortBy('total_revenue')
                : $sellersCollection->sortByDesc('total_revenue');
        }

        // Pagination with per_page support
        $perPage = $request->input('per_page', 20);
        if (! in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        // Manual pagination for collection
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $sellers = new \Illuminate\Pagination\LengthAwarePaginator(
            $sellersCollection->slice($offset, $perPage)->values(),
            $sellersCollection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.reports.sellers', compact(
            'totalSellers',
            'activeSellers',
            'pendingSellers',
            'suspendedSellers',
            'sellers',
            'sortField',
            'sortDirection'
        ));
    }
}
