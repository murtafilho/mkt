<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
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
     * Display a listing of the seller's orders.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        /** @var \App\Models\Seller|null $seller */
        $seller = $user->seller;

        if (! $seller) {
            abort(403, 'Você não possui uma loja cadastrada.');
        }

        $query = Order::query()
            ->where('seller_id', $seller->id)
            ->with(['user', 'items.product.media', 'address']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        // Sort (new system with whitelist)
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Whitelist sortable columns (security)
        $allowedSorts = ['order_number', 'customer_name', 'total', 'created_at'];
        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        // Handle relationship sorting
        if ($sortField === 'customer_name') {
            $query->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortDirection)
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

        // Get status counts for badges
        $statusCounts = [
            'all' => Order::where('seller_id', $seller->id)->count(),
            'awaiting_payment' => Order::where('seller_id', $seller->id)->where('status', 'awaiting_payment')->count(),
            'paid' => Order::where('seller_id', $seller->id)->where('status', 'paid')->count(),
            'preparing' => Order::where('seller_id', $seller->id)->where('status', 'preparing')->count(),
            'shipped' => Order::where('seller_id', $seller->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('seller_id', $seller->id)->where('status', 'delivered')->count(),
        ];

        return view('seller.orders.index', compact('orders', 'statusCounts', 'sortField', 'sortDirection'));
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

        return view('seller.orders.show', compact('order'));
    }

    /**
     * Update the order status.
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
                $validated['note'] ?? null
            );

            return redirect()
                ->route('seller.orders.show', $order)
                ->with('success', 'Status do pedido atualizado com sucesso.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao atualizar status: '.$e->getMessage());
        }
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $this->orderService->cancelOrder($order, 'Cancelado pelo vendedor');

            return redirect()
                ->route('seller.orders.show', $order)
                ->with('success', 'Pedido cancelado com sucesso.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao cancelar pedido: '.$e->getMessage());
        }
    }
}
