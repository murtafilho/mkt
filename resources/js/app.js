import './bootstrap';

// Import Bootstrap JavaScript via npm
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import Alpine from 'alpinejs';

// Import Chart.js (for admin dashboard and reports)
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import Cropper.js (for image upload/crop functionality)
import Cropper from 'cropperjs';
window.Cropper = Cropper;

// Import Mercado Pago SDK
import { loadMercadoPago } from '@mercadopago/sdk-js';
window.loadMercadoPago = loadMercadoPago;

// Import Vanilla Masks (replaces @alpinejs/mask)
import './masks';

window.Alpine = Alpine;

// Cart Global Store - SIMPLES E LIMPO
document.addEventListener('alpine:init', () => {
    console.log('🚀 Inicializando cart store...');

    Alpine.store('cart', {
        // Estado inicial
        open: false,
        items: [],
        loading: false,
        error: null,

        // Propriedades calculadas
        get count() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },

        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get itemsBySeller() {
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

        // Métodos principais
        toggle() {
            this.open = !this.open;
            console.log('🛒 Cart toggle:', this.open);
        },

        close() {
            this.open = false;
            console.log('🛒 Cart closed');
        },

        // Carregar dados do carrinho
        async loadCart() {
            console.log('🛒 Carregando carrinho...');
            this.loading = true;
            this.error = null;

            // Timeout de segurança - se não carregar em 5s, resetar
            const timeoutId = setTimeout(() => {
                if (this.loading) {
                    console.error('⏱️ Timeout ao carregar carrinho - resetando estado');
                    this.loading = false;
                    this.error = 'Timeout ao carregar carrinho';
                    this.items = [];
                }
            }, 5000);

            try {
                console.log('📡 Fazendo requisição para /cart/data...');
                const response = await fetch('/cart/data', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                console.log('📡 Resposta recebida:', response.status);

                if (response.ok) {
                    const data = await response.json();
                    console.log('📦 Dados recebidos:', data);
                    this.items = data.items || [];
                    console.log('✅ Carrinho carregado:', this.items.length, 'itens');
                } else {
                    console.error('❌ Erro HTTP:', response.status);
                    this.error = `Erro HTTP: ${response.status}`;
                    this.items = [];
                }
            } catch (error) {
                console.error('❌ Erro na requisição:', error);
                this.error = error.message;
                this.items = [];
            } finally {
                clearTimeout(timeoutId);
                this.loading = false;
                console.log('🏁 loadCart finalizado. Loading:', this.loading, 'Items:', this.items.length);
            }
        },

        // Adicionar item ao carrinho
        async addItem(productId, quantity = 1) {
            console.log('🛒 Adicionando produto:', productId, 'qty:', quantity);
            this.loading = true;

            try {
                const response = await fetch('/cart/add', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.items = data.cart.items;
                    this.open = true; // Abrir carrinho automaticamente
                    console.log('🛒 Produto adicionado com sucesso');
                    return { success: true, message: data.message };
                } else {
                    console.error('🛒 Erro ao adicionar:', data.message);
                    alert(data.message || 'Erro ao adicionar produto');
                    return { success: false, message: data.message };
                }
            } catch (error) {
                console.error('🛒 Erro na requisição:', error);
                alert('Erro ao adicionar produto ao carrinho');
                return { success: false, message: 'Erro ao adicionar produto ao carrinho' };
            } finally {
                this.loading = false;
            }
        },

        // Atualizar quantidade
        async updateQuantity(cartItemId, quantity) {
            console.log('🛒 Atualizando quantidade:', cartItemId, 'para:', quantity);
            this.loading = true;

            try {
                const response = await fetch(`/cart/update/${cartItemId}`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ quantity })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.items = data.cart.items;
                    console.log('🛒 Quantidade atualizada');
                    return { success: true, message: data.message };
                } else {
                    console.error('🛒 Erro ao atualizar:', data.message);
                    return { success: false, message: data.message };
                }
            } catch (error) {
                console.error('🛒 Erro na requisição:', error);
                return { success: false, message: 'Erro ao atualizar carrinho' };
            } finally {
                this.loading = false;
            }
        },

        // Remover item
        async removeItem(cartItemId) {
            console.log('🛒 Removendo item:', cartItemId);
            this.loading = true;

            try {
                const response = await fetch(`/cart/remove/${cartItemId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.items = data.cart.items;
                    console.log('🛒 Item removido');
                    return { success: true, message: data.message };
                } else {
                    console.error('🛒 Erro ao remover:', data.message);
                    return { success: false, message: data.message };
                }
            } catch (error) {
                console.error('🛒 Erro na requisição:', error);
                return { success: false, message: 'Erro ao remover item do carrinho' };
            } finally {
                this.loading = false;
            }
        },

        // Limpar carrinho
        async clearCart() {
            if (!confirm('Deseja realmente limpar o carrinho?')) {
                return;
            }

            console.log('🛒 Limpando carrinho...');
            this.loading = true;

            try {
                const response = await fetch('/cart/clear', {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.items = [];
                    this.close();
                    console.log('🛒 Carrinho limpo');
                    return { success: true, message: data.message };
                } else {
                    console.error('🛒 Erro ao limpar:', data.message);
                    return { success: false, message: data.message };
                }
            } catch (error) {
                console.error('🛒 Erro na requisição:', error);
                return { success: false, message: 'Erro ao limpar carrinho' };
            } finally {
                this.loading = false;
            }
        }
    });

    console.log('✅ Cart store registrado com sucesso');
});

// Inicializar carrinho automaticamente quando Alpine estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    // Aguardar Alpine inicializar
    setTimeout(() => {
        if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('cart')) {
            console.log('🔄 Carregando carrinho automaticamente...');
            Alpine.store('cart').loadCart();
        } else {
            console.error('❌ Alpine Store não disponível para inicialização automática');
        }
    }, 500);
});

// Search Autocomplete Component
document.addEventListener('alpine:init', () => {
    Alpine.data('searchAutocomplete', () => ({
        query: '',
        products: [],
        sellers: [],
        showSuggestions: false,
        loading: false,

        async performSearch() {
            const query = this.query.trim();

            // Minimum 2 characters
            if (query.length < 2) {
                this.products = [];
                this.sellers = [];
                this.showSuggestions = false;
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
                const data = await response.json();

                this.products = data.products || [];
                this.sellers = data.sellers || [];
                this.showSuggestions = true;
            } catch (error) {
                console.error('Erro na busca:', error);
                this.products = [];
                this.sellers = [];
            } finally {
                this.loading = false;
            }
        }
    }));
});

console.log('🎯 Iniciando Alpine.js...');
Alpine.start();
console.log('✅ Alpine.js iniciado');

/**
 * Header Scroll Effect - Minimalista 2025
 */
window.addEventListener('scroll', () => {
    const header = document.querySelector('header.sticky-top');
    if (header) {
        if (window.scrollY > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }
}, { passive: true });