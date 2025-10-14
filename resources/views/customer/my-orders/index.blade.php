@extends('layouts.public')

@section('page-content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 fw-bold mb-3">
                <i class="bi bi-bag me-2 text-primary"></i>
                Meus Pedidos
            </h1>
            <p class="text-muted">Acompanhe seus pedidos e histórico de compras</p>
        </div>
    </div>

    {{-- Filtros e Busca --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.orders.index') }}">
                <div class="row g-3">
                    {{-- Status filter --}}
                    <div class="col-12 col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="awaiting_payment" {{ request('status') === 'awaiting_payment' ? 'selected' : '' }}>Aguardando Pagamento</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago</option>
                            <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Em Preparação</option>
                            <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Enviado</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregue</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    {{-- Search input --}}
                    <div class="col-12 col-md-4">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="search"
                               name="search"
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Número do pedido"
                               class="form-control">
                    </div>

                    {{-- Sort select --}}
                    <div class="col-12 col-md-3">
                        <label for="sort" class="form-label">Ordenar</label>
                        <select name="sort" id="sort" class="form-select">
                            <option value="desc" {{ request('sort', 'desc') === 'desc' ? 'selected' : '' }}>Mais Recentes</option>
                            <option value="asc" {{ request('sort') === 'asc' ? 'selected' : '' }}>Mais Antigos</option>
                        </select>
                    </div>

                    {{-- Apply button --}}
                    <div class="col-12 col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($orders->isEmpty())
        {{-- Empty state --}}
        <div class="card shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
                <h3 class="h4 fw-bold mb-3">Nenhum pedido encontrado</h3>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['status', 'search']))
                        Nenhum pedido corresponde aos filtros selecionados.
                    @else
                        Você ainda não realizou nenhum pedido.
                    @endif
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    @if(request()->hasAny(['status', 'search']))
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Limpar Filtros
                        </a>
                    @else
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="bi bi-cart me-2"></i>
                            Começar a Comprar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @else
        {{-- Orders list --}}
        @foreach($orders as $order)
            <div class="card shadow-sm mb-3 hover-shadow-md">
                {{-- Order header --}}
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h5 mb-1 fw-semibold">Pedido #{{ $order->order_number }}</h3>
                            <p class="small text-muted mb-0">
                                <i class="bi bi-calendar3 me-1"></i>
                                {{ $order->created_at->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>
                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'primary') }} fs-6">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>

                {{-- Order body --}}
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="bi bi-shop me-1"></i>
                        Vendedor: <span class="fw-medium text-dark">{{ $order->seller->store_name }}</span>
                    </p>

                    {{-- Items preview --}}
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        @foreach($order->items->take(4) as $item)
                            @if($item->product && $item->product->hasMedia('product_images'))
                                <img src="{{ $item->product->getFirstMedia('product_images')->getUrl('thumb') }}"
                                     alt="{{ $item->product->name }}"
                                     loading="lazy"
                                     class="rounded border"
                                     style="width: 64px; height: 64px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded border d-flex align-items-center justify-content-center" 
                                     style="width: 64px; height: 64px;">
                                    <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                </div>
                            @endif
                        @endforeach
                        @if($order->items->count() > 4)
                            <span class="badge bg-secondary">
                                +{{ $order->items->count() - 4 }} {{ $order->items->count() - 4 === 1 ? 'item' : 'itens' }}
                            </span>
                        @endif
                    </div>

                    {{-- Total and action --}}
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div>
                            <p class="small text-muted mb-1">Total do Pedido</p>
                            <p class="h4 fw-bold text-primary mb-0">
                                R$ {{ number_format($order->total, 2, ',', '.') }}
                            </p>
                        </div>
                        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline-primary">
                            Ver Detalhes
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
