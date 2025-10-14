@extends('layouts.seller')

@section('page-content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="{{ route('seller.orders.index') }}" class="text-decoration-none text-muted mb-2 d-d-inline-d-block">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar para pedidos
            </a>
            <h1 class="h2 fw-bold mb-1">Pedido #{{ $order->order_number }}</h1>
            <p class="text-muted mb-0">
                Realizado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
            </p>
        </div>
        <div>
            @switch($order->status)
                @case('awaiting_payment')
                    <span class="badge bg-warning fs-6">Aguardando Pagamento</span>
                    @break
                @case('paid')
                    <span class="badge bg-info fs-6">Pago</span>
                    @break
                @case('preparing')
                    <span class="badge bg-purple text-white fs-6">Em Preparação</span>
                    @break
                @case('shipped')
                    <span class="badge bg-primary fs-6">Enviado</span>
                    @break
                @case('delivered')
                    <span class="badge bg-success fs-6">Entregue</span>
                    @break
                @case('cancelled')
                    <span class="badge bg-danger fs-6">Cancelado</span>
                    @break
                @default
                    <span class="badge bg-secondary fs-6">{{ $order->status_label }}</span>
            @endswitch
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-x-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Left Column: Order Details --}}
        <div class="col-lg-8">
            {{-- Customer Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-person me-2"></i>
                        Informações do Cliente
                    </h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small text-muted">Nome</label>
                            <p class="mb-0 fw-medium">{{ $order->user->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small text-muted">E-mail</label>
                            <p class="mb-0">{{ $order->user->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delivery Address --}}
            @if($order->address)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-geo-alt me-2"></i>
                            Endereço de Entrega
                        </h5>
                        <address class="mb-0">
                            {{ $order->address->street }}, {{ $order->address->number }}
                            @if($order->address->complement)
                                <br>{{ $order->address->complement }}
                            @endif
                            <br>{{ $order->address->neighborhood }} - {{ $order->address->city }}/{{ $order->address->state }}
                            <br>CEP: {{ $order->address->postal_code }}
                        </address>
                    </div>
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Atenção:</strong> Endereço de entrega não disponível para este pedido (pedido criado antes da implementação do sistema de endereços).
                </div>
            @endif

            {{-- Order Items --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-box me-2"></i>
                        Itens do Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($order->items as $item)
                            <div class="col-12">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="flex-shrink-0 me-3">
                                        @if($item->product->hasMedia('product_images'))
                                            <img src="{{ $item->product->getFirstMediaUrl('product_images', 'thumb') }}"
                                                 alt="{{ $item->product->name }}"
                                                 class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="width: 60px; height: 60px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $item->product->name }}</h6>
                                        <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-medium">R$ {{ number_format($item->price, 2, ',', '.') }}</div>
                                        <small class="text-muted">Qtd: {{ $item->quantity }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Order Summary & Actions --}}
        <div class="col-lg-4">
            {{-- Order Summary --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        Resumo do Pedido
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frete:</span>
                        <span>R$ {{ number_format($order->shipping_cost, 2, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total:</span>
                        <span>R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Order Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Ações
                    </h5>
                </div>
                <div class="card-body">
                    @if($order->status === 'paid')
                        <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="preparing">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-clock me-2"></i>
                                Marcar como "Em Preparação"
                            </button>
                        </form>
                    @endif

                    @if($order->status === 'preparing')
                        <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="shipped">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-truck me-2"></i>
                                Marcar como "Enviado"
                            </button>
                        </form>
                    @endif

                    @if($order->status === 'shipped')
                        <form action="{{ route('seller.orders.update-status', $order) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-2"></i>
                                Marcar como "Entregue"
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar para Lista
                    </a>
                </div>
            </div>

            {{-- Order Timeline --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Histórico
                    </h5>
                </div>
                <div class="card-body">
                    @if($order->history->count() > 0)
                        <div class="timeline">
                            @foreach($order->history->sortByDesc('created_at') as $history)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-circle-fill text-white" style="font-size: 8px;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium">{{ $history->status_label }}</div>
                                            <small class="text-muted">{{ $history->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Nenhum histórico disponível.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection