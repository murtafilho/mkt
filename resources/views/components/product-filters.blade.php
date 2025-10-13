@props(['categories'])

<form method="GET" action="{{ route('products.index') }}" class="vstack gap-3">
    {{-- Preserve search query --}}
    @if(request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif

    {{-- Categories Filter --}}
    <div class="card">
        <div class="card-body">
            <h3 class="h6 fw-semibold mb-3">
                <i class="ph ph-folders me-2"></i>
                Categorias
            </h3>
            <div class="vstack gap-2" style="max-height: 16rem; overflow-y: auto;">
                <div class="form-check">
                    <input type="radio" name="category" value="" id="cat-all"
                           {{ !request('category') ? 'checked' : '' }}
                           onchange="this.form.submit()"
                           class="form-check-input">
                    <label class="form-check-label w-100 d-flex justify-content-between" for="cat-all">
                        <span>Todas</span>
                    </label>
                </div>
                @foreach($categories as $category)
                    <div class="form-check">
                        <input type="radio" name="category" value="{{ $category->id }}" id="cat-{{ $category->id }}"
                               {{ request('category') == $category->id ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               class="form-check-input">
                        <label class="form-check-label w-100 d-flex justify-content-between" for="cat-{{ $category->id }}">
                            <span>{{ $category->name }}</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $category->products_count }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Price Range Filter --}}
    <div class="card">
        <div class="card-body">
            <h3 class="h6 fw-semibold mb-3">
                <i class="ph ph-currency-dollar me-2"></i>
                Faixa de Preço
            </h3>
            <div class="vstack gap-3">
                <div>
                    <label class="form-label small text-muted mb-1">Mínimo (R$)</label>
                    <input type="number" name="min_price" value="{{ request('min_price') }}"
                           placeholder="0,00" step="0.01" min="0"
                           class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label small text-muted mb-1">Máximo (R$)</label>
                    <input type="number" name="max_price" value="{{ request('max_price') }}"
                           placeholder="9999,99" step="0.01" min="0"
                           class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="ph ph-check me-2"></i>
                    Aplicar Filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Active Filters --}}
    @if(request()->hasAny(['q', 'category', 'min_price', 'max_price']))
        <div class="card bg-light">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h3 class="h6 fw-semibold mb-0">
                        <i class="ph ph-funnel-simple me-2"></i>
                        Filtros Ativos
                    </h3>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-danger">
                        <i class="ph ph-x me-1"></i>
                        Limpar
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if(request('q'))
                        <span class="badge bg-white text-dark border">
                            Busca: "{{ request('q') }}"
                        </span>
                    @endif
                    @if(request('category'))
                        @php
                            $selectedCategory = $categories->firstWhere('id', request('category'));
                        @endphp
                        @if($selectedCategory)
                            <span class="badge bg-white text-dark border">
                                {{ $selectedCategory->name }}
                            </span>
                        @endif
                    @endif
                    @if(request('min_price') || request('max_price'))
                        <span class="badge bg-white text-dark border">
                            Preço: R$ {{ request('min_price', 0) }} - {{ request('max_price', '∞') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</form>
