@extends('layouts.seller')

@section('title', 'Dashboard - Vendedor')

@section('header', 'Dashboard do Vendedor')

@section('page-content')
<div class="container-fluid">
    {{-- Welcome Message --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-secondary-rgb), 0.1) 100%);">
                <div class="card-body p-4">
                    <h3 class="card-title mb-2 h4 fw-bold">
                        <i class="bi bi-sun me-2 text-warning"></i>
                        Bem-vindo, {{ auth()->user()->seller->store_name }}!
                    </h3>
                    <p class="card-text text-muted mb-0">
                        Gerencie seus produtos, pedidos e vendas através deste painel.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="row g-4 mb-4">
        {{-- Total Products --}}
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-box text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted small mb-1">Total de Produtos</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['total_products'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Orders --}}
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-secondary bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-bag text-secondary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted small mb-1">Pedidos Totais</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['total_orders'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Orders --}}
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-clock text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted small mb-1">Pedidos Pendentes</h6>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['pending_orders'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Sales --}}
        <div class="col-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-currency-dollar text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted small mb-1">Vendas Totais</h6>
                            <h4 class="mb-0 fw-bold text-dark">
                                R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0 fw-semibold">
                        <i class="bi bi-lightning me-2 text-warning"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Add Product --}}
                        <div class="col-md-4">
                            <a href="{{ route('seller.products.create') }}" 
                               class="card h-100 text-decoration-none border-0 shadow-sm hover-lift">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary rounded-3 p-3">
                                                <i class="bi bi-plus-lg text-white fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="card-title fw-semibold mb-1">Adicionar Produto</h6>
                                            <p class="card-text small text-muted mb-0">Cadastre um novo produto</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- View Orders --}}
                        <div class="col-md-4">
                            <a href="{{ route('seller.orders.index') }}" 
                               class="card h-100 text-decoration-none border-0 shadow-sm hover-lift">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-secondary rounded-3 p-3">
                                                <i class="bi bi-clipboard text-white fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="card-title fw-semibold mb-1">Ver Pedidos</h6>
                                            <p class="card-text small text-muted mb-0">Gerencie seus pedidos</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- Edit Profile --}}
                        <div class="col-md-4">
                            <a href="{{ route('seller.profile.edit') }}" 
                               class="card h-100 text-decoration-none border-0 shadow-sm hover-lift">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-info rounded-3 p-3">
                                                <i class="bi bi-person-gear text-white fs-5"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="card-title fw-semibold mb-1">Editar Perfil</h6>
                                            <p class="card-text small text-muted mb-0">Atualize suas informações</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Seller Status Alert --}}
    @if(auth()->user()->seller->status !== 'active')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center" role="alert">
                <div class="flex-shrink-0 me-3">
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading fw-semibold mb-2">
                        Seu cadastro está em análise
                    </h6>
                    <p class="mb-0 small">
                        Sua conta de vendedor está aguardando aprovação do administrador.
                        Assim que for aprovada, você poderá começar a cadastrar produtos e vender no marketplace.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient {
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-secondary-rgb), 0.1) 100%);
}
</style>
@endsection