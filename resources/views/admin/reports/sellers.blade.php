@extends('layouts.admin')

@section('header', 'Relatório de Vendedores')
@section('title', 'Relatório de Vendedores - Admin')

@section('page-content')
    <div class="mb-4">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>
            Voltar para Relatórios
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspenso</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.reports.sellers') }}" class="btn btn-outline-secondary">Limpar</a>
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
            'pending' => 'Pendente',
            'active' => 'Ativo',
            'suspended' => 'Suspenso',
        ];
        $filterChips[] = [
            'label' => 'Status',
            'value' => request('status'),
            'display' => $statusLabels[request('status')] ?? request('status'),
            'removeUrl' => request()->fullUrlWithQuery(['status' => null]),
        ];
    }
    @endphp

    <x-filter-chips :filters="$filterChips" />

    {{-- Metrics --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="small text-muted mb-2">Total de Vendedores</h3>
                    <p class="display-6 fw-bold mb-0">{{ $totalSellers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="small text-muted mb-2">Ativos</h3>
                    <p class="display-6 fw-bold mb-0 text-success">{{ $activeSellers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="small text-muted mb-2">Pendentes</h3>
                    <p class="display-6 fw-bold mb-0 text-warning">{{ $pendingSellers }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <h3 class="small text-muted mb-2">Suspensos</h3>
                    <p class="display-6 fw-bold mb-0 text-danger">{{ $suspendedSellers }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sellers Table --}}
    <div class="card">
        <div class="card-header bg-white py-3">
            <h3 class="h5 mb-0">
                Performance dos Vendedores
                <span class="small fw-normal text-muted">
                    ({{ $sellers->total() }} {{ $sellers->total() === 1 ? 'vendedor' : 'vendedores' }})
                </span>
            </h3>
        </div>

        @if($sellers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <x-sortable-th column="store_name" label="Vendedor" :current-sort="$sortField" :current-direction="$sortDirection" />
                            <th scope="col" class="text-uppercase small fw-semibold">Status</th>
                            <x-sortable-th column="products_count" label="Produtos" :current-sort="$sortField" :current-direction="$sortDirection" />
                            <th scope="col" class="text-uppercase small fw-semibold">Estoque</th>
                            <th scope="col" class="text-uppercase small fw-semibold">Pedidos</th>
                            <x-sortable-th column="revenue" label="Receita" :current-sort="$sortField" :current-direction="$sortDirection" />
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sellers as $seller)
                            <tr>
                                <td>
                                    <div class="small fw-medium">
                                        <a href="{{ route('admin.sellers.show', $seller) }}" class="text-decoration-none">
                                            {{ $seller->store_name }}
                                        </a>
                                    </div>
                                    <div class="small text-muted">{{ $seller->user->name }}</div>
                                </td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'bg-success' => $seller->status === 'active',
                                        'bg-warning text-dark' => $seller->status === 'pending',
                                        'bg-danger' => $seller->status === 'suspended',
                                    ])>
                                        @if($seller->status === 'active')
                                            Ativo
                                        @elseif($seller->status === 'pending')
                                            Pendente
                                        @else
                                            Suspenso
                                        @endif
                                    </span>
                                </td>
                                <td class="small">{{ $seller->products_count ?? 0 }}</td>
                                <td class="small">{{ $seller->products_sum_stock ?? 0 }}</td>
                                <td class="small fw-semibold">{{ $seller->total_orders ?? 0 }}</td>
                                <td class="small fw-semibold">R$ {{ number_format($seller->total_revenue ?? 0, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="per_page" class="small text-muted mb-0">Items por página:</label>
                        <select name="per_page" id="per_page"
                            onchange="window.location='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value"
                            class="form-select form-select-sm" style="width: auto;">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="small text-muted">
                            Mostrando {{ $sellers->firstItem() }} a {{ $sellers->lastItem() }} de {{ $sellers->total() }} resultados
                        </span>
                    </div>

                    <div>
                        {{ $sellers->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-body text-center py-5">
                <svg class="mx-auto mb-3" width="48" height="48" fill="currentColor" style="color: #6c757d;" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="small text-muted mb-0">
                    @if(request()->hasAny(['status']))
                        Nenhum vendedor encontrado com os filtros aplicados.
                    @else
                        Nenhum vendedor cadastrado.
                    @endif
                </p>
            </div>
        @endif
    </div>
@endsection
