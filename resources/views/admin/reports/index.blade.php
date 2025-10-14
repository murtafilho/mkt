@extends('layouts.admin')

@section('title', 'Relatórios - Admin')

@section('header', 'Relatórios')

@section('page-content')
    <div class="vstack gap-4">
        {{-- Page Header --}}
        <div class="card">
            <div class="card-body">
                <h2 class="h3 fw-bold mb-2">Central de Relatórios</h2>
                <p class="text-muted mb-0">Acesse relatórios detalhados e exporte dados do marketplace.</p>
            </div>
        </div>

        {{-- Report Cards Grid --}}
        <div class="row g-4">
            {{-- Sales Report --}}
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('admin.reports.sales') }}" class="card h-100 text-decoration-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded d-flex align-items-center justify-content-center bg-primary-subtle"
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
                            </div>
                            <h3 class="h5 mb-0 ms-3 fw-medium">Relatório de Vendas</h3>
                        </div>
                        <p class="small text-muted mb-3">
                            Análise completa de vendas, faturamento e desempenho por período.
                        </p>
                        <ul class="small text-muted mb-3 ps-3">
                            <li>Receita total e por vendedor</li>
                            <li>Pedidos por status</li>
                            <li>Gráficos de vendas diárias</li>
                            <li>Exportação em CSV</li>
                        </ul>
                        <span class="text-primary small fw-medium">
                            Ver relatório <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </div>
                </a>
            </div>

            {{-- Products Report --}}
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('admin.reports.products') }}" class="card h-100 text-decoration-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded d-flex align-items-center justify-content-center"
                                 style="width: 48px; height: 48px; background-color: rgba(111, 66, 193, 0.1);">
                                <i class="bi bi-box-seam fs-4" style="color: #6f42c1;"></i>
                            </div>
                            <h3 class="h5 mb-0 ms-3 fw-medium">Relatório de Produtos</h3>
                        </div>
                        <p class="small text-muted mb-3">
                            Visão geral do catálogo, estoque e produtos por vendedor.
                        </p>
                        <ul class="small text-muted mb-3 ps-3">
                            <li>Total de produtos (publicados e rascunhos)</li>
                            <li>Alertas de estoque baixo</li>
                            <li>Produtos por vendedor e categoria</li>
                            <li>Filtros avançados</li>
                        </ul>
                        <span class="text-primary small fw-medium">
                            Ver relatório <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </div>
                </a>
            </div>

            {{-- Sellers Report --}}
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('admin.reports.sellers') }}" class="card h-100 text-decoration-none hover-shadow">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded d-flex align-items-center justify-content-center bg-success-subtle"
                                 style="width: 48px; height: 48px;">
                                <i class="bi bi-people text-success fs-4"></i>
                            </div>
                            <h3 class="h5 mb-0 ms-3 fw-medium">Relatório de Vendedores</h3>
                        </div>
                        <p class="small text-muted mb-3">
                            Performance dos vendedores, produtos cadastrados e receita gerada.
                        </p>
                        <ul class="small text-muted mb-3 ps-3">
                            <li>Status dos vendedores (ativos, pendentes)</li>
                            <li>Total de produtos e estoque</li>
                            <li>Pedidos e receita por vendedor</li>
                            <li>Ranking de performance</li>
                        </ul>
                        <span class="text-primary small fw-medium">
                            Ver relatório <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </div>
                </a>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="card">
            <div class="card-body">
                <h3 class="h5 fw-medium mb-3">Estatísticas Rápidas</h3>
                <p class="small text-muted mb-4">Para análises detalhadas, acesse os relatórios específicos acima.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Ver Dashboard Principal
                    </a>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary">
                        <i class="bi bi-graph-up me-2"></i>
                        Acessar Relatórios de Vendas
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
