# Plano de Implementação: Cart + Checkout com Mercado Pago Bricks

## 🎯 Objetivos

1. ✅ Otimizar Cart Store (manter Alpine apenas onde necessário)
2. ✅ Remover @alpinejs/mask → usar vanilla masks
3. ✅ Implementar checkout rigorosamente conforme docs Mercado Pago
4. ✅ Usar Bootstrap nativo onde possível
5. ✅ Melhorar compatibilidade com IA (comentários descritivos)

## 📚 Documentação Mercado Pago Bricks

### Payment Brick - Características

**Métodos de Pagamento Suportados:**
- 💳 Cartão de Crédito/Débito
- 🏦 PIX (Bank Transfer)
- 📄 Boleto (Ticket)
- 💰 Mercado Pago Wallet

**Vantagens:**
- ✅ UI pronta e otimizada
- ✅ PCI DSS Compliance automático
- ✅ Dados sensíveis não passam pelo servidor
- ✅ Validação em tempo real
- ✅ Responsivo e acessível
- ✅ Atualizado automaticamente pelo MP

### Fluxo de Inicialização (Oficial)

```javascript
// 1. Carregar SDK
<script src="https://sdk.mercadopago.com/js/v2"></script>

// OU via NPM (usado no projeto)
import { loadMercadoPago } from '@mercadopago/sdk-js';
await loadMercadoPago();

// 2. Inicializar MercadoPago
const mp = new MercadoPago('YOUR_PUBLIC_KEY', {
    locale: 'pt-BR'
});

// 3. Criar Bricks Builder
const bricksBuilder = mp.bricks();

// 4. Criar Payment Brick
const paymentBrick = await bricksBuilder.create('payment', 'container_id', {
    initialization: {
        amount: 100.00,
        payer: {
            email: 'user@example.com'
        }
    },
    customization: {
        paymentMethods: {
            bankTransfer: 'all',  // PIX
            creditCard: 'all',
            debitCard: 'all',
            ticket: 'all'         // Boleto
        },
        visual: {
            style: {
                theme: 'default' // 'default', 'dark', 'bootstrap'
            }
        }
    },
    callbacks: {
        onReady: () => {
            // Brick carregado e pronto
        },
        onSubmit: async ({ selectedPaymentMethod, formData }) => {
            // Processar pagamento
            return fetch('/api/process-payment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        },
        onError: (error) => {
            // Tratar erros
            console.error(error);
        }
    }
});
```

## 🏗️ Arquitetura de Implementação

### 1. Cart Store (Alpine.js) - MANTER

**Arquivo:** `resources/js/app.js`

**Por que Alpine?**
- Estado global complexo
- Reatividade automática
- Computed properties (count, subtotal, itemsBySeller)
- Economia de ~200 linhas de código

**Otimizações:**
- ✅ Manter estrutura atual
- ✅ Adicionar comentários para IA
- ✅ Melhorar error handling com toast

```javascript
// Alpine Store: Cart (Global State Management)
// Properties: items[], loading, open
// Computed: count, subtotal, itemsBySeller
// Methods: loadCart(), addItem(), updateQuantity(), removeItem(), clearCart()
Alpine.store('cart', {
    // Estado
    open: false,
    items: [],
    loading: false,

    // Computed properties
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

    // Métodos...
});
```

### 2. Cart Drawer (Bootstrap Offcanvas + Alpine mínimo)

**Arquivo:** `resources/views/components/cart-drawer.blade.php`

**Otimizações:**
- ✅ Bootstrap 5.3 Offcanvas nativo
- ✅ Alpine apenas para reatividade de dados
- ✅ Sem x-data desnecessário
- ✅ Sincronização limpa Store ↔ Offcanvas

