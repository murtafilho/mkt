@extends('layouts.public')

@section('title', 'Checkout - Vale do Sol')

@section('page-content')
<div class="container px-4 py-5" style="max-width: 1200px">
 {{-- Breadcrumbs --}}
 <nav class="mb-4 d-flex align-items-center gap-2 small text-muted">
 <a href="{{ route('home') }}" class="text-decoration-none text-primary">Home</a>
 <svg class="bi" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
 </svg>
 <a href="{{ route('cart.index') }}" class="text-decoration-none text-primary">Carrinho</a>
 <svg class="bi" style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
 </svg>
 <span class="fw-medium text-dark">Checkout</span>
 </nav>

 <div class="row g-4">
 {{-- Main Content (8 cols) --}}
 <div class="col-lg-8">
 <h1 class="mb-4 fs-2 fw-bold text-dark">
 Finalizar Compra
 </h1>

 {{-- Shipping Address Section --}}
 <div class="shipping-section mb-4">
 <div class="rounded border bg-white p-4 shadow-sm">
 <h2 class="mb-3 d-flex align-items-center gap-2 fs-5 fw-semibold text-dark">
 <svg class="bi" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
 </svg>
 Endereço de Entrega
 </h2>

 <form id="checkoutForm">
 @csrf

 {{-- Recipient Info --}}
 <div class="row g-3 mb-3">
 <div class="col-sm-6">
 <label for="recipient_name" class="form-label small fw-medium">
 Nome do Destinatário <span class="text-danger">*</span>
 </label>
 <input
 type="text"
 name="recipient_name"
 id="recipient_name"
 value="{{ $defaultAddress->recipient_name ?? auth()->user()->name }}"
 required
 class="form-control"
 />
 </div>

 <div class="col-sm-6">
 <label for="recipient_phone" class="form-label small fw-medium">
 Telefone <span class="text-danger">*</span>
 </label>
 <input
 type="tel"
 name="recipient_phone"
 id="recipient_phone"
 value="{{ $defaultAddress->recipient_phone ?? '' }}"
 data-mask="phone"
 placeholder="(00) 00000-0000"
 required
 class="form-control"
 />
 </div>
 </div>

 {{-- CEP with auto-lookup --}}
 <div class="mb-3" style="max-width: 50%;" x-data="checkoutCepLookup()">
 <label for="postal_code" class="form-label small fw-medium">
 CEP <span class="text-danger">*</span>
 </label>
 <div class="d-flex gap-2">
 <input
 type="text"
 name="postal_code"
 id="postal_code"
 value="{{ $defaultAddress->postal_code ?? '' }}"
 x-model="postalCode"
 data-mask="cep"
 placeholder="00000-000"
 required
 maxlength="9"
 class="form-control"
 @input="clearMessage"
 />
 <button
 type="button"
 @click="searchCep"
 :disabled="loading || postalCode.replace(/[^0-9]/g, '').length !== 8"
 class="btn btn-primary text-uppercase fw-semibold"
 style="white-space: nowrap;"
 >
 <svg x-show="loading" class="spinner-border spinner-border-sm me-1" role="status" style="width: 1rem; height: 1rem;">
 <span class="visually-hidden">Loading...</span>
 </svg>
 <span x-text="loading ? 'Buscando...' : 'Buscar'"></span>
 </button>
 </div>
 <p x-show="error" x-text="error" class="mt-2 small text-danger"></p>
 <p x-show="success" class="mt-2 small text-success">✓ CEP encontrado! Endereço preenchido automaticamente.</p>
 </div>

 {{-- Street and Number --}}
 <div class="row g-3 mb-3">
 <div class="col-sm-9">
 <label for="street" class="form-label small fw-medium">
 Endereço <span class="text-danger">*</span>
 </label>
 <input
 type="text"
 name="street"
 id="street"
 value="{{ $defaultAddress->street ?? '' }}"
 required
 class="form-control"
 />
 </div>

 <div class="col-sm-3">
 <label for="number" class="form-label small fw-medium">
 Número <span class="text-danger">*</span>
 </label>
 <input
 type="text"
 name="number"
 id="number"
 value="{{ $defaultAddress->number ?? '' }}"
 required
 class="form-control"
 />
 </div>
 </div>

 {{-- Complement and Neighborhood --}}
 <div class="row g-3 mb-3">
 <div class="col-sm-6">
 <label for="complement" class="form-label small fw-medium">
 Complemento
 </label>
 <input
 type="text"
 name="complement"
 id="complement"
 value="{{ $defaultAddress->complement ?? '' }}"
 placeholder="Apto, bloco, etc."
 class="form-control"
 />
 </div>

 <div class="col-sm-6">
 <label for="neighborhood" class="form-label small fw-medium">
 Bairro <span class="text-danger">*</span>
 </label>
 <input
 type="text"
 name="neighborhood"
 id="neighborhood"
 value="{{ $defaultAddress->neighborhood ?? '' }}"
 required
 class="form-control"
 />
 </div>
 </div>

 {{-- City and State --}}
 <div class="row g-3 mb-3">
 <div class="col-sm-8">
 <label for="city" class="form-label small fw-medium">
 Cidade <span class="text-danger">*</span>
 </label>
 <input
 type="text"
 name="city"
 id="city"
 value="{{ $defaultAddress->city ?? '' }}"
 required
 class="form-control"
 />
 </div>

 <div class="col-sm-4">
 <label for="state" class="form-label small fw-medium">
 Estado <span class="text-danger">*</span>
 </label>
 <select
 name="state"
 id="state"
 required
 class="form-select"
 >
 <option value="">Selecione</option>
 @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf)
 <option value="{{ $uf }}" {{ ($defaultAddress->state ?? '') === $uf ? 'selected' : '' }}>{{ $uf }}</option>
 @endforeach
 </select>
 </div>
 </div>

 {{-- Notes --}}
 <div class="mb-3">
 <label for="notes" class="form-label small fw-medium">
 Observações
 </label>
 <textarea
 name="notes"
 id="notes"
 rows="3"
 placeholder="Informações adicionais para entrega"
 class="form-control"
 ></textarea>
 </div>
 </form>
 </div>

 {{-- Order Items by Seller --}}
 <div class="mt-4">
 <h2 class="fs-5 fw-semibold text-dark mb-3">Seus Pedidos</h2>

 @foreach($itemsBySeller as $sellerId => $sellerData)
 <div class="rounded border bg-white shadow-sm mb-3">
 {{-- Seller Header --}}
 <div class="border-bottom bg-light px-4 py-3">
 <div class="d-flex align-items-center gap-2">
 <svg class="bi text-muted" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
 </svg>
 <h3 class="fs-6 fw-semibold text-dark mb-0">{{ $sellerData['seller']->store_name }}</h3>
 </div>
 </div>

 {{-- Items --}}
 <div>
 @foreach($sellerData['items'] as $item)
 <div class="p-3 border-bottom">
 <div class="d-flex gap-3">
 {{-- Product Image --}}
 <div class="flex-shrink-0 rounded bg-light" style="width: 4rem; height: 4rem; overflow: hidden;">
 @if($item->product->hasMedia('images'))
 <img
 src="{{ $item->product->getFirstMediaUrl('images', 'thumb') }}"
 alt="{{ $item->product->name }}"
 class="w-100 h-100"
 style="object-fit: cover;"
 />
 @else
 <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-secondary bg-opacity-25">
 <svg class="bi text-secondary" style="width: 2rem; height: 2rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 @endif
 </div>

 {{-- Product Info --}}
 <div class="d-flex flex-grow-1 justify-content-between">
 <div>
 <h4 class="small fw-medium text-dark mb-1">{{ $item->product->name }}</h4>
 <p class="small text-muted mb-0">Quantidade: {{ $item->quantity }}</p>
 </div>
 <p class="small fw-semibold text-primary mb-0">
 R$ {{ number_format($item->product->sale_price * $item->quantity, 2, ',', '.') }}
 </p>
 </div>
 </div>
 </div>
 @endforeach
 </div>

 {{-- Seller Totals --}}
 <div class="border-top bg-light px-4 py-3">
 <div class="small">
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted">Subtotal</span>
 <span class="fw-medium text-dark">R$ {{ number_format($sellerTotals[$sellerId]['subtotal'], 2, ',', '.') }}</span>
 </div>
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted">Frete</span>
 <span class="fw-medium text-dark">R$ {{ number_format($sellerTotals[$sellerId]['shipping_fee'], 2, ',', '.') }}</span>
 </div>
 <div class="d-flex justify-content-between border-top pt-2">
 <span class="fw-semibold text-dark">Total</span>
 <span class="fw-bold text-primary">R$ {{ number_format($sellerTotals[$sellerId]['total'], 2, ',', '.') }}</span>
 </div>
 </div>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 {{-- Payment Section (hidden initially) --}}
 <div class="payment-section d-none">
 <div class="rounded border bg-white p-4 shadow-sm">
 <h2 class="mb-3 d-flex align-items-center gap-2 fs-5 fw-semibold text-dark">
 <svg class="bi" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
 </svg>
 Forma de Pagamento
 </h2>

 {{-- Payment Brick Container --}}
 <div id="paymentBrick_container"></div>
 </div>
 </div>
 </div>

 {{-- Order Summary Sidebar (4 cols) --}}
 <div class="col-lg-4">
 <div class="sticky-top rounded border bg-white p-4 shadow-sm" style="top: 6rem;">
 <h2 class="mb-3 fs-5 fw-bold text-dark">Resumo Total</h2>

 <div class="border-bottom pb-3 mb-3">
 @foreach($sellerTotals as $sellerId => $totals)
 <div class="small mb-3">
 <div class="fw-medium text-dark">{{ $totals['seller_name'] }}</div>
 <div class="d-flex justify-content-between text-muted mt-1">
 <span>Produtos + Frete</span>
 <span>R$ {{ number_format($totals['total'], 2, ',', '.') }}</span>
 </div>
 </div>
 @endforeach
 </div>

 <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
 <span class="fw-semibold text-dark">Total Geral</span>
 <span class="fs-4 fw-bold text-primary">
 R$ {{ number_format($grandTotal, 2, ',', '.') }}
 </span>
 </div>

 <div class="mb-4">
 <button
 type="button"
 id="processCheckoutBtn"
 class="btn btn-primary w-100 py-3 fw-semibold shadow-sm"
 >
 Continuar para Pagamento
 </button>
 </div>

 {{-- Trust Badges --}}
 <div class="border-top pt-4">
 <div class="d-flex align-items-center gap-2 small text-muted mb-3">
 <svg class="bi text-success" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
 </svg>
 <span>Pagamento 100% seguro</span>
 </div>
 <div class="d-flex align-items-center gap-2 small text-muted mb-3">
 <svg class="bi text-success" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
 </svg>
 <span>PIX instantâneo</span>
 </div>
 <div class="d-flex align-items-center gap-2 small text-muted">
 <svg class="bi text-success" style="width: 1.25rem; height: 1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
 </svg>
 <span>Cartão de crédito/débito</span>
 </div>
 </div>
 </div>
 </div>
 </div>
