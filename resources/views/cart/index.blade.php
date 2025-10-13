@extends('layouts.public')

@section('title', 'Carrinho de Compras - Vale do Sol')

@section('page-content')
<div class="container py-4">
        {{-- Breadcrumbs --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Carrinho</li>
            </ol>
        </nav>

        <div x-data class="row g-4">
            {{-- Cart Items Section (8 cols) --}}
            <div class="col-12 col-lg-8">
                <h1 class="h2 fw-bold mb-4">
                    Seu Carrinho
                </h1>

                {{-- Loading State --}}
                <div x-show="$store.cart.loading" class="d-flex align-items-center justify-content-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>

                {{-- Empty State --}}
                <div x-show="!$store.cart.loading && $store.cart.items.length === 0" class="card border-2 border-dashed bg-light text-center">
                    <div class="card-body p-5">
                        <svg class="mx-auto text-muted mb-3" width="64" height="64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h2 class="h5 fw-semibold mb-2">Seu carrinho está vazio</h2>
                        <p class="text-muted mb-4">Adicione produtos para começar suas compras</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-2" style="display: inline-block; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Ver Produtos
                        </a>
                    </div>
                </div>

                {{-- Cart Items by Seller --}}
                <div x-show="!$store.cart.loading && $store.cart.items.length > 0" class="vstack gap-4">
                    <template x-for="sellerGroup in $store.cart.itemsBySeller" :key="sellerGroup.seller.store_name">
                        <div class="card">
                            {{-- Seller Header --}}
                            <div class="card-header bg-light">
                                <div class="d-flex align-items-center gap-2">
                                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <h2 class="h6 fw-semibold mb-0" x-text="sellerGroup.seller.store_name"></h2>
                                </div>
                            </div>

                            {{-- Items from this Seller --}}
                            <div class="list-group list-group-flush">
                                <template x-for="item in sellerGroup.items" :key="item.id">
                                    <div class="list-group-item">
                                        <div class="d-flex gap-3">
                                            {{-- Product Image --}}
                                            <div class="flex-shrink-0 rounded overflow-hidden bg-light" style="width: 100px; height: 100px;">
                                                <img
                                                    :src="item.product.image_url || '/images/product-placeholder.png'"
                                                    :alt="item.product.name"
                                                    loading="lazy"
                                                    class="w-100 h-100 object-fit-cover"
                                                />
                                            </div>

                                            {{-- Product Info --}}
                                            <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="flex-grow-1">
                                                            <h3 class="h6 fw-medium mb-1" x-text="item.product.name"></h3>
                                                            <p class="small text-muted mb-0">
                                                                Estoque: <span x-text="item.product.stock"></span> unidades
                                                            </p>
                                                        </div>
                                                        <p class="h5 fw-semibold text-primary mb-0 ms-3">
                                                            R$ <span x-text="(item.price).toFixed(2).replace('.', ',')"></span>
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Quantity Controls + Remove --}}
                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                    <div class="btn-group" role="group">
                                                        <button
                                                            @click="item.quantity > 1 && $store.cart.updateQuantity(item.id, item.quantity - 1)"
                                                            :disabled="item.quantity <= 1"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            aria-label="Diminuir quantidade">
                                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <span class="btn btn-outline-secondary btn-sm disabled" x-text="item.quantity" style="min-width: 3rem;"></span>
                                                        <button
                                                            @click="item.quantity < item.product.stock && $store.cart.updateQuantity(item.id, item.quantity + 1)"
                                                            :disabled="item.quantity >= item.product.stock"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            aria-label="Aumentar quantidade">
                                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>

                                                    <button
                                                        @click="$store.cart.removeItem(item.id)"
                                                        class="btn btn-link btn-sm text-danger">
                                                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1" style="display: inline-block; vertical-align: middle;">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Remover
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Clear Cart Button --}}
                    <div class="d-flex justify-content-end mt-3">
                        <button
                            @click="$store.cart.clearCart()"
                            class="btn btn-link btn-sm text-danger">
                            Limpar Carrinho
                        </button>
                    </div>
                </div>
            </div>

            {{-- Order Summary Sidebar (4 cols) --}}
            <div x-show="!$store.cart.loading && $store.cart.items.length > 0" class="col-12 col-lg-4">
                <div class="card sticky-top" style="top: 1.5rem;">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-4">Resumo do Pedido</h2>

                        <div class="vstack gap-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-medium">
                                    R$ <span x-text="$store.cart.subtotal.toFixed(2).replace('.', ',')"></span>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Frete</span>
                                <span class="text-muted">Calculado no checkout</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between py-3 border-bottom">
                            <span class="fw-semibold">Total</span>
                            <span class="fs-5 fw-bold text-primary">
                                R$ <span x-text="$store.cart.subtotal.toFixed(2).replace('.', ',')"></span>
                            </span>
                        </div>

                        <div class="vstack gap-2 mt-4">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100">
                                Finalizar Compra
                            </a>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">
                                Continuar Comprando
                            </a>
                        </div>

                        {{-- Trust Badges --}}
                        <div class="vstack gap-3 pt-4 mt-4 border-top">
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <svg class="text-success" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Compra 100% segura</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <svg class="text-success" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Entrega garantida</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <svg class="text-success" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Suporte ao cliente</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection
