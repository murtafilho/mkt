@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('header', 'Dashboard Administrativo')

@section('page-content')

    <div class="vstack gap-4">
        <!-- Sales Stats Grid -->
        <div class="row g-3 g-lg-4">
            <!-- Today Sales -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <svg class="text-secondary" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="small text-muted text-truncate">Vendas Hoje</div>
                                <div class="h4 fw-semibold mb-0">R$ {{ number_format($todaySales, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Week Sales -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <svg class="text-secondary" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="small text-muted text-truncate">Vendas Semana</div>
                                <div class="h4 fw-semibold mb-0">R$ {{ number_format($weekSales, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Month Sales -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <svg class="text-secondary" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="small text-muted text-truncate">Vendas Mês</div>
                                <div class="h4 fw-semibold mb-0">R$ {{ number_format($monthSales, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <svg class="text-secondary" width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <div class="small text-muted text-truncate">Receita Total</div>
                                <div class="h4 fw-semibold mb-0">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats -->
        <div class="row g-3 g-lg-4">
            <!-- Orders -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="h5 fw-medium mb-3">Pedidos</h3>
                        <dl class="row g-2 small mb-0">
                            <div class="col-6"><dt>Total:</dt></div>
                            <div class="col-6 text-end"><dd class="fw-semibold mb-0">{{ $ordersByStatus['total'] }}</dd></div>
                            
                            <div class="col-6"><dt>Aguardando:</dt></div>
                            <div class="col-6 text-end"><dd class="text-warning fw-medium mb-0">{{ $ordersByStatus['awaiting_payment'] }}</dd></div>
                            
                            <div class="col-6"><dt>Pagos:</dt></div>
                            <div class="col-6 text-end"><dd class="text-primary fw-medium mb-0">{{ $ordersByStatus['paid'] }}</dd></div>
                            
                            <div class="col-6"><dt>Preparando:</dt></div>
                            <div class="col-6 text-end"><dd class="text-info fw-medium mb-0">{{ $ordersByStatus['preparing'] }}</dd></div>
                            
                            <div class="col-6"><dt>Enviados:</dt></div>
                            <div class="col-6 text-end"><dd class="text-primary fw-medium mb-0">{{ $ordersByStatus['shipped'] }}</dd></div>
                            
                            <div class="col-6"><dt>Entregues:</dt></div>
                            <div class="col-6 text-end"><dd class="text-success fw-medium mb-0">{{ $ordersByStatus['delivered'] }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Sellers -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="h5 fw-medium mb-3">Vendedores</h3>
                        <dl class="row g-2 small mb-0">
                            <div class="col-6"><dt>Total:</dt></div>
                            <div class="col-6 text-end"><dd class="fw-semibold mb-0">{{ $sellersByStatus['total'] }}</dd></div>
                            
                            <div class="col-6"><dt>Pendentes:</dt></div>
                            <div class="col-6 text-end"><dd class="text-warning fw-medium mb-0">{{ $sellersByStatus['pending'] }}</dd></div>
                            
                            <div class="col-6"><dt>Ativos:</dt></div>
                            <div class="col-6 text-end"><dd class="text-success fw-medium mb-0">{{ $sellersByStatus['active'] }}</dd></div>
                            
                            <div class="col-6"><dt>Suspensos:</dt></div>
                            <div class="col-6 text-end"><dd class="text-danger fw-medium mb-0">{{ $sellersByStatus['suspended'] }}</dd></div>
                        </dl>
                        @if($sellersByStatus['pending'] > 0)
                            <a href="{{ route('admin.sellers.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary mt-3">
                                Ver pendentes →
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="h5 fw-medium mb-3">Produtos & Clientes</h3>
                        <dl class="row g-2 small mb-0">
                            <div class="col-8"><dt>Total Produtos:</dt></div>
                            <div class="col-4 text-end"><dd class="fw-semibold mb-0">{{ $productsByStatus['total'] }}</dd></div>
                            
                            <div class="col-8"><dt>Publicados:</dt></div>
                            <div class="col-4 text-end"><dd class="text-success fw-medium mb-0">{{ $productsByStatus['published'] }}</dd></div>
                            
                            <div class="col-8"><dt>Rascunhos:</dt></div>
                            <div class="col-4 text-end"><dd class="text-muted fw-medium mb-0">{{ $productsByStatus['draft'] }}</dd></div>
                            
                            <div class="col-12"><hr class="my-2"></div>
                            
                            <div class="col-8"><dt>Total Clientes:</dt></div>
                            <div class="col-4 text-end"><dd class="fw-semibold mb-0">{{ $customersCount }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Sales Chart -->
        <div class="card">
            <div class="card-body">
                <h3 class="h5 fw-medium mb-4">Vendas Mensais (Últimos 6 Meses)</h3>
                <canvas id="monthlySalesChart" height="80"></canvas>
            </div>
        </div>

        <!-- Lists -->
        <div class="row g-3 g-lg-4">
            <!-- Recent Orders -->
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="h6 fw-medium mb-0">Pedidos Recentes</h3>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-link text-primary">Ver todos →</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($recentOrders as $order)
                            <a href="{{ route('admin.orders.show', $order) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 me-3">
                                        <div class="small fw-medium text-dark">{{ $order->order_number }}</div>
                                        <div class="small text-muted">{{ $order->user->name }} • {{ $order->seller->store_name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $order->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="small fw-semibold text-dark">R$ {{ number_format($order->total, 2, ',', '.') }}</div>
                                        <span class="badge badge-{{ $order->status }} mt-1">{{ $order->status_label }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center text-muted py-5">
                                Nenhum pedido encontrado
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pending Sellers -->
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="h6 fw-medium mb-0">Vendedores Pendentes</h3>
                        <a href="{{ route('admin.sellers.index', ['status' => 'pending']) }}" class="btn btn-sm btn-link text-primary">Ver todos →</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse($pendingSellers as $seller)
                            <a href="{{ route('admin.sellers.show', $seller) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    @if($seller->getFirstMediaUrl('logo'))
                                        <img src="{{ $seller->getFirstMediaUrl('logo', 'thumb') }}" 
                                             alt="{{ $seller->store_name }}" 
                                             class="rounded-circle" 
                                             width="40" height="40" style="object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <span class="small fw-medium">{{ substr($seller->store_name, 0, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="ms-3 flex-grow-1">
                                        <div class="small fw-medium text-dark">{{ $seller->store_name }}</div>
                                        <div class="small text-muted">{{ $seller->user->name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $seller->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="list-group-item text-center text-muted py-5">
                                Nenhum vendedor pendente
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Chart.js is now imported via app.js (NPM) - no CDN needed --}}
    <script>
    const monthlySalesData = @json($monthlySales);

    const labels = monthlySalesData.map(item => {
        const [year, month] = item.month.split('-');
        const date = new Date(year, month - 1);
        return date.toLocaleDateString('pt-BR', { month: 'short', year: '2-digit' });
    });

    const revenueData = monthlySalesData.map(item => parseFloat(item.revenue));
    const orderData = monthlySalesData.map(item => parseInt(item.order_count));

    const ctx = document.getElementById('monthlySalesChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Receita (R$)',
                    data: revenueData,
                    backgroundColor: 'rgba(88, 140, 76, 0.5)',
                    borderColor: 'rgb(88, 140, 76)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: 'Pedidos',
                    data: orderData,
                    type: 'line',
                    borderColor: 'rgb(230, 126, 34)',
                    backgroundColor: 'rgba(230, 126, 34, 0.1)',
                    borderWidth: 2,
                    yAxisID: 'y1',
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