</div>

{{-- Mercado Pago SDK loaded via NPM (@mercadopago/sdk-js) --}}
<script>
// Alpine.js component for CEP lookup
function checkoutCepLookup() {
 return {
 postalCode: '{{ $defaultAddress->postal_code ?? '' }}',
 loading: false,
 error: null,
 success: false,

 async searchCep() {
 this.loading = true;
 this.error = null;
 this.success = false;

 const cep = this.postalCode.replace(/\D/g, '');

 if (cep.length !== 8) {
 this.error = 'CEP deve ter 8 dígitos';
 this.loading = false;
 return;
 }

 try {
 const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
 const data = await response.json();

 if (data.erro) {
 this.error = 'CEP não encontrado';
 this.loading = false;
 return;
 }

 // Fill address fields
 document.getElementById('street').value = data.logradouro || '';
 document.getElementById('neighborhood').value = data.bairro || '';
 document.getElementById('city').value = data.localidade || '';

 // Set state in select element
 const stateSelect = document.getElementById('state');
 if (stateSelect && data.uf) {
 stateSelect.value = data.uf;
 }

 this.success = true;

 // Focus on number field
 setTimeout(() => {
 document.getElementById('number')?.focus();
 }, 100);
 } catch (error) {
 console.error('Erro ao buscar CEP:', error);
 this.error = 'Erro ao buscar CEP. Tente novamente.';
 } finally {
 this.loading = false;
 }
 },

 clearMessage() {
 this.error = null;
 this.success = false;
 }
 };
}

document.addEventListener('DOMContentLoaded', async function() {
    const checkoutForm = document.getElementById('checkoutForm');
    const processBtn = document.getElementById('processCheckoutBtn');
    let currentStep = 'address'; // 'address' or 'payment'
    let shippingData = null;
    let paymentBrickController = null;
    let mp = null; // MercadoPago instance

    // Initialize Mercado Pago (via NPM package) with error handling
    console.log('🔄 Loading Mercado Pago SDK...');

    try {
        if (typeof window.loadMercadoPago !== 'function') {
            throw new Error('loadMercadoPago function not found. Check if @mercadopago/sdk-js is properly loaded in app.js');
        }

        await window.loadMercadoPago();
        console.log('✅ Mercado Pago SDK loaded successfully');

        if (typeof window.MercadoPago === 'undefined') {
            throw new Error('MercadoPago class not found after loading SDK');
        }

        mp = new window.MercadoPago('{{ config('services.mercadopago.public_key') }}', {
            locale: 'pt-BR'
        });
        console.log('✅ MercadoPago instance created successfully');

        // Make mp accessible globally for debugging
        window.mpInstance = mp;
    } catch (error) {
        console.error('❌ Error initializing Mercado Pago:', error);
        alert('Erro ao carregar sistema de pagamento. Por favor, recarregue a página.');
        return;
    }

 processBtn.addEventListener('click', async function() {
 if (currentStep === 'address') {
 // Step 1: Validate and save shipping address
 if (!checkoutForm.checkValidity()) {
 checkoutForm.reportValidity();
 return;
 }

 // Save shipping data
 const formData = new FormData(checkoutForm);
 shippingData = Object.fromEntries(formData.entries());

 // Hide address form, show payment section
 document.querySelector('.shipping-section').classList.add('d-none');
 document.querySelector('.payment-section').classList.remove('d-none');
 processBtn.classList.add('d-none'); // Payment Brick has its own submit button
 currentStep = 'payment';

 // Render Payment Brick
 await renderPaymentBrick();
 }
 });

    async function renderPaymentBrick() {
        console.log('🎨 Starting Payment Brick rendering...');

        if (!mp) {
            console.error('❌ MercadoPago instance not initialized');
            alert('Erro: Sistema de pagamento não inicializado. Recarregue a página.');
            return;
        }

        const bricksBuilder = mp.bricks();
        console.log('✅ Bricks builder created:', bricksBuilder);

        const grandTotal = {{ $grandTotal }};
        console.log('💰 Grand total:', grandTotal);

        try {
            console.log('🔨 Creating Payment Brick...');
            paymentBrickController = await bricksBuilder.create('payment', 'paymentBrick_container', {
 initialization: {
 amount: grandTotal,
 payer: {
 email: '{{ auth()->user()->email }}',
 },
 },
 customization: {
 paymentMethods: {
 bankTransfer: 'all', // PIX (primeira opção)
 creditCard: 'all',
 debitCard: 'all',
 ticket: 'all', // Boleto
 },
 visual: {
 style: {
 theme: 'default',
 },
 },
 },
                callbacks: {
                    onReady: () => {
                        console.log('✅ Payment Brick pronto e renderizado');
                    },
                    onSubmit: async ({ selectedPaymentMethod, formData }) => {
                        console.log('💳 Payment Brick onSubmit triggered');
                        console.log('💳 Método de pagamento selecionado:', selectedPaymentMethod);
                        console.log('📋 Dados do formulário:', formData);

 try {
 // Combine shipping and payment data
 const checkoutData = {
 ...shippingData,
 payment_data: formData,
 payment_method: selectedPaymentMethod,
 };

 // Send to backend
 const response = await fetch('{{ route('checkout.process') }}', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}'
 },
 body: JSON.stringify(checkoutData)
 });

 const result = await response.json();

 if (result.success) {
 // Build success page URL with payment data
 const params = new URLSearchParams({
 order_id: result.order_id,
 payment_id: result.payment.payment_id,
 status: result.payment.status,
 payment_method: selectedPaymentMethod
 });

 // Add PIX-specific data if available
 if (result.payment.qr_code) {
 params.append('qr_code', result.payment.qr_code);
 }
 if (result.payment.qr_code_base64) {
 params.append('qr_code_base64', result.payment.qr_code_base64);
 }

 window.location.href = '{{ route('checkout.success') }}?' + params.toString();
 } else {
 throw new Error(result.message || 'Erro ao processar pagamento');
 }
 } catch (error) {
 console.error('Erro no pagamento:', error);
 alert(error.message || 'Erro ao processar pagamento. Por favor, tente novamente.');
 throw error; // Re-throw to let Brick handle it
 }
 },
 onError: (error) => {
 console.error('Erro no Payment Brick:', error);
 alert('Erro no formulário de pagamento. Por favor, verifique os dados e tente novamente.');
 },
                    },
                });
                console.log('✅ Payment Brick created successfully:', paymentBrickController);
            } catch (error) {
                console.error('❌ Erro ao criar Payment Brick:', error);
                console.error('❌ Error details:', {
                    message: error.message,
                    stack: error.stack,
                    cause: error.cause
                });
                alert('Erro ao carregar formulário de pagamento. Por favor, recarregue a página.\n\nErro: ' + error.message);
            }
        }
});
</script>
@endsection