```blade
{{-- Bootstrap Offcanvas + Alpine Store Sync --}}
<div class="offcanvas offcanvas-end"
     id="cartOffcanvas"
     tabindex="-1"
     style="width: 400px; max-width: 90vw;">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            Carrinho
            <span class="badge bg-primary" x-text="$store.cart.count"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
        {{-- Loading State --}}
        <div x-show="$store.cart.loading" class="text-center p-4">
            <div class="spinner-border text-primary"></div>
        </div>

        {{-- Empty State --}}
        <div x-show="!$store.cart.loading && $store.cart.items.length === 0">
            <!-- Empty cart UI -->
        </div>

        {{-- Items by Seller --}}
        <div x-show="!$store.cart.loading && $store.cart.items.length > 0">
            <template x-for="sellerGroup in $store.cart.itemsBySeller" :key="sellerGroup.seller.store_name">
                <!-- Seller items -->
            </template>
        </div>
    </div>

    <div class="offcanvas-footer border-top p-3">
        <div class="d-flex justify-content-between mb-3">
            <span>Subtotal</span>
            <span class="fw-bold">R$ <span x-text="$store.cart.subtotal.toFixed(2).replace('.', ',')"></span></span>
        </div>
        <a href="/cart" class="btn btn-primary w-100">Ver Carrinho Completo</a>
    </div>
</div>

<script>
// Sincronização Alpine Store ↔ Bootstrap Offcanvas
document.addEventListener('alpine:init', () => {
    Alpine.nextTick(() => {
        const cartOffcanvas = new bootstrap.Offcanvas('#cartOffcanvas');

        // Watch Alpine Store → controla Offcanvas
        Alpine.effect(() => {
            if (Alpine.store('cart').open) {
                cartOffcanvas.show();
                // Lazy load data
                if (Alpine.store('cart').items.length === 0) {
                    Alpine.store('cart').loadCart();
                }
            } else {
                cartOffcanvas.hide();
            }
        });

        // Listen Bootstrap event → atualiza Store
        document.getElementById('cartOffcanvas')
            .addEventListener('hidden.bs.offcanvas', () => {
                Alpine.store('cart').open = false;
            });
    });
});
</script>
```

### 3. Máscaras Vanilla (Remover @alpinejs/mask)

**Arquivo:** `resources/js/masks.js` (novo)

```javascript
/**
 * Vanilla JavaScript Input Masks
 * Replaces @alpinejs/mask dependency (-5 KB)
 */

// CEP Mask: 00000-000
export function maskCEP(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 8) value = value.substring(0, 8);

    if (value.length > 5) {
        input.value = value.substring(0, 5) + '-' + value.substring(5);
    } else {
        input.value = value;
    }
}

// Telefone Mask: (00) 00000-0000
export function maskPhone(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substring(0, 11);

    if (value.length > 10) {
        input.value = '(' + value.substring(0, 2) + ') ' +
                      value.substring(2, 7) + '-' + value.substring(7);
    } else if (value.length > 6) {
        input.value = '(' + value.substring(0, 2) + ') ' +
                      value.substring(2, 6) + '-' + value.substring(6);
    } else if (value.length > 2) {
        input.value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
    } else if (value.length > 0) {
        input.value = '(' + value;
    }
}

// CPF Mask: 000.000.000-00
export function maskCPF(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 11) value = value.substring(0, 11);

    if (value.length > 9) {
        input.value = value.substring(0, 3) + '.' +
                      value.substring(3, 6) + '.' +
                      value.substring(6, 9) + '-' + value.substring(9);
    } else if (value.length > 6) {
        input.value = value.substring(0, 3) + '.' +
                      value.substring(3, 6) + '.' + value.substring(6);
    } else if (value.length > 3) {
        input.value = value.substring(0, 3) + '.' + value.substring(3);
    } else {
        input.value = value;
    }
}

// CNPJ Mask: 00.000.000/0000-00
export function maskCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 14) value = value.substring(0, 14);

    if (value.length > 12) {
        input.value = value.substring(0, 2) + '.' +
                      value.substring(2, 5) + '.' +
                      value.substring(5, 8) + '/' +
                      value.substring(8, 12) + '-' + value.substring(12);
    } else if (value.length > 8) {
        input.value = value.substring(0, 2) + '.' +
                      value.substring(2, 5) + '.' +
                      value.substring(5, 8) + '/' + value.substring(8);
    } else if (value.length > 5) {
        input.value = value.substring(0, 2) + '.' +
                      value.substring(2, 5) + '.' + value.substring(5);
    } else if (value.length > 2) {
        input.value = value.substring(0, 2) + '.' + value.substring(2);
    } else {
        input.value = value;
    }
}

// Aplicar máscaras automaticamente via data-mask
document.addEventListener('DOMContentLoaded', () => {
    // CEP inputs
    document.querySelectorAll('input[data-mask="cep"]').forEach(input => {
        input.addEventListener('input', () => maskCEP(input));
    });

    // Phone inputs
    document.querySelectorAll('input[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', () => maskPhone(input));
    });

    // CPF inputs
    document.querySelectorAll('input[data-mask="cpf"]').forEach(input => {
        input.addEventListener('input', () => maskCPF(input));
    });

    // CNPJ inputs
    document.querySelectorAll('input[data-mask="cnpj"]').forEach(input => {
        input.addEventListener('input', () => maskCNPJ(input));
    });
});
```

