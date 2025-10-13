<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get cart items for user or guest.
     */
    public function getCartItems(?User $user = null, ?string $sessionId = null): Collection
    {
        $query = CartItem::with(['product.seller', 'variation']);

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return new Collection;
        }

        return $query->get();
    }

    /**
     * Add item to cart.
     */
    public function addItem(
        Product $product,
        int $quantity = 1,
        ?int $variationId = null,
        ?User $user = null,
        ?string $sessionId = null,
        ?string $guestCartId = null
    ): CartItem {
        // Validate stock
        if (! $product->isAvailable()) {
            throw new \Exception('Product is not available.');
        }

        if ($product->stock < $quantity) {
            throw new \Exception('Quantidade solicitada excede o estoque disponível');
        }

        // 🛡️ SINGLE-SELLER RESTRICTION: Validate cart only contains items from one seller
        $this->validateSingleSellerCart($product->seller_id, $user, $sessionId);

        return DB::transaction(function () use ($product, $quantity, $variationId, $user, $sessionId, $guestCartId) {
            // Check if item already exists in cart
            $existingItem = $this->findExistingItem($product->id, $variationId, $user, $sessionId);

            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem->quantity + $quantity;

                if ($product->stock < $newQuantity) {
                    throw new \Exception('Insufficient stock available.');
                }

                $existingItem->update(['quantity' => $newQuantity]);

                return $existingItem->fresh();
            }

            // Create new cart item
            return CartItem::create([
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'guest_cart_id' => $guestCartId,
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'quantity' => $quantity,
            ]);
        });
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero.');
        }

        // Check stock
        /** @var \App\Models\Product $product */
        $product = $cartItem->product;

        if ($product->stock < $quantity) {
            throw new \Exception('Quantidade solicitada excede o estoque disponível');
        }

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh();
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(CartItem $cartItem): bool
    {
        return $cartItem->delete();
    }

    /**
     * Clear cart for user or guest.
     */
    public function clearCart(?User $user = null, ?string $sessionId = null): bool
    {
        $query = CartItem::query();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return false;
        }

        return $query->delete() > 0;
    }

    /**
     * Calculate cart total.
     *
     * @return array{subtotal: float, total: float, items_count: int}
     */
    public function calculateTotal(?User $user = null, ?string $sessionId = null): array
    {
        $items = $this->getCartItems($user, $sessionId);

        $subtotal = 0;
        $itemsCount = 0;

        foreach ($items as $item) {
            /** @var \App\Models\CartItem $item */
            $subtotal += $item->product->sale_price * $item->quantity;
            $itemsCount += $item->quantity;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2), // In future, apply discounts here
            'items_count' => $itemsCount,
        ];
    }

    /**
     * Group cart items by seller.
     *
     * @return array<int, array{seller: \App\Models\Seller, items: Collection}>
     */
    public function groupBySeller(?User $user = null, ?string $sessionId = null): array
    {
        $items = $this->getCartItems($user, $sessionId);

        $grouped = [];

        foreach ($items as $item) {
            /** @var \App\Models\CartItem $item */
            $sellerId = $item->product->seller_id;

            if (! isset($grouped[$sellerId])) {
                /** @var \App\Models\Seller $seller */
                $seller = $item->product->seller;

                $grouped[$sellerId] = [
                    'seller' => $seller,
                    'items' => new Collection,
                ];
            }

            $grouped[$sellerId]['items']->push($item);
        }

        return $grouped;
    }

    /**
     * Merge guest cart to user cart on login.
     */
    public function mergeGuestCart(User $user, string $sessionId): void
    {
        DB::transaction(function () use ($user, $sessionId) {
            $guestItems = CartItem::where('session_id', $sessionId)
                ->orWhere('guest_cart_id', $sessionId)
                ->get();

            foreach ($guestItems as $guestItem) {
                // Check if user already has this item
                $existingItem = CartItem::where('user_id', $user->id)
                    ->where('product_id', $guestItem->product_id)
                    ->where('variation_id', $guestItem->variation_id)
                    ->first();

                if ($existingItem) {
                    // Merge quantities
                    $existingItem->increment('quantity', $guestItem->quantity);
                    $guestItem->delete();
                } else {
                    // Transfer to user
                    $guestItem->update([
                        'user_id' => $user->id,
                        'session_id' => null,
                        'guest_cart_id' => null,
                    ]);
                }
            }
        });
    }

    /**
     * Validate cart items (check stock, availability).
     *
     * @return array{valid: bool, errors: array}
     */
    public function validateCart(?User $user = null, ?string $sessionId = null): array
    {
        $items = $this->getCartItems($user, $sessionId);
        $errors = [];

        foreach ($items as $item) {
            /** @var \App\Models\CartItem $item */
            // Check if product is still available
            if (! $item->product->isAvailable()) {
                $errors[] = "Product '{$item->product->name}' is no longer available.";

                continue;
            }

            // Check stock
            if ($item->product->stock < $item->quantity) {
                $errors[] = "Product '{$item->product->name}' has insufficient stock (available: {$item->product->stock}).";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Find existing cart item.
     */
    private function findExistingItem(
        int $productId,
        ?int $variationId,
        ?User $user,
        ?string $sessionId
    ): ?CartItem {
        $query = CartItem::where('product_id', $productId)
            ->where('variation_id', $variationId);

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query->first();
    }

    /**
     * Validate that cart contains items from only one seller.
     * Throws exception if trying to add product from different seller.
     *
     * @throws \Exception When cart has items from different seller
     */
    private function validateSingleSellerCart(int $sellerId, ?User $user, ?string $sessionId): void
    {
        $cartItems = $this->getCartItems($user, $sessionId);

        if ($cartItems->isEmpty()) {
            return; // Empty cart, allow any seller
        }

        // Get seller ID from first cart item
        /** @var \App\Models\CartItem $firstCartItem */
        $firstCartItem = $cartItems->first();
        $existingSellerId = $firstCartItem->product->seller_id;

        if ($existingSellerId !== $sellerId) {
            /** @var \App\Models\Seller $existingSeller */
            $existingSeller = $firstCartItem->product->seller;

            throw new \Exception(
                "Seu carrinho já contém produtos de {$existingSeller->store_name}. ".
                'Para adicionar produtos de outro vendedor, finalize a compra atual ou limpe o carrinho.'
            );
        }
    }

    // Alias methods for test compatibility
    public function addToCart(Product $product, int $quantity, ?User $user = null): CartItem
    {
        if (! $product->isAvailable()) {
            throw new \Exception('Produto não disponível para venda');
        }

        if ($product->stock === 0) {
            throw new \Exception('Produto fora de estoque');
        }

        if ($product->stock < $quantity) {
            throw new \Exception('Quantidade solicitada excede o estoque disponível');
        }

        $sessionId = $user ? null : session()->getId();
        $guestCartId = $user ? null : session()->get('guest_cart_id');

        return $this->addItem($product, $quantity, null, $user, $sessionId, $guestCartId);
    }

    public function removeFromCart(CartItem $cartItem): bool
    {
        return $this->removeItem($cartItem);
    }

    public function getGuestCartItems(string $guestCartId): Collection
    {
        return CartItem::where('guest_cart_id', $guestCartId)
            ->orWhere('session_id', $guestCartId)
            ->with(['product.seller'])
            ->get();
    }

    public function calculateCartTotal(?User $user): string
    {
        $total = $this->calculateTotal($user);

        return number_format($total['total'], 2, '.', '');
    }

    public function calculateGuestCartTotal(string $guestCartId): string
    {
        $items = $this->getGuestCartItems($guestCartId);
        $total = 0;

        foreach ($items as $item) {
            /** @var \App\Models\CartItem $item */
            $total += $item->product->sale_price * $item->quantity;
        }

        return number_format($total, 2, '.', '');
    }

    public function clearGuestCart(string $guestCartId): bool
    {
        CartItem::where('guest_cart_id', $guestCartId)
            ->orWhere('session_id', $guestCartId)
            ->delete();

        return true;
    }

    public function getCartItemsCount(?User $user): int
    {
        return CartItem::where('user_id', $user->id)->sum('quantity');
    }

    public function groupCartItemsBySeller(?User $user): array
    {
        $items = CartItem::where('user_id', $user->id)
            ->with(['product.seller'])
            ->get();

        $grouped = [];

        foreach ($items as $item) {
            /** @var \App\Models\CartItem $item */
            $sellerId = $item->product->seller_id;

            if (! isset($grouped[$sellerId])) {
                $grouped[$sellerId] = new Collection;
            }

            $grouped[$sellerId]->push($item);
        }

        return $grouped;
    }
}
