@extends('layouts.seller')

@section('title', 'Meus Produtos - Vendedor')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <span>Meus Produtos</span>
        <a href="{{ route('seller.products.create') }}" class="btn btn-primary d-flex align-items-center">
            <i class="bi bi-plus-lg me-2"></i>
            Adicionar Produto
        </a>
    </div>
@endsection

@section('page-content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('seller.products.index') }}">
                <div class="row g-3">
                    {{-- Search --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Nome ou SKU..." class="form-control">
                    </div>

                    {{-- Category Filter --}}
                    <div class="col-md-3">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Todas</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status Filter --}}
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Sem Estoque</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                            <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">Limpar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Filter Chips --}}
    @php
    $filterChips = [];

    if (request('category_id')) {
        $category = $categories->firstWhere('id', request('category_id'));
        $filterChips[] = [
            'label' => 'Categoria',
            'value' => request('category_id'),
            'display' => $category->name ?? 'N/A',
            'removeUrl' => request()->fullUrlWithQuery(['category_id' => null]),
        ];
    }

    if (request('status')) {
        $statusLabels = [
            'published' => 'Publicado',
            'draft' => 'Rascunho',
            'out_of_stock' => 'Sem Estoque',
            'inactive' => 'Inativo',
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

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="card-title mb-0">
                Produtos
                <span class="badge bg-secondary ms-2">{{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }}</span>
            </h5>
        </div>

        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <x-sortable-th column="name" label="Produto" :current-sort="$sortField" :current-direction="$sortDirection" />
                            <th scope="col">Categoria</th>
                            <x-sortable-th column="price" label="Preço" :current-sort="$sortField" :current-direction="$sortDirection" />
                            <x-sortable-th column="stock" label="Estoque" :current-sort="$sortField" :current-direction="$sortDirection" />
                            <th scope="col">Status</th>
                            <x-sortable-th column="created_at" label="Data" :current-sort="$sortField" :current-direction="$sortDirection" />
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            @if($product->hasMedia('product_images'))
                                                <img src="{{ $product->getFirstMediaUrl('product_images', 'thumb') }}"
                                                     alt="{{ $product->name }}"
                                                     class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-medium">{{ $product->name }}</div>
                                            <small class="text-muted">SKU: {{ $product->sku ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $product->category->name }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium">R$ {{ number_format($product->sale_price, 2, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->stock }}</span>
                                </td>
                                <td>
                                    @if($product->status === 'published')
                                        <span class="badge bg-success">Publicado</span>
                                    @elseif($product->status === 'draft')
                                        <span class="badge bg-warning">Rascunho</span>
                                    @elseif($product->status === 'out_of_stock')
                                        <span class="badge bg-danger">Sem Estoque</span>
                                    @else
                                        <span class="badge bg-secondary">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $product->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('seller.products.edit', $product) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                            Editar
                                        </a>
                                        <form action="{{ route('seller.products.destroy', $product) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                                                <i class="bi bi-trash"></i>
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="card-footer bg-transparent border-0">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <label for="per_page" class="form-label mb-0 small">Items por página:</label>
                        <select name="per_page" id="per_page"
                            onchange="window.location='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value"
                            class="form-select form-select-sm" style="width: auto;">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <small class="text-muted">
                            Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} de {{ $products->total() }} resultados
                        </small>
                    </div>
                    <div>
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="bi bi-box display-1 text-muted mb-3"></i>
                <h5 class="card-title">
                    @if(request()->hasAny(['category_id', 'status', 'search']))
                        Nenhum produto encontrado com os filtros aplicados.
                    @else
                        Nenhum produto cadastrado
                    @endif
                </h5>
                <p class="card-text text-muted">
                    @if(!request()->hasAny(['category_id', 'status', 'search']))
                        Comece adicionando seu primeiro produto.
                    @endif
                </p>
                @if(!request()->hasAny(['category_id', 'status', 'search']))
                    <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>
                        Adicionar Produto
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection