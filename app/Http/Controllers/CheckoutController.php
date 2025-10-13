<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\ProcessCheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private PaymentService $paymentService
    ) {}

    /**
     * Display checkout review page.
     * Shows cart items grouped by seller, shipping address, and order summary.
     */
    public function index(): View|RedirectResponse
    {
        $itemsBySeller = $this->cartService->groupBySeller(
            auth()->user(),
            session()->getId()
        );

        // Redirect to cart if empty
        if (empty($itemsBySeller)) {
            return redirect()->route('cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Validate cart (stock, availability)
        $validation = $this->cartService->validateCart(
            auth()->user(),
            session()->getId()
        );

        if (! $validation['valid']) {
            return redirect()->route('cart.index')
                ->with('error', 'Alguns itens do seu carrinho não estão mais disponíveis. Por favor, revise seu carrinho.');
        }

        // Get user's default shipping address (if authenticated)
        $user = auth()->user();
        $defaultAddress = $user !== null
            ? $user->addresses()->where('is_default', true)->first()
            : null;

        // Calculate totals per seller
        /** @var array<int, array{seller_name: string, subtotal: float, shipping_fee: float, total: float}> $sellerTotals */
        $sellerTotals = [];
        foreach ($itemsBySeller as $sellerId => $sellerData) {
            /** @var \App\Models\Seller $seller */
            $seller = $sellerData['seller'];
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $items */
            $items = $sellerData['items'];

            $subtotal = $items->sum(function ($item) {
                /** @var \App\Models\CartItem $item */
                return $item->product->sale_price * $item->quantity;
            });

            // TODO: Calculate shipping fee per seller (for now, fixed value)
            $shippingFee = 15.00;

            $sellerTotals[$sellerId] = [
                'seller_name' => $seller->store_name,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $subtotal + $shippingFee,
            ];
        }

        $grandTotal = collect($sellerTotals)->sum('total');

        return view('checkout.index', [
            'itemsBySeller' => $itemsBySeller,
            'sellerTotals' => $sellerTotals,
            'grandTotal' => $grandTotal,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * Process checkout: create single order and process payment via Payment Brick.
     * Returns JSON with payment data (payment_id, qr_code for PIX, redirect_url).
     */
    public function process(ProcessCheckoutRequest $request): JsonResponse
    {
        try {
            Log::info('CheckoutController::process - Starting', [
                'request_data' => $request->all(),
            ]);

            $validated = $request->validated();

            Log::info('CheckoutController::process - Validation passed', [
                'validated_keys' => array_keys($validated),
            ]);

            // 1. Save or update address in user_addresses (reusable for future orders)
            /** @var \App\Models\UserAddress $userAddress */
            $userAddress = auth()->user()->addresses()->updateOrCreate(
                [
                    'postal_code' => $validated['postal_code'],
                    'number' => $validated['number'],
                ],
                [
                    'type' => 'shipping',
                    'recipient_name' => $validated['recipient_name'],
                    'street' => $validated['street'],
                    'complement' => $validated['complement'] ?? null,
                    'neighborhood' => $validated['neighborhood'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                ]
            );

            // 2. Prepare shipping data with user_address_id
            $shippingData = [
                'user_address_id' => $userAddress->id,
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'],
                'street' => $validated['street'],
                'number' => $validated['number'],
                'complement' => $validated['complement'] ?? null,
                'neighborhood' => $validated['neighborhood'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'notes' => $validated['notes'] ?? null,
            ];

            // 🛡️ SINGLE-SELLER RESTRICTION: Cart now contains items from only ONE seller
            // Create single order from cart (CartService enforces single-seller rule)
            $orders = $this->orderService->createOrdersFromCart(
                auth()->user(),
                $this->cartService,
                $shippingData
            );

            // Validate that only ONE order was created
            if (count($orders) !== 1) {
                throw new \Exception('Erro: apenas um vendedor é permitido por pedido.');
            }

            $order = $orders[0];

            // Process payment via Payment Brick (PIX, card, etc.)
            $paymentData = $this->paymentService->createPayment(
                $order,
                $validated['payment_data'],
                $validated['payment_method']
            );

            // Cart is already cleared by OrderService

            return response()->json([
                'success' => true,
                'message' => 'Pedido criado com sucesso!',
                'order_id' => $order->id,
                'payment' => $paymentData,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('CheckoutController::process - Validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro de validação: '.implode(', ', array_map(fn ($errors) => implode(', ', $errors), $e->errors())),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('CheckoutController::process - Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar checkout: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Payment success page.
     * Shows order confirmation and payment status.
     */
    public function success(): View
    {
        // Get payment IDs from query string (Mercado Pago redirects with these params)
        $paymentId = request()->query('payment_id');
        $status = request()->query('status');
        $externalReference = request()->query('external_reference');

        // Find order by ID (external_reference)
        $order = null;
        if ($externalReference) {
            $order = Order::find($externalReference);
        }

        return view('checkout.success', [
            'paymentId' => $paymentId,
            'status' => $status,
            'order' => $order,
        ]);
    }

    /**
     * Payment failure page.
     * Shows error message and allows retry.
     */
    public function failure(): View
    {
        $paymentId = request()->query('payment_id');
        $status = request()->query('status');
        $externalReference = request()->query('external_reference');

        $order = null;
        if ($externalReference) {
            $order = Order::find($externalReference);
        }

        return view('checkout.failure', [
            'paymentId' => $paymentId,
            'status' => $status,
            'order' => $order,
        ]);
    }

    /**
     * Payment pending page.
     * Shows pending status message.
     */
    public function pending(): View
    {
        $paymentId = request()->query('payment_id');
        $status = request()->query('status');
        $externalReference = request()->query('external_reference');

        $order = null;
        if ($externalReference) {
            $order = Order::find($externalReference);
        }

        return view('checkout.pending', [
            'paymentId' => $paymentId,
            'status' => $status,
            'order' => $order,
        ]);
    }
}
