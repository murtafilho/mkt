{{--
    Cart Drawer Component - Bootstrap 5.3 Offcanvas + Alpine.js Store

    Architecture:
    - Bootstrap Offcanvas: Native slide-out drawer (right side)
    - Alpine Store ($store.cart): Global reactive state management
    - Synchronization: Alpine.effect() watches store.open and controls Offcanvas

    State Management:
    - $store.cart.items: Array of cart items
    - $store.cart.loading: Boolean loading state
    - $store.cart.count: Computed getter (total quantity)
    - $store.cart.subtotal: Computed getter (total price)
    - $store.cart.itemsBySeller: Computed getter (grouped by seller)

    Methods:
    - $store.cart.toggle(): Toggle cart visibility
    - $store.cart.addItem(productId, quantity): Add product to cart
    - $store.cart.updateQuantity(cartItemId, quantity): Update item quantity
    - $store.cart.removeItem(cartItemId): Remove item from cart
    - $store.cart.clearCart(): Clear all items
    - $store.cart.loadCart(): Fetch cart data from backend

    Integration Points:
    - Backend API: /cart/data (GET), /cart/add (POST), /cart/update/{id} (PATCH), /cart/remove/{id} (DELETE)
    - CartController: Handles all cart operations
    - CartService: Business logic layer

    See resources/js/app.js for full Alpine Store definition
--}}

<div class="offcanvas offcanvas-end" 
     tabindex="-1" 
     id="cartOffcanvas" 
     aria-labelledby="cartOffcanvasLabel"
     style="width: 400px; max-width: 90vw;">
    
    {{-- Header --}}
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="cartOffcanvasLabel">Carrinho de Compras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-0">
        {{-- Loading State --}}
        <div x-show="$store.cart.loading" x-cloak class="d-flex flex-column align-items-center justify-content-center p-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="text-muted small">Carregando carrinho...</p>
        </div>

        {{-- Error State --}}
        <div x-show="!$store.cart.loading && $store.cart.error" x-cloak class="d-flex flex-column align-items-center justify-content-center p-5 text-center">
            <svg class="text-danger mb-3" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="fs-6 fw-medium mb-2">Erro ao carregar carrinho</p>
            <p class="text-muted small mb-3" x-text="$store.cart.error"></p>
            <button type="button" @click="$store.cart.loadCart()" class="btn btn-primary btn-sm">
                Tentar Novamente
            </button>
        </div>

        {{-- Empty State --}}
        <div x-show="!$store.cart.loading && !$store.cart.error && $store.cart.items.length === 0" x-cloak
             class="d-flex flex-column align-items-center justify-content-center p-5 text-center">
            <svg class="text-muted mb-4" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <p class="fs-5 fw-medium mb-1">Seu carrinho está vazio</p>
            <p class="text-muted small mb-4">Adicione produtos para começar suas compras</p>
            <button type="button" class="btn btn-primary" data-bs-dismiss="offcanvas">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-2" style="display: inline-block; vertical-align: middle;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Continuar Comprando
            </button>
        </div>

        {{-- Cart Items by Seller --}}
        <div x-show="!$store.cart.loading && $store.cart.items.length > 0" x-cloak class="p-3"
             x-init="console.log('🛒 DEBUG Cart Items:', $store.cart.items, 'itemsBySeller:', $store.cart.itemsBySeller)">
            <template x-for="sellerGroup in $store.cart.itemsBySeller" :key="sellerGroup.seller.store_name">
                <div class="card mb-3">
                    {{-- Seller Header --}}
                    <div class="card-header bg-light py-2">
                        <div class="d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <small class="fw-semibold text-secondary mb-0" x-text="sellerGroup.seller.store_name"></small>
                        </div>
                    </div>

                    {{-- Items --}}
                    <div class="list-group list-group-flush">
                        <template x-for="item in sellerGroup.items" :key="item.id">
                            <div class="list-group-item p-3">
                                <div class="d-flex gap-3">
                                    {{-- Product Image --}}
                                    <div class="flex-shrink-0">
                                        <img :src="item.product.image_url" 
                                             :alt="item.product.name" 
                                             class="rounded" 
                                             width="60" height="60"
                                             style="object-fit: cover;">
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="flex-grow-1">
                                        <h6 class="small mb-1 line-clamp-2" x-text="item.product.name"></h6>
                                        <p class="small text-primary fw-bold mb-2">
                                            R$ <span x-text="item.price.toFixed(2).replace('.', ',')"></span>
                                        </p>
                                        
                                        {{-- Quantity + Remove --}}
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="btn-group btn-group-sm">
                                                <button @click.stop="$store.cart.updateQuantity(item.id, item.quantity - 1)"
                                                        :disabled="item.quantity <= 1"
                                                        class="btn btn-outline-secondary">−</button>
                                                <span class="btn btn-outline-secondary disabled" x-text="item.quantity" style="min-width: 2.5rem;"></span>
                                                <button @click.stop="$store.cart.updateQuantity(item.id, item.quantity + 1)"
                                                        :disabled="item.quantity >= item.product.stock"
                                                        class="btn btn-outline-secondary">+</button>
                                            </div>
                                            <button @click.stop="$store.cart.removeItem(item.id)"
                                                    class="btn btn-link btn-sm text-danger p-0">
                                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Footer com Total --}}
    <div x-show="!$store.cart.loading && $store.cart.items.length > 0"
         x-init="console.log('🛒 DEBUG Footer - Subtotal:', $store.cart.subtotal)"
         class="border-top bg-light p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-medium">Subtotal</span>
            <span class="h5 fw-bold text-primary mb-0">
                R$ <span x-text="$store.cart.subtotal.toFixed(2).replace('.', ',')"></span>
            </span>
        </div>
        <p class="small text-muted mb-3">Frete calculado no checkout</p>
        <div class="d-grid gap-2">
            <a href="/cart" class="btn btn-primary">Ver Carrinho Completo</a>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                Continuar Comprando
            </button>
            <button @click.stop="$store.cart.clearCart()" type="button" class="btn btn-link btn-sm text-danger">
                Limpar Carrinho
            </button>
        </div>
    </div>
