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

    <div class="row g-4">
        {{-- Cart Items Section --}}
        <div class="col-12 col-lg-8">
            <h1 class="h2 fw-bold mb-4">Seu Carrinho</h1>

            @if($cartItems->isEmpty())
                {{-- Empty State --}}
                <div class="card border-2 border-dashed bg-light text-center">
                    <div class="card-body p-5">
                        <i class="bi bi-cart text-muted mb-3" style="font-size: 4rem;"></i>
                        <h2 class="h5 fw-semibold mb-2">Seu carrinho está vazio</h2>
                        <p class="text-muted mb-4">Adicione produtos para começar suas compras</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Ver Produtos
                        </a>
                    </div>
                </div>
            @else
                {{-- Cart Items by Seller --}}
                <div class="vstack gap-4">
                    @foreach($itemsBySeller as $sellerGroup)
                        <div class="card">
                            {{-- Seller Header --}}
                            <div class="card-header bg-light">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-shop"></i>
                                    <h2 class="h6 fw-semibold mb-0">{{ $sellerGroup['seller']->store_name }}</h2>
                                </div>
                            </div>

                            {{-- Items --}}
                            <div class="list-group list-group-flush">
                                @foreach($sellerGroup['items'] as $item)
                                    <div class="list-group-item">
                                        <div class="d-flex gap-3">
                                            {{-- Product Image --}}
                                            <div class="flex-shrink-0 rounded overflow-hidden bg-light" style="width: 100px; height: 100px;">
                                                <img
                                                    src="{{ $item->product->hasImages() ? $item->product->getMainImage()->getUrl('thumb') : '/images/product-placeholder.svg' }}"
                                                    alt="{{ $item->product->name }}"
                                                    loading="lazy"
                                                    class="w-100 h-100 object-fit-cover"
                                                />
                                            </div>

                                            {{-- Product Info --}}
                                            <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between">
                                                        <div class="flex-grow-1">
                                                            <h3 class="h6 fw-medium mb-1">{{ $item->product->name }}</h3>
                                                            <p class="small text-muted mb-0">
                                                                Estoque: {{ $item->product->stock }} unidades
                                                            </p>
                                                        </div>
                                                        <p class="h5 fw-semibold text-primary mb-0 ms-3">
                                                            R$ {{ number_format($item->product->sale_price, 2, ',', '.') }}
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Quantity + Remove (formulários server-side) --}}
                                                <div class="d-flex align-items-center justify-content-between mt-3">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                                                            <button type="submit" class="btn btn-outline-secondary" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                                <i class="bi bi-dash"></i>
                                                            </button>
                                                        </form>
                                                        <span class="btn btn-outline-secondary disabled" style="min-width: 3rem;">{{ $item->quantity }}</span>
                                                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="quantity" value="{{ min($item->product->stock, $item->quantity + 1) }}">
                                                            <button type="submit" class="btn btn-outline-secondary" {{ $item->quantity >= $item->product->stock ? 'disabled' : '' }}>
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <form method="POST" action="{{ route('cart.remove', $item->id) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link btn-sm text-danger">
                                                            <i class="bi bi-trash me-1"></i>
                                                            Remover
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Clear Cart Button --}}
                    <div class="d-flex justify-content-end mt-3">
                        <form method="POST" action="{{ route('cart.clear') }}" onsubmit="return confirm('Deseja realmente limpar o carrinho?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link btn-sm text-danger">
                                Limpar Carrinho
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        {{-- Order Summary Sidebar --}}
        @if($cartItems->isNotEmpty())
        <div class="col-12 col-lg-4">
            <div class="card sticky-top" style="top: 1.5rem;">
                <div class="card-body">
                    <h2 class="h5 fw-bold mb-4">Resumo do Pedido</h2>

                    <div class="vstack gap-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-medium">
                                R$ {{ number_format($subtotal, 2, ',', '.') }}
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
                            R$ {{ number_format($subtotal, 2, ',', '.') }}
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
                            <i class="bi bi-shield-check text-success"></i>
                            <span>Compra 100% segura</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="bi bi-check-circle text-success"></i>
                            <span>Entrega garantida</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="bi bi-headset text-success"></i>
                            <span>Suporte ao cliente</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
