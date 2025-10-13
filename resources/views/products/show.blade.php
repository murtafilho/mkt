@extends('layouts.public')

@section('title', $product->name . ' - Vale do Sol')

@section('page-content')
    <div class="bg-white py-4">
        <div class="container">
            {{-- Breadcrumbs --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produtos</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category_id]) }}">{{ $product->category->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 40) }}</li>
                </ol>
            </nav>

            {{-- Product Detail: 2 Columns --}}
            <div class="row g-4 g-lg-5">
                {{-- Left: Image Gallery --}}
                <div class="col-12 col-lg-6">
                    <x-product-gallery :product="$product" />
                </div>

                {{-- Right: Product Info --}}
                <div class="col-12 col-lg-6">
                    {{-- Seller Badge --}}
                    <a href="{{ route('seller.show', $product->seller->slug) }}"
                       class="d-inline-flex align-items-center gap-2 rounded bg-light px-3 py-2 text-decoration-none small mb-3">
                        @if($product->seller->hasMedia('seller_logo'))
                            <img src="{{ $product->seller->getFirstMediaUrl('seller_logo', 'thumb') }}"
                                 alt="{{ $product->seller->store_name }}"
                                 loading="lazy"
                                 class="rounded-circle"
                                 width="24" height="24"
                                 style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                 style="width: 24px; height: 24px;">
                                <span style="font-size: 0.7rem;">👤</span>
                            </div>
                        @endif
                        <span class="text-dark fw-medium">{{ $product->seller->store_name }}</span>
                    </a>

                    {{-- Product Name --}}
                    <h1 class="h2 fw-bold text-dark mb-2">
                        {{ $product->name }}
                    </h1>

                    {{-- Category --}}
                    <p class="small text-muted mb-4">
                        Categoria:
                        <a href="{{ route('products.index', ['category' => $product->category_id]) }}"
                           class="text-primary text-decoration-none fw-medium">
                            {{ $product->category->name }}
                        </a>
                    </p>

                    {{-- Price --}}
                    <div class="mb-4">
                        @if($product->hasDiscount())
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <p class="h4 text-muted text-decoration-line-through mb-0">
                                    R$ {{ number_format($product->original_price, 2, ',', '.') }}
                                </p>
                                <span class="badge bg-danger fw-semibold">
                                    -{{ number_format($product->discount_percent, 0) }}%
                                </span>
                            </div>
                        @endif
                        <p class="display-6 fw-bold text-primary mb-0">
                            R$ {{ number_format($product->sale_price, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- Stock Info --}}
                    <p class="small mb-4">
                        @if($product->stock > 10)
                            <span class="d-inline-flex align-items-center gap-1 text-success fw-medium">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Em estoque ({{ $product->stock }} unidades disponíveis)
                            </span>
                        @elseif($product->stock > 0)
                            <span class="d-inline-flex align-items-center gap-1 text-warning fw-medium">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Últimas {{ $product->stock }} unidades!
                            </span>
                        @else
                            <span class="d-inline-flex align-items-center gap-1 text-danger fw-medium">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Fora de estoque
                            </span>
                        @endif
                    </p>

                    {{-- Add to Cart Form --}}
                    @if($product->stock > 0)
                        <div class="vstack gap-3 mb-4" x-data="{
                            quantity: 1,
                            isAdding: false,
                            async addToCart() {
                                if (this.isAdding) return;
                                
                                this.isAdding = true;
                                
                                try {
                                    const result = await $store.cart.addItem({{ $product->id }}, this.quantity);
                                    
                                    if (result.success) {
                                        const button = this.$refs.addButton;
                                        const originalText = button.innerHTML;
                                        button.innerHTML = '✓ Adicionado!';
                                        button.classList.add('btn-success');
                                        button.classList.remove('btn-secondary');
                                        
                                        setTimeout(() => {
                                            button.innerHTML = originalText;
                                            button.classList.remove('btn-success');
                                            button.classList.add('btn-secondary');
                                            this.isAdding = false;
                                        }, 2000);
                                    }
                                } catch (error) {
                                    console.error('Error adding to cart:', error);
                                    alert('Erro ao adicionar produto ao carrinho');
                                    this.isAdding = false;
                                }
                            }
                        }">
                            <div class="d-flex align-items-center gap-3">
                                <label class="small fw-medium text-secondary mb-0">Quantidade:</label>
                                <input type="number" x-model.number="quantity" min="1" max="{{ $product->stock }}"
                                       class="form-control form-control-sm text-center" style="width: 80px;">
                            </div>
                            <button type="button" 
                                    x-ref="addButton"
                                    @click="addToCart()"
                                    :disabled="isAdding"
                                    class="btn btn-secondary btn-lg w-100">
                                <svg x-show="!isAdding" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-2" style="display: inline-block; vertical-align: middle;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="spinner-border spinner-border-sm me-2" x-show="isAdding" role="status"></span>
                                <span x-show="!isAdding">Adicionar ao Carrinho</span>
                                <span x-show="isAdding">Adicionando...</span>
                            </button>
                        </div>
                    @endif

                    {{-- Description --}}
                    <div class="mt-4 pt-4 border-top">
                        <h2 class="h5 fw-semibold mb-3">Descrição do Produto</h2>
                        <p class="text-secondary" style="white-space: pre-line; line-height: 1.6;">{{ $product->description }}</p>
                    </div>

                    {{-- Specifications --}}
                    @if($product->weight || $product->width || $product->height || $product->length)
                        <div class="mt-4 pt-4 border-top">
                            <h2 class="h5 fw-semibold mb-3">Especificações Técnicas</h2>
                            <dl class="row g-2 small mb-0">
                                @if($product->weight)
                                    <div class="col-6"><dt class="text-muted">Peso:</dt></div>
                                    <div class="col-6 text-end"><dd class="text-dark fw-medium mb-0">{{ number_format($product->weight, 0) }}g</dd></div>
                                @endif
                                @if($product->width && $product->height && $product->length)
                                    <div class="col-6"><dt class="text-muted">Dimensões (L×A×C):</dt></div>
                                    <div class="col-6 text-end"><dd class="text-dark fw-medium mb-0">
                                        {{ number_format($product->width, 1) }} × {{ number_format($product->height, 1) }} × {{ number_format($product->length, 1) }} cm
                                    </dd></div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    {{-- Share & Views --}}
                    <div class="mt-4 pt-4 border-top d-flex align-items-center justify-content-between small text-muted">
                        <div class="d-flex align-items-center gap-2">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ number_format($product->views) }} visualizações</span>
                        </div>
                        <button type="button"
                                onclick="alert('Compartilhamento será implementado na Phase 2')"
                                class="btn btn-link btn-sm text-primary p-0">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="me-1" style="display: inline-block; vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Compartilhar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Related Products --}}
            @if($relatedProducts->count() > 0)
                <div class="mt-5 pt-5 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 fw-bold mb-0">Produtos Relacionados</h2>
                        <a href="{{ route('products.index', ['category' => $product->category_id]) }}"
                           class="btn btn-link text-primary text-decoration-none">
                            Ver todos →
                        </a>
                    </div>
                    <div class="row g-3 g-md-4">
                        @foreach($relatedProducts as $relatedProduct)
                            <div class="col-6 col-md-4 col-lg-3">
                                <x-product-card :product="$relatedProduct" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
