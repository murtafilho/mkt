<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
     * Display a listing of the user's orders.
     */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->where('user_id', auth()->id())
            ->with(['seller.media', 'items.product.media', 'address']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%'.$request->search.'%');
        }

        // Sort by date
        $sort = $request->input('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $orders = $query->paginate(10)->withQueryString();

        return view('customer.my-orders.index', compact('orders'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'seller.media',
            'items.product.media',
            'address',
            'history' => fn ($query) => $query->orderBy('created_at', 'asc'),
        ]);

        return view('customer.my-orders.show', compact('order'));
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $this->orderService->cancelOrder($order);

            return redirect()
                ->route('customer.orders.show', $order)
                ->with('success', 'Pedido cancelado com sucesso.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao cancelar pedido: '.$e->getMessage());
        }
    }
}
