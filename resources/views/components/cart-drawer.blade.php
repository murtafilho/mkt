{{-- Cart Drawer - Vanilla JavaScript --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="cartOffcanvasLabel">
            <i class="bi bi-cart3 me-2"></i>
            Carrinho
            <span id="cart-header-count" class="badge bg-primary ms-2" style="display: none;">0</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-0" style="overflow-y: auto; position: relative;">
        {{-- Loading State --}}
        <div id="cart-loading" class="d-flex flex-column align-items-center justify-content-center p-5" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: white; z-index: 10;">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="text-muted small">Carregando carrinho...</p>
        </div>

        {{-- Error State --}}
        <div id="cart-error" class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: white; z-index: 10;">
            <i class="bi bi-exclamation-circle text-danger fs-1 mb-3"></i>
            <p class="fw-medium mb-2">Erro ao carregar carrinho</p>
            <p class="text-muted small mb-3" id="cart-error-message"></p>
            <button onclick="Cart.loadCart()" class="btn btn-primary btn-sm">
                Tentar Novamente
            </button>
        </div>

        {{-- Empty State --}}
        <div id="cart-empty" class="d-flex flex-column align-items-center justify-content-center p-5 text-center" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: white; z-index: 10;">
            <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
            <p class="text-muted mb-3">Seu carrinho está vazio</p>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-grid-3x3-gap me-2"></i>
                Ver Produtos
            </a>
        </div>

        {{-- Cart Items --}}
        <div id="cart-items" class="p-3" style="display: none; position: relative; z-index: 1;">
            <!-- Items serão inseridos aqui via JavaScript -->
        </div>
    </div>

    {{-- Footer --}}
    <div id="cart-footer" class="border-top bg-light p-3" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-medium">Subtotal</span>
            <span class="h5 fw-bold text-primary mb-0">
                R$ <span id="cart-subtotal">0,00</span>
            </span>
        </div>

        <div class="d-grid gap-2">
            <a href="{{ route('cart.index') }}" class="btn btn-primary">
                <i class="bi bi-bag-check me-2"></i>
                Finalizar Pedido
            </a>
            <button onclick="Cart.clearCart()" class="btn btn-link btn-sm text-danger">
                <i class="bi bi-trash me-1"></i>
                Limpar Carrinho
            </button>
        </div>
    </div>
</div>
