@extends('layouts.public')

@section('page-content')
<div class="container py-5">
    {{-- Header --}}
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold mb-3">Todas as Categorias</h1>
            <p class="lead text-muted">Explore nossa comunidade organizada por categorias</p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="row mb-5">
        <div class="col-lg-6 mx-auto">
            <form action="{{ route('categories.index') }}" method="GET" class="search-form">
                <div class="input-group rounded-pill overflow-hidden" style="border: 1px solid #dee2e6;">
                    <input type="search" 
                           class="form-control border-0" 
                           name="q"
                           placeholder="Buscar categoria..." 
                           value="{{ request('q') }}"
                           aria-label="Buscar categoria"
                           style="border-right: none !important;">

                    <button class="btn btn-primary border-0" type="submit" style="border-left: 1px solid rgba(255,255,255,0.2) !important;">
                        <i class="bi bi-search"></i>
                        <span class="d-none d-md-inline ms-2">Buscar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Categories Grid --}}
    <div class="row g-4">
        @forelse($categories as $category)
        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none">
                <div class="card h-100 category-card border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="category-icon mb-3">
                            <i class="bi bi-{{ $category->icon ?? 'tag' }} fs-1 text-primary"></i>
                        </div>
                        <h5 class="card-title mb-2">{{ $category->name }}</h5>
                        <small class="text-muted">
                            {{ $category->products_count }} {{ Str::plural('item', $category->products_count) }}
                        </small>
                        
                        {{-- Subcategorias se existirem --}}
                        @if($category->children->count() > 0)
                        <div class="mt-3">
                            <small class="text-primary">
                                {{ $category->children->count() }} subcategorias
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox display-1 text-muted mb-3"></i>
            <h3 class="text-muted">Nenhuma categoria encontrada</h3>
            <p class="text-muted">Tente ajustar sua busca ou volte mais tarde.</p>
            <a href="{{ route('categories.index') }}" class="btn btn-primary">
                Ver Todas as Categorias
            </a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($categories->hasPages())
    <div class="row mt-5">
        <div class="col-12">
            {{ $categories->links() }}
        </div>
    </div>
    @endif
</div>

@once
<style>
.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
}

.category-icon {
    transition: all 0.3s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
}

.category-card:hover .category-icon i {
    color: var(--bs-primary) !important;
}
</style>
@endonce
@endsection
