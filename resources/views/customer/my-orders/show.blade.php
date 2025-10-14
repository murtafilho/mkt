@extends('layouts.public')

@section('page-content')
<div class="container py-5">
    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('customer.orders.index') }}">Meus Pedidos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pedido #{{ $order->order_number }}</li>
        </ol>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 fw-bold mb-2">
                        <i class="bi bi-bag me-2 text-primary"></i>
                        Pedido #{{ $order->order_number }}
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3 me-1"></i>
                        Realizado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
                    </p>
                </div>
                <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'primary') }} fs-5">
                    {{ $order->status_label }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Left column: Order details --}}
        <div class="col-lg-8">
            {{-- Order Items --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="h5 mb-0 fw-semibold">
                        <i class="bi bi-box-seam me-2"></i>
                        Itens do Pedido
                    </h3>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($order->items as $item)
                            <div class="list-group-item px-0">
                                <div class="d-flex gap-3">
                                    {{-- Product image --}}
                                    @if($item->product && $item->product->hasMedia('product_images'))
                                        <img src="{{ $item->product->getFirstMedia('product_images')->getUrl('thumb') }}"
                                             alt="{{ $item->product->name }}"
                                             loading="lazy"
                                             class="rounded border"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                             style="width: 80px; height: 80px;">
                                            <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif

                                    {{-- Product info --}}
                                    <div class="flex-fill">
                                        <h4 class="h6 fw-semibold mb-1">{{ $item->product->name ?? 'Produto não disponível' }}</h4>
                                        <p class="small text-muted mb-1">Quantidade: {{ $item->quantity }}x</p>
                                        <p class="small text-muted mb-0">
                                            Preço unitário: R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                        </p>
                                    </div>

                                    {{-- Subtotal --}}
                                    <div class="text-end">
                                        <p class="h5 fw-bold text-primary mb-0">
                                            R$ {{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Delivery Address --}}
            @if($order->address)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h3 class="h5 mb-0 fw-semibold">
                            <i class="bi bi-geo-alt me-2"></i>
                            Endereço de Entrega
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="fw-medium mb-2">{{ $order->address->recipient_name }}</p>
                        <p class="mb-1">{{ $order->address->street }}, {{ $order->address->number }}</p>
                        @if($order->address->complement)
                            <p class="mb-1">{{ $order->address->complement }}</p>
                        @endif
                        <p class="mb-1">{{ $order->address->neighborhood }}</p>
                        <p class="mb-1">{{ $order->address->city }}/{{ $order->address->state }}</p>
                        <p class="mb-1">CEP: {{ $order->address->postal_code }}</p>
                        @if($order->address->recipient_phone)
                            <p class="mb-0 mt-2">
                                <i class="bi bi-telephone me-1"></i>
                                {{ $order->address->recipient_phone }}
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção:</strong> Endereço de entrega não disponível para este pedido.
                </div>
            @endif

            {{-- Customer Notes --}}
            @if($order->notes)
                <div class="alert alert-info mb-4">
                    <h4 class="alert-heading h6 fw-semibold mb-2">
                        <i class="bi bi-chat-left-text me-2"></i>
                        Observações do Pedido
                    </h4>
                    <p class="mb-0">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Right column: Order summary --}}
        <div class="col-lg-4">
            {{-- Order Summary --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="h5 mb-0 fw-semibold">
                        <i class="bi bi-receipt me-2"></i>
                        Resumo do Pedido
                    </h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-medium">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                        </div>
                        @if($order->shipping_fee > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Frete:</span>
                                <span class="fw-medium">R$ {{ number_format($order->shipping_fee, 2, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->discount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Desconto:</span>
                                <span class="text-success fw-medium">-R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <span class="h5 fw-bold mb-0">Total:</span>
                            <span class="h5 fw-bold text-primary mb-0">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top">
                        <p class="small text-muted mb-1">Status do Pagamento:</p>
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'primary') }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Seller Info --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h3 class="h5 mb-0 fw-semibold">
                        <i class="bi bi-shop me-2"></i>
                        Vendedor
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if($order->seller->hasMedia('seller_logo'))
                            <img src="{{ $order->seller->getFirstMedia('seller_logo')->getUrl('thumb') }}"
                                 alt="{{ $order->seller->store_name }}"
                                 loading="lazy"
                                 class="rounded-circle border"
                                 style="width: 48px; height: 48px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle border d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-shop text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <p class="fw-semibold mb-0">{{ $order->seller->store_name }}</p>
                            <p class="small text-muted mb-0">Vendedor</p>
                        </div>
                    </div>
                    <a href="{{ route('seller.show', $order->seller->slug) }}"
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-shop me-2"></i>
                        Ver Loja
                    </a>
                </div>
            </div>

            {{-- Tracking Code (if available) --}}
            @if($order->tracking_code)
                <div class="card shadow-sm border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0 fw-semibold">
                            <i class="bi bi-truck me-2"></i>
                            Rastreamento
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="font-monospace mb-3">{{ $order->tracking_code }}</p>
                        <a href="https://rastreamento.correios.com.br/app/index.php"
                           target="_blank"
                           class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-up-right me-2"></i>
                            Rastrear nos Correios
                        </a>
                    </div>
                </div>
            @endif

            {{-- Cancel Order Button (if applicable) --}}
            @can('cancel', $order)
                <div class="card shadow-sm border-danger">
                    <div class="card-header bg-danger text-white">
                        <h3 class="h5 mb-0 fw-semibold">
                            <i class="bi bi-x-circle me-2"></i>
                            Cancelar Pedido
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="small mb-3">
                            Você pode cancelar este pedido. O estoque dos produtos será restaurado automaticamente.
                        </p>
                        <form method="POST" action="{{ route('customer.orders.cancel', $order) }}"
                              onsubmit="return confirm('Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.')">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar Pedido
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
