@extends('layouts.admin')

@section('title', 'Detalhes do Vendedor - Admin')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <span>Detalhes do Vendedor</span>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-link text-decoration-none">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
@endsection

@section('page-content')
<div class="container-fluid">
    {{-- Seller Info --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 class="fw-bold mb-2">
                        <i class="bi bi-shop me-2 text-primary"></i>
                        {{ $seller->store_name }}
                    </h3>
                    <p class="text-muted mb-0">
                        <i class="bi bi-link-45deg me-1"></i>
                        {{ $seller->slug }}
                    </p>
                </div>
                <div>
                    @if($seller->status === 'active')
                        <span class="badge bg-success fs-6">
                            <i class="bi bi-check-circle me-1"></i>Ativo
                        </span>
                    @elseif($seller->status === 'pending')
                        <span class="badge bg-warning fs-6">
                            <i class="bi bi-clock me-1"></i>Pendente
                        </span>
                    @else
                        <span class="badge bg-danger fs-6">
                            <i class="bi bi-x-circle me-1"></i>Suspenso
                        </span>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                {{-- Owner Info --}}
                <div class="col-md-6">
                    <h5 class="fw-semibold mb-3">
                        <i class="bi bi-person me-2"></i>
                        Proprietário
                    </h5>
                    <dl class="row g-2">
                        <dt class="col-sm-4 text-muted">Nome:</dt>
                        <dd class="col-sm-8 fw-medium">{{ $seller->user->name }}</dd>
                        
                        <dt class="col-sm-4 text-muted">E-mail:</dt>
                        <dd class="col-sm-8">{{ $seller->user->email }}</dd>
                    </dl>
                </div>

                {{-- Seller Details --}}
                <div class="col-md-6">
                    <h5 class="fw-semibold mb-3">
                        <i class="bi bi-building me-2"></i>
                        Informações da Loja
                    </h5>
                    <dl class="row g-2">
                        <dt class="col-sm-5 text-muted">Tipo de Pessoa:</dt>
                        <dd class="col-sm-7 fw-medium">{{ $seller->person_type === 'individual' ? 'Pessoa Física' : 'Pessoa Jurídica' }}</dd>
                        
                        <dt class="col-sm-5 text-muted">Documento:</dt>
                        <dd class="col-sm-7">{{ $seller->document_number }}</dd>
                        
                        @if($seller->company_name)
                            <dt class="col-sm-5 text-muted">Razão Social:</dt>
                            <dd class="col-sm-7">{{ $seller->company_name }}</dd>
                        @endif
                        
                        @if($seller->trade_name)
                            <dt class="col-sm-5 text-muted">Nome Fantasia:</dt>
                            <dd class="col-sm-7">{{ $seller->trade_name }}</dd>
                        @endif
                    </dl>
                </div>

                {{-- Contact Info --}}
                <div class="col-md-6">
                    <h5 class="fw-semibold mb-3">
                        <i class="bi bi-telephone me-2"></i>
                        Contato
                    </h5>
                    <dl class="row g-2">
                        <dt class="col-sm-4 text-muted">E-mail:</dt>
                        <dd class="col-sm-8">{{ $seller->business_email }}</dd>
                        
                        <dt class="col-sm-4 text-muted">Telefone:</dt>
                        <dd class="col-sm-8">{{ $seller->business_phone }}</dd>
                    </dl>
                </div>

                {{-- Status Info --}}
                <div class="col-md-6">
                    <h5 class="fw-semibold mb-3">
                        <i class="bi bi-calendar-check me-2"></i>
                        Status
                    </h5>
                    <dl class="row g-2">
                        <dt class="col-sm-5 text-muted">Cadastrado em:</dt>
                        <dd class="col-sm-7">{{ $seller->created_at->format('d/m/Y H:i') }}</dd>
                        
                        @if($seller->approved_at)
                            <dt class="col-sm-5 text-muted">Aprovado em:</dt>
                            <dd class="col-sm-7">{{ $seller->approved_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($seller->description)
                <div class="mt-4">
                    <h5 class="fw-semibold mb-3">
                        <i class="bi bi-text-paragraph me-2"></i>
                        Descrição
                    </h5>
                    <p class="text-muted">{{ $seller->description }}</p>
                </div>
            @endif

            {{-- Actions --}}
            <div class="mt-4 pt-4 border-top">
                <div class="d-flex gap-2 flex-wrap">
                    @if($seller->status === 'pending')
                        <form method="POST" action="{{ route('admin.sellers.approve', $seller) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Aprovar este vendedor?')">
                                <i class="bi bi-check-lg me-2"></i>
                                Aprovar Vendedor
                            </button>
                        </form>
                    @elseif($seller->status === 'active')
                        <form method="POST" action="{{ route('admin.sellers.suspend', $seller) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('Suspender este vendedor? Todos os produtos serão despublicados.')">
                                <i class="bi bi-pause-circle me-2"></i>
                                Suspender Vendedor
                            </button>
                        </form>
                    @elseif($seller->status === 'suspended')
                        <form method="POST" action="{{ route('admin.sellers.reactivate', $seller) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" 
                                    onclick="return confirm('Reativar este vendedor?')">
                                <i class="bi bi-play-circle me-2"></i>
                                Reativar Vendedor
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('seller.show', $seller->slug) }}" target="_blank" 
                       class="btn btn-primary">
                        <i class="bi bi-box-arrow-up-right me-2"></i>
                        Ver Página Pública
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-box text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted small mb-1">Total de Produtos</h6>
                            <h3 class="fw-bold mb-0">{{ $stats['total_products'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-cart-check text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted small mb-1">Total de Vendas</h6>
                            <h3 class="fw-bold mb-0">{{ $stats['total_sales'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-currency-dollar text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted small mb-1">Receita Total</h6>
                            <h3 class="fw-bold mb-0 small">
                                R$ {{ number_format($stats['total_revenue'] ?? 0, 2, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="bi bi-clock-history text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted small mb-1">Comissões Pendentes</h6>
                            <h3 class="fw-bold mb-0 small">
                                R$ {{ number_format($stats['pending_earnings'] ?? 0, 2, ',', '.') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Products --}}
    @if($seller->products->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-box-seam me-2"></i>
                    Produtos
                    <span class="badge bg-secondary ms-2">{{ $seller->products->count() }}</span>
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Produto</th>
                            <th scope="col">Categoria</th>
                            <th scope="col">Preço</th>
                            <th scope="col">Estoque</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($seller->products as $product)
                            <tr>
                                <td class="fw-medium">{{ $product->name }}</td>
                                <td class="text-muted">{{ $product->category->name ?? '-' }}</td>
                                <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->stock }}</span>
                                </td>
                                <td>
                                    @if($product->status === 'published')
                                        <span class="badge bg-success">Publicado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($product->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection