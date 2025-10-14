@extends('layouts.seller')

@section('title', 'Pedidos - Vendedor')

@section('header', 'Gerenciar Pedidos')

@section('page-content')
<div class="container-fluid">
    {{-- Status Tabs --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill border-0" id="orderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('seller.orders.index') }}"
                       class="nav-link {{ !request('status') ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2">
                        Todos
                        <span class="badge {{ !request('status') ? 'bg-primary' : 'bg-secondary' }} rounded-pill">
                            {{ $statusCounts['all'] }}
                        </span>
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a href="{{ route('seller.orders.index', ['status' => 'awaiting_payment']) }}"
                       class="nav-link {{ request('status') === 'awaiting_payment' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2">
                        Aguardando Pagamento
                        <span class="badge {{ request('status') === 'awaiting_payment' ? 'bg-warning' : 'bg-secondary' }} rounded-pill">
                            {{ $statusCounts['awaiting_payment'] }}
                        </span>
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a href="{{ route('seller.orders.index', ['status' => 'paid']) }}"
                       class="nav-link {{ request('status') === 'paid' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2">
                        Pago
                        <span class="badge {{ request('status') === 'paid' ? 'bg-info' : 'bg-secondary' }} rounded-pill">
                            {{ $statusCounts['paid'] }}
                        </span>
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a href="{{ route('seller.orders.index', ['status' => 'preparing']) }}"
                       class="nav-link {{ request('status') === 'preparing' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2">
                        Em Preparação
                        <span class="badge {{ request('status') === 'preparing' ? 'bg-purple' : 'bg-secondary' }} rounded-pill">
                            {{ $statusCounts['preparing'] }}
                        </span>
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a href="{{ route('seller.orders.index', ['status' => 'shipped']) }}"
                       class="nav-link {{ request('status') === 'shipped' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2">
                        Enviado
                        <span class="badge {{ request('status') === 'shipped' ? 'bg-primary' : 'bg-secondary' }} rounded-pill">
                            {{ $statusCounts['shipped'] }}
                        </span>
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a href="{{ route('seller.orders.index', ['status' => 'delivered']) }}"
                       class="nav-link {{ request('status') === 'delivered' ? 'active' : '' }} d-flex align-items-center justify-content-center gap-2">
                        Entregue
                        <span class="badge {{ request('status') === 'delivered' ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                            {{ $statusCounts['delivered'] }}
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('seller.orders.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="search" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Buscar por número do pedido ou nome do cliente..." class="form-control">
                    </div>
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary d-flex-fill">Buscar</button>
                            <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Filter Chips --}}
    @php
    $filterChips = [];

    if (request('status')) {
        $statusLabels = [
            'awaiting_payment' => 'Aguardando Pagamento',
            'paid' => 'Pago',
            'preparing' => 'Em Preparação',
            'shipped' => 'Enviado',
            'delivered' => 'Entregue',
        ];
        $filterChips[] = [
            'label' => 'Status',
            'value' => request('status'),
            'display' => $statusLabels[request('status')] ?? request('status'),
            'removeUrl' => request()->fullUrlWithQuery(['status' => null]),
        ];
    }

    if (request('search')) {
        $filterChips[] = [
            'label' => 'Busca',
            'value' => request('search'),
            'display' => '"' . request('search') . '"',
            'removeUrl' => request()->fullUrlWithQuery(['search' => null]),
        ];
    }
    @endphp

    <x-filter-chips :filters="$filterChips" />

    {{-- Orders List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="card-title mb-0">
                Pedidos
                <span class="badge bg-secondary ms-2">{{ $orders->total() }} {{ $orders->total() === 1 ? 'pedido' : 'pedidos' }}</span>
            </h5>
        </div>

        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Pedido</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Status</th>
                            <th scope="col">Data</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $order->order_number }}</div>
                                        <small class="text-muted">{{ $order->items->count() }} item(s)</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $order->user->name }}</div>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                                </td>
                                <td>
                                    @switch($order->status)
                                        @case('awaiting_payment')
                                            <span class="badge bg-warning">Aguardando Pagamento</span>
                                            @break
                                        @case('paid')
                                            <span class="badge bg-info">Pago</span>
                                            @break
                                        @case('preparing')
                                            <span class="badge bg-purple text-white">Em Preparação</span>
                                            @break
                                        @case('shipped')
                                            <span class="badge bg-primary">Enviado</span>
                                            @break
                                        @case('delivered')
                                            <span class="badge bg-success">Entregue</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Cancelado</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $order->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $order->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $order->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('seller.orders.show', $order) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer bg-transparent border-0">
                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="bi bi-clipboard display-1 text-muted mb-3"></i>
                <h5 class="card-title">
                    @if(request()->hasAny(['status', 'search']))
                        Nenhum pedido encontrado com os filtros aplicados.
                    @else
                        Nenhum pedido encontrado
                    @endif
                </h5>
                <p class="card-text text-muted">
                    @if(!request()->hasAny(['status', 'search']))
                        Seus pedidos aparecerão aqui quando os clientes fizerem compras.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection