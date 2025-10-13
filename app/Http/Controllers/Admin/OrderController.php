<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Seller;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Display a listing of all orders.
     */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['user', 'seller', 'items.product.media', 'address']);

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by seller
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        // Sort (new system with whitelist)
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Whitelist sortable columns (security)
        $allowedSorts = ['order_number', 'customer_name', 'seller_name', 'total', 'status', 'created_at'];
        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        // Handle relationship sorting
        if ($sortField === 'customer_name') {
            $query->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortDirection)
                ->select('orders.*')
                ->distinct();
        } elseif ($sortField === 'seller_name') {
            $query->leftJoin('sellers', 'orders.seller_id', '=', 'sellers.id')
                ->orderBy('sellers.store_name', $sortDirection)
                ->select('orders.*')
                ->distinct();
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination with per_page support
        $perPage = $request->input('per_page', 20);
        if (! in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $orders = $query->paginate($perPage)->withQueryString();

        // Get sellers for filter dropdown
        $sellers = Seller::where('status', 'active')
            ->orderBy('store_name')
            ->get(['id', 'store_name']);

        // Get status counts
        $statusCounts = [
            'all' => Order::count(),
            'awaiting_payment' => Order::where('status', 'awaiting_payment')->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'preparing' => Order::where('status', 'preparing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'sellers', 'statusCounts', 'sortField', 'sortDirection'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'user',
            'seller.media',
            'items.product.media',
            'address',
            'history' => fn ($query) => $query->orderBy('created_at', 'asc'),
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status (admin override).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);

        try {
            $validated = $request->validated();

            $this->orderService->updateOrderStatus(
                $order,
                $validated['status'],
                $validated['tracking_code'] ?? null,
                $validated['note'] ?? 'Status atualizado pelo administrador'
            );

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Status do pedido atualizado com sucesso.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao atualizar status: '.$e->getMessage());
        }
    }

    /**
     * Cancel the specified order (admin action).
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $this->orderService->cancelOrder($order, 'Cancelado pelo administrador');

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('success', 'Pedido cancelado com sucesso.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao cancelar pedido: '.$e->getMessage());
        }
    }
}