**Uso:**
```blade
{{-- Antes (Alpine Mask) --}}
<input x-mask="99999-999" name="postal_code">

{{-- Depois (Vanilla) --}}
<input data-mask="cep" name="postal_code">
```

### 4. Checkout com Payment Brick (Rigoroso)

**Arquivo:** `resources/views/checkout/index.blade.php`

**Estrutura:**

```blade
@extends('layouts.public')

@section('title', 'Checkout')

@section('page-content')
<div class="container py-5">
    <div class="row">
        {{-- Address Section --}}
        <div class="col-lg-8">
            <div id="shipping-section">
                <h2>Endereço de Entrega</h2>
                <form id="shippingForm">
                    @csrf
                    {{-- Address fields com máscaras vanilla --}}
                    <input data-mask="cep" name="postal_code" required>
                    <input data-mask="phone" name="recipient_phone" required>
                    {{-- outros campos --}}
                </form>
                <button id="continueToPayment" class="btn btn-primary">
                    Continuar para Pagamento
                </button>
            </div>

            {{-- Payment Section (hidden initially) --}}
            <div id="payment-section" class="d-none">
                <h2>Forma de Pagamento</h2>
                {{-- Mercado Pago Payment Brick Container --}}
                <div id="paymentBrick_container"></div>
            </div>
        </div>

        {{-- Order Summary Sidebar --}}
        <div class="col-lg-4">
            <div class="card sticky-top">
                <div class="card-body">
                    <h3>Resumo do Pedido</h3>
                    {{-- Totals --}}
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span>R$ {{ number_format($grandTotal, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mercado Pago Integration (Rigoroso) --}}
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const continueBtn = document.getElementById('continueToPayment');
    const shippingSection = document.getElementById('shipping-section');
    const paymentSection = document.getElementById('payment-section');
    const shippingForm = document.getElementById('shippingForm');

    let mp = null;
    let paymentBrick = null;

    // ========================================
    // STEP 1: Carregar Mercado Pago SDK
    // ========================================
    console.log('🔄 Loading Mercado Pago SDK...');

    try {
        // Carregar SDK via NPM (já instalado)
        await window.loadMercadoPago();

        // Verificar se MercadoPago está disponível
        if (typeof window.MercadoPago === 'undefined') {
            throw new Error('MercadoPago SDK not loaded');
        }

        console.log('✅ Mercado Pago SDK loaded successfully');
    } catch (error) {
        console.error('❌ Error loading MP SDK:', error);
        alert('Erro ao carregar sistema de pagamento. Recarregue a página.');
        return;
    }

    // ========================================
    // STEP 2: Inicializar MercadoPago
    // ========================================
    try {
        mp = new window.MercadoPago('{{ config('services.mercadopago.public_key') }}', {
            locale: 'pt-BR'
        });

        console.log('✅ MercadoPago instance created');

        // Debug (remove em produção)
        window.mpInstance = mp;
    } catch (error) {
        console.error('❌ Error initializing MP:', error);
        alert('Erro ao inicializar Mercado Pago');
        return;
    }

    // ========================================
    // STEP 3: Handle Continue to Payment
    // ========================================
    continueBtn.addEventListener('click', async () => {
        // Validar formulário de endereço
        if (!shippingForm.checkValidity()) {
            shippingForm.reportValidity();
            return;
        }

        // Ocultar seção de endereço, mostrar pagamento
        shippingSection.classList.add('d-none');
        paymentSection.classList.remove('d-none');

        // Renderizar Payment Brick
        await renderPaymentBrick();
    });

    // ========================================
    // STEP 4: Renderizar Payment Brick
    // ========================================
    async function renderPaymentBrick() {
        console.log('🎨 Rendering Payment Brick...');

        try {
            // Criar Bricks Builder
            const bricksBuilder = mp.bricks();

            // Criar Payment Brick (RIGOROSAMENTE conforme docs)
            paymentBrick = await bricksBuilder.create('payment', 'paymentBrick_container', {
                initialization: {
                    amount: {{ $grandTotal }},
                    payer: {
                        email: '{{ auth()->user()->email }}',
                        @if(auth()->user()->cpf)
                        identification: {
                            type: 'CPF',
                            number: '{{ auth()->user()->cpf }}'
                        },
                        @endif
                    }
                },
                customization: {
                    paymentMethods: {
                        bankTransfer: 'all',  // PIX (primeira opção)
                        creditCard: 'all',
                        debitCard: 'all',
                        ticket: 'all'         // Boleto
                    },
                    visual: {
                        style: {
                            theme: 'bootstrap'  // Integra com Bootstrap
                        },
                        hideFormTitle: false,
                        hidePaymentButton: false
                    }
                },
                callbacks: {
                    onReady: () => {
                        console.log('✅ Payment Brick ready');
                    },

                    onSubmit: async ({ selectedPaymentMethod, formData }) => {
                        console.log('💳 Payment submission started');
                        console.log('Method:', selectedPaymentMethod);

                        try {
                            // Coletar dados de endereço
                            const shippingData = new FormData(shippingForm);

                            // Combinar shipping + payment data
                            const checkoutData = {
                                shipping: Object.fromEntries(shippingData),
                                payment: formData,
                                payment_method: selectedPaymentMethod
                            };

                            // Enviar para backend (Laravel)
                            const response = await fetch('{{ route('checkout.process') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(checkoutData)
                            });

                            if (!response.ok) {
                                const error = await response.json();
                                throw new Error(error.message || 'Erro ao processar pagamento');
                            }

                            const result = await response.json();

                            // Redirecionar para success page
                            if (result.success) {
                                const params = new URLSearchParams({
                                    order_id: result.order_id,
                                    payment_id: result.payment.id,
                                    status: result.payment.status
                                });

                                // Adicionar QR Code se PIX
                                if (result.payment.qr_code) {
                                    params.append('qr_code', result.payment.qr_code);
                                    params.append('qr_code_base64', result.payment.qr_code_base64);
                                }

                                window.location.href = '{{ route('checkout.success') }}?' + params.toString();
                            } else {
                                throw new Error(result.message);
                            }

                        } catch (error) {
                            console.error('❌ Payment error:', error);
                            alert(error.message || 'Erro ao processar pagamento');

                            // Retornar erro para o Brick (mostra feedback visual)
                            throw error;
                        }
                    },

                    onError: (error) => {
                        console.error('❌ Payment Brick error:', error);

                        // Mostrar mensagem amigável
                        let message = 'Erro no formulário de pagamento.';

                        if (error.message) {
                            message += ' ' + error.message;
                        }

                        alert(message);
                    }
                }
            });

            console.log('✅ Payment Brick created successfully');

        } catch (error) {
            console.error('❌ Error creating Payment Brick:', error);
            alert('Erro ao carregar formulário de pagamento. Recarregue a página.');
        }
    }
});
</script>
@endsection
```