</div>

{{--
    Bootstrap Offcanvas ↔ Alpine Store Synchronization

    This script creates a two-way binding between:
    1. Bootstrap's Offcanvas component (native Bootstrap 5.3)
    2. Alpine Store's reactive state ($store.cart.open)

    Flow:
    1. Alpine.effect() watches $store.cart.open changes
    2. When open = true → Bootstrap shows offcanvas
    3. When open = false → Bootstrap hides offcanvas
    4. When user closes via Bootstrap → Updates Alpine state
    5. Lazy loads cart data only when first opened

    Why this pattern?
    - Best of both worlds: Bootstrap's polished UI + Alpine's reactivity
    - Single source of truth: $store.cart.open
    - Works with both: @click="$store.cart.toggle()" and Bootstrap's data-bs-toggle
--}}
<script>
// Simplified Cart Offcanvas Initialization
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        console.log('🔄 Initializing Cart Offcanvas (simplified)...');

        // Initialize Bootstrap Offcanvas
        const cartOffcanvasEl = document.getElementById('cartOffcanvas');
        if (!cartOffcanvasEl) {
            console.error('❌ Cart offcanvas element not found!');
            return;
        }

        const cartOffcanvas = new bootstrap.Offcanvas(cartOffcanvasEl, {
            backdrop: true,
            keyboard: true,
            scroll: false
        });

        console.log('✅ Bootstrap Offcanvas initialized');

        // Wait for Alpine to be available
        const initAlpineSync = () => {
            if (typeof Alpine === 'undefined' || !Alpine.store) {
                console.log('⏳ Waiting for Alpine...');
                setTimeout(initAlpineSync, 100);
                return;
            }

            const cartStore = Alpine.store('cart');
            if (!cartStore) {
                console.error('❌ Cart store not found!');
                return;
            }

            console.log('✅ Cart store found, syncing...');

            // Alpine → Bootstrap: Watch store changes (only 'open' state)
            Alpine.effect(() => {
                const isOpen = cartStore.open;
                console.log('🛒 Cart state:', isOpen ? 'OPEN' : 'CLOSED');

                if (isOpen) {
                    cartOffcanvas.show();
                } else {
                    cartOffcanvas.hide();
                }
            });

            // Bootstrap → Alpine: Sync when user closes
            cartOffcanvasEl.addEventListener('hidden.bs.offcanvas', () => {
                console.log('🛒 User closed offcanvas');
                cartStore.open = false;
            });

            console.log('✅ Cart Offcanvas ↔ Alpine Store synchronized');
        };

        initAlpineSync();
    }, 200);
});
</script>

@once
<style>
[x-cloak] {
    display: none !important;
}
</style>
@endonce