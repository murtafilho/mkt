@extends('layouts.admin')

@section('title', 'Relatório de Vendas - Admin')

@section('header', 'Relatório de Vendas')

@section('page-content')
    <div class="vstack gap-4">
        {{-- Back Button --}}
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-link text-primary p-0">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar para Relatórios
            </a>
        </div>

        {{-- Filters --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.sales') }}">
                    <div class="row g-3">
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

                        {{-- Seller Filter --}}
                        <div class="col-12 col-md-6 col-lg-4">
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

                        {{-- Filter Button --}}
                        <div class="col-12 col-md-6 col-lg-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-2"></i>
                                Filtrar
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-4 mt-3 border-top">
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            Limpar Filtros
                        </a>
                        <a href="{{ route('admin.reports.sales.export', request()->query()) }}" class="btn btn-success">
                            <i class="bi bi-download me-2"></i>
                            Exportar CSV
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Metrics Cards --}}
        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">
                            <i class="bi bi-bag-check me-1"></i>
                            Total de Pedidos
                        </h3>
                        <p class="h2 fw-bold text-primary mb-0">{{ number_format($totalOrders, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">
                            <i class="bi bi-currency-dollar me-1"></i>
                            Receita Total
                        </h3>
                        <p class="h2 fw-bold text-success mb-0">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-info">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">
                            <i class="bi bi-calculator me-1"></i>
                            Ticket Médio
                        </h3>
                        <p class="h2 fw-bold text-info mb-0">R$ {{ number_format($averageOrderValue, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Orders by Status --}}
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="h5 fw-medium mb-0">
                    <i class="bi bi-pie-chart me-2"></i>
                    Pedidos por Status
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Quantidade</th>
                                <th class="text-end">Receita</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordersByStatus as $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'delivered' ? 'success' : ($item->status === 'cancelled' ? 'danger' : 'primary') }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($item->count, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">R$ {{ number_format($item->revenue, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Nenhum dado disponível para o período selecionado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Daily Sales Chart --}}
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="h5 fw-medium mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Vendas Diárias
                </h3>
            </div>
            <div class="card-body">
                <canvas id="dailySalesChart" height="80"></canvas>
            </div>
        </div>

        {{-- Top Sellers --}}
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="h5 fw-medium mb-0">
                    <i class="bi bi-trophy me-2"></i>
                    Top 10 Vendedores
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Vendedor</th>
                                <th class="text-end">Pedidos</th>
                                <th class="text-end">Receita</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellers as $index => $item)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                            <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'danger') }}">
                                                {{ $index + 1 }}º
                                            </span>
                                        @else
                                            <span class="text-muted">{{ $index + 1 }}º</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item->seller->hasMedia('seller_logo'))
                                                <img src="{{ $item->seller->getFirstMediaUrl('seller_logo', 'thumb') }}"
                                                     alt="{{ $item->seller->store_name }}"
                                                     class="rounded-circle"
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                     style="width: 32px; height: 32px;">
                                                    <i class="bi bi-shop text-muted"></i>
                                                </div>
                                            @endif
                                            <span class="fw-medium">{{ $item->seller->store_name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($item->order_count, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold text-success">R$ {{ number_format($item->revenue, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Nenhum dado disponível para o período selecionado
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Chart.js is now imported via app.js (NPM) - no CDN needed --}}
    <script>
    const dailySalesData = @json($dailySales);

    const labels = dailySalesData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });

    const revenueData = dailySalesData.map(item => parseFloat(item.revenue));
    const orderData = dailySalesData.map(item => parseInt(item.order_count));

    const ctx = document.getElementById('dailySalesChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Receita (R$)',
                    data: revenueData,
                    borderColor: 'rgb(88, 140, 76)', // Verde Mata
                    backgroundColor: 'rgba(88, 140, 76, 0.1)',
                    borderWidth: 2,
                    yAxisID: 'y',
                    tension: 0.3,
                },
                {
                    label: 'Pedidos',
                    data: orderData,
                    borderColor: 'rgb(184, 111, 80)', // Terracota
                    backgroundColor: 'rgba(184, 111, 80, 0.1)',
                    borderWidth: 2,
                    yAxisID: 'y1',
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 0) {
                                    label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                },
            }
        }
    });
    </script>
    @endpush
@endsection
