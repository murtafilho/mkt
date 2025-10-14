@extends('layouts.admin')

@section('title', 'Relatório de Produtos - Admin')

@section('header', 'Relatório de Produtos')

@section('page-content')
    <div class="vstack gap-4">
        {{-- Back link --}}
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-link text-primary p-0">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar para Relatórios
            </a>
        </div>

        {{-- Filters --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.products') }}">
                    <div class="row g-3">
                        {{-- Seller filter --}}
                        <div class="col-12 col-md-4">
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

                        {{-- Status filter --}}
                        <div class="col-12 col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicados</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunhos</option>
                            </select>
                        </div>

                        {{-- Actions --}}
                        <div class="col-12 col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('admin.reports.products') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Active filters --}}
        @if(request()->hasAny(['seller_id', 'status']))
            <div class="d-flex gap-2 flex-wrap">
                @if(request('seller_id'))
                    @php
                        $seller = $sellers->firstWhere('id', request('seller_id'));
                    @endphp
                    <div class="badge bg-primary-subtle text-primary d-flex align-items-center gap-2 py-2 px-3">
                        <span>Vendedor: {{ $seller->store_name ?? 'N/A' }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['seller_id' => null]) }}" class="text-primary text-decoration-none">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                @endif

                @if(request('status'))
                    @php
                        $statusLabels = [
                            'published' => 'Publicados',
                            'draft' => 'Rascunhos',
                        ];
                    @endphp
                    <div class="badge bg-primary-subtle text-primary d-flex align-items-center gap-2 py-2 px-3">
                        <span>Status: {{ $statusLabels[request('status')] ?? request('status') }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="text-primary text-decoration-none">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Metrics --}}
        <div class="row g-3 g-lg-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">Total de Produtos</h3>
                        <p class="h2 fw-bold mb-0">{{ $totalProducts }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">Publicados</h3>
                        <p class="h2 fw-bold text-success mb-0">{{ $publishedProducts }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">Rascunhos</h3>
                        <p class="h2 fw-bold text-muted mb-0">{{ $draftProducts }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <h3 class="small text-muted mb-2">Estoque Baixo</h3>
                        <p class="h2 fw-bold text-danger mb-0">{{ $lowStockProducts }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Table --}}
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="h5 fw-medium mb-0">
                    Produtos
                    <span class="text-muted fw-normal small">
                        ({{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }})
                    </span>
                </h3>
            </div>

            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $sortField === 'name' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Produto
                                        @if($sortField === 'name')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Vendedor</th>
                                <th>Categoria</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price', 'direction' => $sortField === 'price' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Preço
                                        @if($sortField === 'price')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'stock', 'direction' => $sortField === 'stock' && $sortDirection === 'asc' ? 'desc' : 'asc']) }}"
                                       class="text-decoration-none text-dark d-flex align-items-center">
                                        Estoque
                                        @if($sortField === 'stock')
                                            <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $product->name }}</div>
                                        <div class="small text-muted">SKU: {{ $product->sku }}</div>
                                    </td>
                                    <td>{{ $product->seller->store_name }}</td>
                                    <td>{{ $product->category->name ?? '-' }}</td>
                                    <td class="fw-semibold">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="{{ $product->stock <= 10 ? 'text-danger fw-bold' : '' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $product->status === 'published' ? 'success' : 'secondary' }}">
                                            {{ $product->status === 'published' ? 'Publicado' : 'Rascunho' }}
                                        </span>
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
                                Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} de {{ $products->total() }} resultados
                            </span>
                        </div>

                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="card-body text-center py-5">
                    <svg class="text-muted mb-3" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-muted mb-0">
                        @if(request()->hasAny(['seller_id', 'status']))
                            Nenhum produto encontrado com os filtros aplicados.
                        @else
                            Nenhum produto cadastrado.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
