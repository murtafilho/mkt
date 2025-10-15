@extends('layouts.public')

@section('page-content')
<div class="container py-5">
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Início</a></li>
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categorias</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    {{-- Category Header --}}
    <div class="row mb-5">
        <div class="col-12 text-center">
            <div class="category-header-icon mb-3">
                <i class="bi bi-{{ $category->icon ?? 'tag' }} display-1 text-primary"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3">{{ $category->name }}</h1>
            @if($category->description)
            <p class="lead text-muted">{{ $category->description }}</p>
            @endif
            <div class="badge bg-primary fs-6 px-3 py-2">
                {{ $category->products_count }} {{ Str::plural('produto', $category->products_count) }}
            </div>
        </div>
    </div>

    {{-- Products Grid --}}
    @if($products->count() > 0)
    <div class="row g-4">
        @foreach($products as $product)
        <div class="col-6 col-md-4 col-lg-3">
            <x-product-card :product="$product" />
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="row mt-5">
        <div class="col-12">
            {{ $products->links() }}
        </div>
    </div>
    @endif
    @else
    {{-- Empty State --}}
    <div class="row">
        <div class="col-12 text-center py-5">
            <i class="bi bi-box display-1 text-muted mb-3"></i>
            <h3 class="text-muted">Nenhum produto nesta categoria</h3>
            <p class="text-muted">Os vendedores ainda não adicionaram produtos aqui.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                Ver Todos os Produtos
            </a>
        </div>
    </div>
    @endif

    {{-- Related Categories --}}
    @if($relatedCategories->count() > 0)
    <div class="row mt-5 pt-5 border-top">
        <div class="col-12 mb-4">
            <h2 class="h3 fw-bold mb-0">Categorias Relacionadas</h2>
            <p class="text-muted">Explore outras categorias similares</p>
        </div>
        
        <div class="col-12">
            <div class="row g-3">
                @foreach($relatedCategories as $relatedCategory)
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="{{ route('categories.show', $relatedCategory->slug) }}" class="text-decoration-none">
                        <div class="card h-100 category-card border-0 shadow-sm">
                            <div class="card-body text-center p-3">
                                <div class="category-icon mb-2">
                                    <i class="bi bi-{{ $relatedCategory->icon ?? 'tag' }} fs-4 text-primary"></i>
                                </div>
                                <h6 class="card-title mb-1 small">{{ $relatedCategory->name }}</h6>
                                <small class="text-muted">
                                    {{ $relatedCategory->products_count }} itens
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@once
<style>
.category-header-icon {
    transition: all 0.3s ease;
}

.category-header-icon:hover {
    transform: scale(1.1);
}

.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
}

.category-icon {
    transition: all 0.3s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
}
</style>
@endonce
@endsection