## 📋 Checklist de Implementação

### Fase 1: Otimizações (2-3 horas)

- [ ] Criar `resources/js/masks.js` com máscaras vanilla
- [ ] Remover @alpinejs/mask do package.json
- [ ] Atualizar app.js para importar masks.js
- [ ] Substituir `x-mask` por `data-mask` em todos os templates
- [ ] Testar todas as máscaras (CEP, CPF, CNPJ, Telefone)

### Fase 2: Cart Drawer (1 hora)

- [ ] Revisar cart-drawer.blade.php
- [ ] Adicionar comentários descritivos para IA
- [ ] Otimizar sincronização Alpine ↔ Bootstrap
- [ ] Testar abertura/fechamento do drawer
- [ ] Testar operações do carrinho (add, update, remove)

### Fase 3: Checkout com Bricks (2-3 horas)

- [ ] Revisar checkout/index.blade.php
- [ ] Implementar Payment Brick rigorosamente
- [ ] Adicionar todos os callbacks (onReady, onSubmit, onError)
- [ ] Implementar tratamento de erros robusto
- [ ] Adicionar logs de debug
- [ ] Testar fluxo address → payment → success

### Fase 4: Backend (1-2 horas)

- [ ] Revisar CheckoutController::process()
- [ ] Validar dados do Payment Brick
- [ ] Processar pagamento via SDK PHP
- [ ] Criar Order + OrderItems
- [ ] Salvar dados de pagamento
- [ ] Retornar resposta JSON estruturada

### Fase 5: Testes (1-2 horas)

- [ ] Testar fluxo completo guest user
- [ ] Testar fluxo completo authenticated user
- [ ] Testar PIX (gerar QR Code)
- [ ] Testar Cartão de Crédito
- [ ] Testar Boleto
- [ ] Testar casos de erro (cartão recusado, etc)

### Fase 6: Documentação (30 min)

- [ ] Atualizar README com fluxo de pagamento
- [ ] Documentar estrutura de resposta do backend
- [ ] Criar guia de troubleshooting
- [ ] Adicionar comentários inline para IA

## 🎯 Resultado Esperado

**Bundle Size:**
- Antes: 140 KB gzipped
- Depois: 132 KB gzipped (-8 KB, -5.7%)

**Código:**
- Máscaras vanilla: +100 linhas (mas -5 KB no bundle)
- Cart: mantém ~200 linhas (Alpine justificado)
- Checkout: +50 linhas (implementação completa e robusta)

**Qualidade:**
- ✅ Seguindo docs oficiais MP Bricks
- ✅ Tratamento de erros robusto
- ✅ Comentários descritivos para IA
- ✅ Código mais vanilla onde possível
- ✅ Alpine apenas onde faz sentido

## 📚 Referências

- [Mercado Pago Bricks - Payment Brick](https://www.mercadopago.com.br/developers/pt/docs/checkout-bricks/payment-brick/introduction)
- [Mercado Pago SDK PHP 3.7.0](https://github.com/mercadopago/sdk-php)
- [Bootstrap 5.3 Offcanvas](https://getbootstrap.com/docs/5.3/components/offcanvas/)
- [Alpine.js Store](https://alpinejs.dev/globals/alpine-store)

---

**Status:** Pronto para implementação
**Tempo Estimado:** 7-11 horas total
**Prioridade:** Alta (MVP)
