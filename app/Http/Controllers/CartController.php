<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    /**
     * Display cart page.
     */
    public function index()
    {
        /** @var \Illuminate\Support\Collection<int, \App\Models\CartItem> $cartItems */
        $cartItems = $this->cartService->getCartItems(
            auth()->user(),
            session()->getId()
        );

        $itemsBySeller = $this->cartService->groupBySeller(
            auth()->user(),
            session()->getId()
        );
        $totals = $this->cartService->calculateTotal(
            auth()->user(),
            session()->getId()
        );
        $subtotal = $totals['subtotal'] ?? 0;

        return view('cart.index', compact('cartItems', 'itemsBySeller', 'subtotal'));
    }

    /**
     * Get cart data (AJAX).
     */
    public function data(): JsonResponse
    {
        $user = auth()->user();
        $sessionId = session()->getId();
        
        \Log::info('Cart data request', [
            'user_id' => $user ? $user->id : null,
            'session_id' => $sessionId,
            'is_authenticated' => auth()->check()
        ]);
        
        /** @var \Illuminate\Support\Collection<int, \App\Models\CartItem> $cartItems */
        $cartItems = $this->cartService->getCartItems($user, $sessionId);
        
        \Log::info('Cart items found', [
            'count' => $cartItems->count(),
            'items' => $cartItems->toArray()
        ]);

        return response()->json([
            'items' => $cartItems->map(function (\App\Models\CartItem $item) {
                /** @var \App\Models\Seller $seller */
                $seller = $item->product->seller;

                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->product->sale_price,
                    'seller_id' => $item->product->seller_id,
                    'seller' => [
                        'store_name' => $seller->store_name,
                    ],
                    'product' => [
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'stock' => $item->product->stock,
                        'image_url' => $item->product->hasImages()
                            ? $item->product->getMainImage()->getUrl('thumb')
                            : null,
                    ],
                ];
            }),
        ]);
    }

    /**
     * Add product to cart (AJAX).
     */
    public function add(AddToCartRequest $request): JsonResponse
    {
        try {
            $product = \App\Models\Product::findOrFail($request->input('product_id'));

            $this->cartService->addItem(
                $product,
                $request->input('quantity', 1),
                null, // $variationId
                auth()->user(),
                session()->getId()
            );

            return response()->json([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho!',
                'cart' => [
                    'items' => $this->getCartData(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update cart item quantity (AJAX).
     */
    public function update(UpdateCartRequest $request, int $cartItemId): JsonResponse
    {
        try {
            $cartItem = \App\Models\CartItem::findOrFail($cartItemId);

            $this->cartService->updateQuantity(
                $cartItem,
                $request->input('quantity')
            );

            return response()->json([
                'success' => true,
                'message' => 'Quantidade atualizada!',
                'cart' => [
                    'items' => $this->getCartData(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove item from cart (AJAX).
     */
    public function remove(int $cartItemId): JsonResponse
    {
        try {
            $cartItem = \App\Models\CartItem::findOrFail($cartItemId);

            $this->cartService->removeItem($cartItem);

            return response()->json([
                'success' => true,
                'message' => 'Item removido do carrinho!',
                'cart' => [
                    'items' => $this->getCartData(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clear cart (AJAX).
     */
    public function clear(): JsonResponse
    {
        try {
            $this->cartService->clearCart(
                auth()->user(),
                session()->getId()
            );

            return response()->json([
                'success' => true,
                'message' => 'Carrinho limpo!',
                'cart' => [
                    'items' => [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Helper to get formatted cart data.
     */
    private function getCartData(): array
    {
        /** @var \Illuminate\Support\Collection<int, \App\Models\CartItem> $cartItems */
        $cartItems = $this->cartService->getCartItems(
            auth()->user(),
            session()->getId()
        );

        return $cartItems->map(function (\App\Models\CartItem $item) {
            /** @var \App\Models\Seller $seller */
            $seller = $item->product->seller;

            return [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'price' => (float) $item->product->sale_price,
                'seller_id' => $item->product->seller_id,
                'seller' => [
                    'store_name' => $seller->store_name,
                ],
                'product' => [
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'stock' => $item->product->stock,
                    'image_url' => $item->product->hasImages()
                        ? $item->product->getMainImage()->getUrl('thumb')
                        : null,
                ],
            ];
        })->toArray();
    }
}
