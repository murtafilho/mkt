@extends('layouts.admin')

@section('title', 'Pedidos - Admin')

@section('header', 'Gerenciar Pedidos')

@section('page-content')
    <div class="vstack gap-4">
        {{-- Filters --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.orders.index') }}">
                    <div class="row g-3">
                        {{-- Search --}}
                        <div class="col-12 col-lg-6">
                            <label for="search" class="form-label">Buscar</label>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nº pedido, cliente, email..."
                                   class="form-control">
                        </div>

                        {{-- Status Filter --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Todos ({{ $statusCounts['all'] }})</option>
                                <option value="awaiting_payment" {{ request('status') === 'awaiting_payment' ? 'selected' : '' }}>Aguardando Pagamento ({{ $statusCounts['awaiting_payment'] }})</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago ({{ $statusCounts['paid'] }})</option>
                                <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparando ({{ $statusCounts['preparing'] }})</option>
                                <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Enviado ({{ $statusCounts['shipped'] }})</option>
                                <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregue ({{ $statusCounts['delivered'] }})</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado ({{ $statusCounts['cancelled'] }})</option>
                            </select>
                        </div>

                        {{-- Seller Filter --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="seller_id" class="form-label">Vendedor</label>
                            <select name="seller_id" id="seller_id" class="form-select">
                                <option value="">Todos os vendedores</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->store_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date From --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="date_from" class="form-label">Data Início</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
                        </div>

                        {{-- Date To --}}
                        <div class="col-12 col-md-6 col-lg-3">
                            <label for="date_to" class="form-label">Data Fim</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-4 mt-3 border-top">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Limpar Filtros
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Filter Chips --}}
        @php
            $filterChips = [];

            if (request('status') && request('status') !== 'all') {
                $statusLabels = [
                    'awaiting_payment' => 'Aguardando Pagamento',
                    'paid' => 'Pago',
                    'preparing' => 'Preparando',
                    'shipped' => 'Enviado',
                    'delivered' => 'Entregue',
                    'cancelled' => 'Cancelado',
                ];
                $filterChips[] = [
                    'label' => 'Status',
                    'display' => $statusLabels[request('status')] ?? request('status'),
                    'removeUrl' => request()->fullUrlWithQuery(['status' => null]),
                ];
            }

            if (request('seller_id')) {
                $seller = $sellers->firstWhere('id', request('seller_id'));
                $filterChips[] = [
                    'label' => 'Vendedor',
                    'display' => $seller->store_name ?? 'N/A',
                    'removeUrl' => request()->fullUrlWithQuery(['seller_id' => null]),
                ];
            }

            if (request('search')) {
                $filterChips[] = [
                    'label' => 'Busca',
                    'display' => '"' . request('search') . '"',
                    'removeUrl' => request()->fullUrlWithQuery(['search' => null]),
                ];
            }

            if (request('date_from') || request('date_to')) {
                $filterChips[] = [
                    'label' => 'Período',
                    'display' => (request('date_from') ?: '...') . ' até ' . (request('date_to') ?: '...'),
                    'removeUrl' => request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]),
                ];
            }
        @endphp

        @if(count($filterChips) > 0)
            <div class="d-flex gap-2 flex-wrap">
                @foreach($filterChips as $chip)
                    <div class="badge bg-primary-subtle text-primary d-flex align-items-center gap-2 py-2 px-3">
                        <span>{{ $chip['label'] }}: {{ $chip['display'] }}</span>
                        <a href="{{ $chip['removeUrl'] }}" class="text-primary text-decoration-none">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Orders List --}}
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="h5 fw-medium mb-0">
                    Pedidos
                    <span class="text-muted fw-normal small">
                        ({{ $orders->total() }} {{ $orders->total() === 1 ? 'pedido' : 'pedidos' }})
                    </span>
                </h3>
            </div>

            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'order_number', 'direction' => $sortField === 'order_number' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Pedido
                                        @if($sortField === 'order_number')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'customer_name', 'direction' => $sortField === 'customer_name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Cliente
                                        @if($sortField === 'customer_name')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'seller_name', 'direction' => $sortField === 'seller_name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Vendedor
                                        @if($sortField === 'seller_name')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Produtos</th>
                                <th class="text-end">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total', 'direction' => $sortField === 'total' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center justify-content-end">
                                        Valor
                                        @if($sortField === 'total')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => $sortField === 'status' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Status
                                        @if($sortField === 'status')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => $sortField === 'created_at' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Data
                                        @if($sortField === 'created_at')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $order->order_number }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $order->user->name }}</div>
                                        <div class="small text-muted">{{ $order->user->email }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $order->seller->store_name }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            @foreach($order->items->take(3) as $item)
                                                @if($item->product && $item->product->hasMedia('product_images'))
                                                    <img src="{{ $item->product->getFirstMediaUrl('product_images', 'thumb') }}"
                                                         alt="{{ $item->product->name }}"
                                                         loading="lazy"
                                                         class="rounded-circle border border-2 border-white"
                                                         style="width: 32px; height: 32px; object-fit: cover; margin-left: -8px;"
                                                         title="{{ $item->product->name }}">
                                                @endif
                                            @endforeach
                                            @if($order->items->count() > 3)
                                                <div class="rounded-circle border border-2 border-white bg-light d-flex align-items-center justify-content-center"
                                                     style="width: 32px; height: 32px; margin-left: -8px; font-size: 0.7rem;">
                                                    +{{ $order->items->count() - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-semibold">R$ {{ number_format($order->total, 2, ',', '.') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'primary') }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                            Ver Detalhes
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-white">
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <label for="per_page" class="small text-muted mb-0">Itens por página:</label>
                            <select name="per_page" id="per_page"
                                    onchange="window.location='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value"
                                    class="form-select form-select-sm" style="width: auto;">
                                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span class="small text-muted">
                                Mostrando {{ $orders->firstItem() }} a {{ $orders->lastItem() }} de {{ $orders->total() }} resultados
                            </span>
                        </div>

                        <div>
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="card-body text-center py-5">
                    <svg class="text-muted mb-3" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-muted mb-0">
                        @if(request()->hasAny(['status', 'seller_id', 'search', 'date_from', 'date_to']))
                            Nenhum pedido encontrado com os filtros aplicados.
                        @else
                            Nenhum pedido cadastrado ainda.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
