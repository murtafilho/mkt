@extends('layouts.public')

@section('page-content')
    <div class="bg-light py-4">
        <div class="container">
            {{-- Breadcrumbs --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Produtos</li>
                </ol>
            </nav>

            {{-- Page Header --}}
            <div class="mb-4">
                <h1 class="h2 fw-bold mb-2">
                    @if(request('q'))
                        Resultados para "{{ request('q') }}"
                    @else
                        Todos os Produtos
                    @endif
                </h1>
                <p class="text-muted mb-0">
                    {{ $products->total() }} {{ Str::plural('produto', $products->total()) }} {{ Str::plural('encontrado', $products->total()) }}
                </p>
            </div>

            {{-- Layout: Sidebar + Grid --}}
            <div class="row g-4">
                {{-- Sidebar Filters (Desktop) --}}
                <aside class="col-lg-3 d-none d-lg-block">
                    <x-product-filters :categories="$categories" />
                </aside>

                {{-- Products Grid --}}
                <div class="col-12 col-lg-9">
                    {{-- Mobile Filters Button --}}
                    <div class="mb-3 d-lg-none">
                        <button type="button"
                                class="btn btn-outline-secondary w-100"
                                data-bs-toggle="offcanvas"
                                data-bs-target="#mobileFiltersOffcanvas"
                                aria-controls="mobileFiltersOffcanvas">
                            <i class="bi bi-funnel me-2"></i>
                            Filtros e Ordenação
                        </button>
                    </div>

                    {{-- Sort Bar --}}
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <p class="small text-muted mb-0">
                            Mostrando {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} de {{ $products->total() }}
                        </p>
                        <form method="GET" action="{{ route('products.index') }}" class="d-inline-block">
                            {{-- Preserve filters --}}
                            @if(request('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            @if(request('min_price'))
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            @endif
                            @if(request('max_price'))
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                            @endif

                            <select name="sort" onchange="this.form.submit()" class="form-select form-select-sm">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mais recentes</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Mais populares</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Menor preço</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Maior preço</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nome (A-Z)</option>
                            </select>
                        </form>
                    </div>

                    {{-- Products Grid --}}
                    @if($products->count() > 0)
                        <div x-data class="row g-3 g-md-4">
                            @foreach($products as $product)
                                <div class="col-6 col-md-4">
                                    <x-product-card :product="$product" :searchTerm="request('q')" />
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4">
                            {{ $products->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted mb-3" style="font-size: 4rem;"></i>
                            <h3 class="h5 fw-semibold mb-2">
                                Nenhum produto encontrado
                            </h3>
                            <p class="text-muted mb-3">
                                Tente ajustar seus filtros ou buscar por outro termo.
                            </p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Ver todos os produtos
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Filters Offcanvas --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileFiltersOffcanvas" aria-labelledby="mobileFiltersLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="mobileFiltersLabel">
                <i class="bi bi-funnel me-2"></i>
                Filtros e Ordenação
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
            <x-product-filters :categories="$categories" />
        </div>
    </div>
@endsection
