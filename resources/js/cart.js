/**
 * Cart - Vanilla JavaScript
 * Gerenciamento simples do carrinho sem Alpine.js
 */

const Cart = {
    // Estados
    items: [],
    loading: false,

    // Elementos DOM
    els: {
        loading: null,
        error: null,
        errorMessage: null,
        empty: null,
        items: null,
        footer: null,
        subtotal: null,
        headerCount: null,
        headerBadge: null,
    },

    // Inicializar
    init() {
        console.log('🛒 Cart.init()');

        // Cachear elementos DOM
        this.els.loading = document.getElementById('cart-loading');
        this.els.error = document.getElementById('cart-error');
        this.els.errorMessage = document.getElementById('cart-error-message');
        this.els.empty = document.getElementById('cart-empty');
        this.els.items = document.getElementById('cart-items');
        this.els.footer = document.getElementById('cart-footer');
        this.els.subtotal = document.getElementById('cart-subtotal');
        this.els.headerCount = document.getElementById('cart-header-count');
        this.els.headerBadge = document.querySelector('.cart-badge');
        
        // Event listener para abrir offcanvas
        const cartOffcanvas = document.getElementById('cartOffcanvas');
        if (cartOffcanvas) {
            cartOffcanvas.addEventListener('show.bs.offcanvas', () => {
                console.log('🛒 Offcanvas abrindo...');
                // SÓ carregar se não houver items ainda
                if (this.items.length === 0) {
                    // Delay de 300ms para evitar race condition com sessão Laravel
                    setTimeout(() => {
                        this.loadCart();
                    }, 300);
                } else {
                    this.render();
                }
            });
        }
        
        // Carregar carrinho na inicialização para atualizar badge
        this.loadCart();
    },

    // Mostrar estado
    showState(state) {
        console.log('👁️ showState chamado com:', state);

        // Esconder todos AGRESSIVAMENTE (display + visibility)
        this.els.loading.style.display = 'none';
        this.els.loading.style.visibility = 'hidden';
        this.els.error.style.display = 'none';
        this.els.error.style.visibility = 'hidden';
        this.els.empty.style.display = 'none';
        this.els.empty.style.visibility = 'hidden';
        this.els.items.style.display = 'none';
        this.els.items.style.visibility = 'hidden';
        this.els.footer.style.display = 'none';
        this.els.footer.style.visibility = 'hidden';

        console.log('✅ Todos estados escondidos');

        // Mostrar estado solicitado
        if (state === 'loading') {
            console.log('🔄 Mostrando LOADING');
            this.els.loading.style.display = 'flex';
            this.els.loading.style.visibility = 'visible';
        } else if (state === 'error') {
            console.log('❌ Mostrando ERROR');
            this.els.error.style.display = 'flex';
            this.els.error.style.visibility = 'visible';
        } else if (state === 'empty') {
            console.log('📭 Mostrando EMPTY');
            this.els.empty.style.display = 'flex';
            this.els.empty.style.visibility = 'visible';
        } else if (state === 'items') {
            console.log('📦 Mostrando ITEMS');
            this.els.items.style.display = 'block';
            this.els.items.style.visibility = 'visible';
            this.els.footer.style.display = 'block';
            this.els.footer.style.visibility = 'visible';
            console.log('✅ Items display:', this.els.items.style.display);
            console.log('✅ Footer display:', this.els.footer.style.display);
        }

        console.log('👁️ showState finalizado');
    },

    // Carregar carrinho
    async loadCart() {
        console.log('🔄 Cart.loadCart() - INÍCIO');
        console.log('🔄 this.loading:', this.loading);

        // Prevenir chamadas concorrentes
        if (this.loading) {
            console.log('⚠️ JÁ está carregando - ignorando chamada duplicada');
            return;
        }

        this.loading = true;
        this.showState('loading');

        try {
            console.log('📡 Fazendo fetch para /cart/data...');

            const response = await fetch('/cart/data', {
                headers: { 'Accept': 'application/json' }
            });

            console.log('📡 Response STATUS:', response.status);
            console.log('📡 Response OK:', response.ok);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 RAW Data recebida:', JSON.stringify(data, null, 2));
            console.log('📦 data.items:', data.items);
            console.log('📦 Array.isArray(data.items):', Array.isArray(data.items));
            console.log('📦 data.items.length:', data.items ? data.items.length : 'undefined');

            this.items = data.items || [];
            console.log('✅ this.items atribuído:', this.items.length, 'items');
            console.log('✅ this.items conteúdo:', this.items);

            console.log('🎨 Chamando render()...');
            this.render();
            console.log('✅ render() finalizado');

        } catch (error) {
            console.error('❌ ERRO CAPTURADO:', error);
            console.error('❌ Error stack:', error.stack);
            this.els.errorMessage.textContent = error.message;
            this.showState('error');
        } finally {
            this.loading = false;
            console.log('🔄 this.loading resetado para false');
        }

        console.log('🔄 Cart.loadCart() - FIM');
    },

    // Renderizar carrinho
    render() {
        console.log('🎨 Cart.render() -', this.items.length, 'items');
        console.log('📦 Items:', this.items);

        if (this.items.length === 0) {
            console.log('⚠️ Carrinho vazio - mostrando empty state');
            this.showState('empty');
            this.updateBadge(0);
            return;
        }

        console.log('✅ Renderizando items...');

        // Agrupar por seller
        const grouped = this.groupBySeller();

        // Gerar HTML
        let html = '';
        for (const seller of grouped) {
            html += this.renderSellerGroup(seller);
        }

        this.els.items.innerHTML = html;

        // Atualizar subtotal
        const subtotal = this.calculateSubtotal();
        this.els.subtotal.textContent = this.formatMoney(subtotal);

        // Atualizar badge
        const count = this.items.reduce((sum, item) => sum + item.quantity, 0);
        this.updateBadge(count);

        this.showState('items');
    },

    // Agrupar items por seller
    groupBySeller() {
        const grouped = {};

        this.items.forEach(item => {
            const sellerId = item.seller_id;
            if (!grouped[sellerId]) {
                grouped[sellerId] = {
                    seller: item.seller,
                    items: []
                };
            }
            grouped[sellerId].items.push(item);
        });

        return Object.values(grouped);
    },

    // Renderizar grupo de seller
    renderSellerGroup(group) {
        let html = '<div class="cart-seller-group mb-4">';

        // Seller header
        html += '<div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">';
        html += '<i class="bi bi-shop text-primary"></i>';
        html += `<span class="fw-medium small">${group.seller.store_name}</span>`;
        html += '</div>';

        // Items
        group.items.forEach(item => {
            html += this.renderItem(item);
        });

        html += '</div>';
        return html;
    },

    // Renderizar item
    renderItem(item) {
        const imageUrl = item.product.image_url || '/images/product-placeholder.svg';

        return `
            <div class="cart-item d-flex gap-3 pb-3 mb-3 border-bottom">
                <img src="${imageUrl}" alt="${item.product.name}"
                     class="rounded" style="width: 80px; height: 80px; object-fit: cover;">

                <div class="flex-grow-1">
                    <h6 class="mb-1 small fw-medium">${item.product.name}</h6>
                    <p class="mb-2 small text-muted">R$ ${this.formatMoney(item.price)}</p>

                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm">
                            <button onclick="Cart.updateQuantity(${item.id}, ${item.quantity - 1})"
                                    ${item.quantity <= 1 ? 'disabled' : ''}
                                    class="btn btn-outline-secondary" style="width: 32px;">−</button>
                            <span class="btn btn-outline-secondary disabled" style="width: 50px; pointer-events: none;">${item.quantity}</span>
                            <button onclick="Cart.updateQuantity(${item.id}, ${item.quantity + 1})"
                                    ${item.quantity >= item.product.stock ? 'disabled' : ''}
                                    class="btn btn-outline-secondary" style="width: 32px;">+</button>
                        </div>

                        <button onclick="Cart.removeItem(${item.id})"
                                class="btn btn-link btn-sm text-danger p-0 ms-auto">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    },

    // Calcular subtotal
    calculateSubtotal() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    // Formatar dinheiro
    formatMoney(value) {
        return value.toFixed(2).replace('.', ',');
    },

    // Atualizar badge
    updateBadge(count) {
        console.log('🏷️ updateBadge:', count);
        
        if (count > 0) {
            if (this.els.headerCount) {
                this.els.headerCount.textContent = count;
                this.els.headerCount.style.display = 'inline';
            }
            if (this.els.headerBadge) {
                this.els.headerBadge.textContent = count;
                this.els.headerBadge.style.display = 'inline';
            }
        } else {
            if (this.els.headerCount) {
                this.els.headerCount.style.display = 'none';
            }
            if (this.els.headerBadge) {
                this.els.headerBadge.style.display = 'none';
            }
        }
    },

    // Adicionar item
    async addItem(productId, quantity = 1) {
        console.log('➕ Cart.addItem:', productId, quantity);

        try {
            const response = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ product_id: productId, quantity })
            });

            console.log('📡 Response:', response.status);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erro ao adicionar produto');
            }

            const data = await response.json();
            console.log('✅ Success:', data);
            console.log('✅ Cart data received:', data.cart);

            // USAR OS DADOS QUE JÁ VIERAM NA RESPOSTA (sem race condition!)
            this.items = data.cart.items || [];
            console.log('✅ Items atribuídos diretamente:', this.items.length, 'items');

            // Renderizar ANTES de abrir offcanvas (para que this.items.length > 0)
            this.render();

            // Agora abrir offcanvas (evento verá this.items.length > 0 e não chamará loadCart)
            const offcanvasEl = document.getElementById('cartOffcanvas');
            if (offcanvasEl) {
                const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
                offcanvas.show();
            }

            return true;
        } catch (error) {
            console.error('❌ Erro:', error);
            alert(`Erro: ${error.message}`);
            return false;
        }
    },

    // Atualizar quantidade
    async updateQuantity(itemId, quantity) {
        if (quantity < 1) return;

        console.log('🔄 Cart.updateQuantity:', itemId, quantity);

        try {
            const response = await fetch(`/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ quantity })
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            console.log('✅ Update success:', data);

            // Usar dados da resposta
            this.items = data.cart.items || [];
            this.render();
        } catch (error) {
            console.error('❌ Erro:', error);
            alert('Erro ao atualizar quantidade');
        }
    },

    // Remover item
    async removeItem(itemId) {
        console.log('🗑️ Cart.removeItem:', itemId);

        try {
            const response = await fetch(`/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            console.log('✅ Remove success:', data);

            // Usar dados da resposta
            this.items = data.cart.items || [];
            this.render();
        } catch (error) {
            console.error('❌ Erro:', error);
            alert('Erro ao remover item');
        }
    },

    // Limpar carrinho
    async clearCart() {
        if (!confirm('Deseja realmente limpar o carrinho?')) return;

        console.log('🧹 Cart.clearCart()');

        try {
            const response = await fetch('/cart/clear', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            this.items = [];
            this.render();
        } catch (error) {
            console.error('❌ Erro:', error);
            alert('Erro ao limpar carrinho');
        }
    }
};

// Inicializar quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    Cart.init();
});

// Expor globalmente
window.Cart = Cart;

// Função helper para botão add to cart
window.addToCartBtn = async function(button, productId) {
    // Desabilitar botão
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    // Adicionar ao carrinho
    const success = await Cart.addItem(productId, 1);

    // Feedback visual
    if (success) {
        button.classList.remove('btn-primary');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="bi bi-check-lg"></i>';

        setTimeout(() => {
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
            button.innerHTML = originalHTML;
            button.disabled = false;
        }, 2000);
    } else {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
};
