<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create orders from cart items (1 order per seller).
     *
     * @return EloquentCollection<int, Order>
     */
    public function createOrdersFromCart(
        User $user,
        Collection|EloquentCollection|CartService $cartItemsOrService,
        mixed $shippingDataOrAddress = [],
        ?int $addressId = null
    ): EloquentCollection {
        // Handle different method signatures for backward compatibility
        $cartService = null;

        if ($cartItemsOrService instanceof CartService) {
            $cartService = $cartItemsOrService;
            $shippingData = $shippingDataOrAddress;

            // Validate cart first
            $validation = $cartService->validateCart($user);

            if (! $validation['valid']) {
                throw new \Exception('Cart validation failed: '.implode(', ', $validation['errors']));
            }

            // Group cart items by seller (1 order = 1 seller)
            $itemsBySeller = $cartService->groupBySeller($user);
        } else {
            // Direct collection of cart items
            $cartItems = $cartItemsOrService;
            $address = $shippingDataOrAddress;

            // Group by seller
            $itemsBySeller = $cartItems->groupBy(function ($item) {
                /** @var \App\Models\CartItem $item */
                return $item->product->seller_id;
            });

            $shippingData = [];
            $addressId = $address ? $address->id : null;
        }

        if (empty($itemsBySeller)) {
            throw new \Exception('Cart is empty.');
        }

        return DB::transaction(function () use ($user, $itemsBySeller, $shippingData, $cartService) {
            $orders = new EloquentCollection;

            foreach ($itemsBySeller as $sellerId => $sellerData) {
                // Extract items from seller data structure (new format: ['seller' => Seller, 'items' => Collection])
                /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $items */
                $items = is_array($sellerData) ? $sellerData['items'] : $sellerData;

                // Calculate order totals
                $totals = $this->calculateOrderTotals($items);

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'seller_id' => $sellerId,
                    'order_number' => $this->generateOrderNumber(),
                    'status' => 'awaiting_payment',
                    'subtotal' => $totals['subtotal'],
                    'discount' => $totals['discount'],
                    'shipping_fee' => $shippingData['shipping_cost'] ?? 0.00,
                    'total' => $totals['total'] + ($shippingData['shipping_cost'] ?? 0.00),
                ]);

                // Create order items
                foreach ($items as $cartItem) {
                    /** @var \App\Models\CartItem $cartItem */
                    $price = $cartItem->product->sale_price;

                    if ($cartItem->variation) {
                        $price += $cartItem->variation->additional_price;
                    }

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'variation_id' => $cartItem->variation_id,
                        'product_name' => $cartItem->product->name,
                        'sku' => $cartItem->product->sku ?? 'N/A',
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $price,
                        'subtotal' => $price * $cartItem->quantity,
                    ]);

                    // Reserve stock (decrease stock)
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }

                // Create order history
                $this->addHistory($order, 'created', 'Order created and awaiting payment.');

                $orders->push($order);
            }

            // Clear cart after successful order creation (only if using CartService)
            if ($cartService) {
                $cartService->clearCart($user);
            }

            return $orders;
        });
    }

    /**
     * Calculate order totals from cart items.
     *
     * @return array{subtotal: float, discount: float, total: float}
     */
    public function calculateOrderTotals(Collection $items): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            /** @var \App\Models\CartItem $item */
            $price = $item->product->sale_price;

            if ($item->variation) {
                $price += $item->variation->additional_price;
            }

            $subtotal += $price * $item->quantity;
        }

        // Future: Apply discount codes here
        $discount = 0.00;

        $total = $subtotal - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status, ?string $notes = null): Order
    {
        return $this->updateOrderStatus($order, $status, null, $notes);
    }

    /**
     * Update order status with tracking code and validation.
     */
    public function updateOrderStatus(Order $order, string $status, ?string $trackingCode = null, ?string $notes = null): Order
    {
        $allowedStatuses = [
            'awaiting_payment',
            'paid',
            'preparing',
            'shipped',
            'delivered',
            'cancelled',
            'refunded',
        ];

        if (! in_array($status, $allowedStatuses)) {
            throw new \Exception("Invalid order status: {$status}");
        }

        // Validate status transitions
        $this->validateStatusTransition($order->status, $status);

        DB::transaction(function () use ($order, $status, $trackingCode, $notes) {
            $oldStatus = $order->status;

            $updateData = ['status' => $status];

            // Add tracking code if provided
            if ($trackingCode) {
                $updateData['tracking_code'] = $trackingCode;
            }

            // Set shipped_at timestamp when status is shipped
            if ($status === 'shipped') {
                $updateData['shipped_at'] = now();
            }

            // Set delivered_at timestamp when status is delivered
            if ($status === 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $order->update($updateData);

            // Add history entry
            $historyNote = $notes ?? "Order status changed from {$oldStatus} to {$status}.";
            $this->addHistory($order, $status, $historyNote, $oldStatus);

            // Handle side effects
            if ($status === 'cancelled' || $status === 'refunded') {
                $this->restoreStockOnCancel($order);
            }

            if ($status === 'paid') {
                $order->update(['paid_at' => now()]);
            }
        });

        return $order->fresh();
    }

    /**
     * Cancel order and restore stock.
     */
    public function cancelOrder(Order $order, ?string $reason = null): Order
    {
        if (! in_array($order->status, ['awaiting_payment', 'paid', 'preparing'])) {
            throw new \Exception('Order cannot be cancelled in current status.');
        }

        DB::transaction(function () use ($order, $reason) {
            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Restore stock for each item
            foreach ($order->items as $item) {
                /** @var \App\Models\OrderItem $item */
                /** @phpstan-ignore if.alwaysTrue */
                if ($item->product) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // Create history entry
            OrderHistory::create([
                'order_id' => $order->id,
                'previous_status' => $order->status,
                'new_status' => 'cancelled',
                'note' => $reason ?? 'Pedido cancelado pelo cliente',
                'created_at' => now(),
            ]);
        });

        return $order->fresh();
    }

    /**
     * Get user orders.
     */
    public function getUserOrders(User $user, ?string $status = null, int $perPage = 15)
    {
        $query = Order::where('user_id', $user->id)
            ->with(['seller', 'items.product', 'items.variation']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get seller orders.
     */
    public function getSellerOrders(Seller|int $sellerOrId, ?string $status = null, int $perPage = 15)
    {
        $sellerId = $sellerOrId instanceof Seller ? $sellerOrId->id : $sellerOrId;

        $query = Order::where('seller_id', $sellerId)
            ->with(['user', 'items.product', 'items.variation']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get order details with history.
     */
    public function getOrderWithHistory(Order $order): Order
    {
        return $order->load(['seller', 'user', 'items.product', 'items.variation', 'history']);
    }

    /**
     * Calculate seller earnings from order.
     */
    public function calculateSellerEarnings(Order $order): array
    {
        $subtotal = $order->subtotal;

        // Get commission percentage from settings (default 10%)
        $commissionPercentage = 10.00; // TODO: Get from settings table

        $commission = ($subtotal * $commissionPercentage) / 100;
        $sellerAmount = $subtotal - $commission;

        return [
            'order_total' => $order->total,
            'subtotal' => $subtotal,
            'commission_percentage' => $commissionPercentage,
            'commission_amount' => round($commission, 2),
            'seller_amount' => round($sellerAmount, 2),
            'shipping_cost' => $order->shipping_cost,
        ];
    }

    /**
     * Add history entry to order.
     */
    public function addHistory(Order $order, string $newStatus, string $notes, ?string $previousStatus = null): OrderHistory
    {
        return OrderHistory::create([
            'order_id' => $order->id,
            'previous_status' => $previousStatus ?? $order->status,
            'new_status' => $newStatus,
            'note' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Validate status transition.
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        $validTransitions = [
            'awaiting_payment' => ['paid', 'cancelled'],
            'paid' => ['preparing', 'cancelled', 'refunded'],
            'preparing' => ['shipped', 'cancelled', 'refunded'],
            'shipped' => ['delivered', 'refunded'],
            'delivered' => ['refunded'],
            'cancelled' => [],
            'refunded' => [],
        ];

        if (! isset($validTransitions[$currentStatus])) {
            throw new \Exception("Unknown current status: {$currentStatus}");
        }

        if (! in_array($newStatus, $validTransitions[$currentStatus])) {
            throw new \Exception("Cannot transition from {$currentStatus} to {$newStatus}.");
        }
    }

    /**
     * Restore stock when order is cancelled or refunded.
     */
    private function restoreStockOnCancel(Order $order): void
    {
        foreach ($order->items as $item) {
            /** @var \App\Models\OrderItem $item */
            $item->product->increment('stock', $item->quantity);
        }
    }

    // Removed - moved to main implementation above line 163

    public function markAsPaid(Order $order): Order
    {
        return $this->updateStatus($order, 'paid');
    }

    public function getOrderById(int $id): ?Order
    {
        return Order::with(['seller', 'user', 'items.product'])->find($id);
    }

    public function calculateOrderSubtotal(Order $order): string
    {
        $subtotal = $order->items->sum('subtotal');

        return number_format($subtotal, 2, '.', '');
    }

    public function applyShippingFee(Order $order, float $shippingFee): Order
    {
        $order->shipping_fee = $shippingFee;
        $order->total = $order->subtotal + $shippingFee;
        $order->save();

        return $order->fresh();
    }

    public function getOrdersByStatus(string $status)
    {
        return Order::where('status', $status)
            ->with(['seller', 'user', 'items.product'])
            ->latest()
            ->get();
    }

    public function getPendingOrders()
    {
        return $this->getOrdersByStatus('awaiting_payment');
    }

    /**
     * Generate unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.strtoupper(uniqid());
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
