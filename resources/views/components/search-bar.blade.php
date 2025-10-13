{{-- Search Bar Component with Autocomplete --}}
<form action="{{ route('products.index') }}" method="GET" class="search-form" x-data="searchAutocomplete()">
    <div class="input-group input-group-lg">
        {{-- Search Input --}}
        <input type="search" 
               class="form-control" 
               name="q"
               placeholder="O que você procura? Ex: jardineiro, marmita caseira..." 
               autocomplete="off"
               id="searchInput"
               x-model="query"
               @input.debounce.300ms="performSearch()"
               @focus="showSuggestions = true"
               aria-label="Campo de busca"
               value="{{ request('q') }}">

        {{-- Search Button --}}
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i>
            <span class="d-none d-md-inline ms-2">Buscar</span>
        </button>
    </div>

    {{-- Autocomplete Suggestions --}}
    <div x-show="showSuggestions && (products.length > 0 || sellers.length > 0)" 
         x-cloak
         @click.away="showSuggestions = false"
         class="search-suggestions">
        
        <div class="list-group">
            {{-- Products --}}
            <template x-if="products.length > 0">
                <div>
                    <div class="list-group-item bg-light border-0">
                        <small class="text-muted fw-bold">PRODUTOS</small>
                    </div>
                    <template x-for="product in products" :key="product.slug">
                        <a :href="`/products/${product.slug}`"
                           class="list-group-item list-group-item-action border-0"
                           @click="showSuggestions = false">
                            <div class="d-flex align-items-center">
                                <img :src="product.thumbnail || '/images/placeholder.jpg'" 
                                     :alt="product.name"
                                     class="me-3 rounded" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-medium" x-text="product.name"></div>
                                    <small class="text-muted" x-text="product.category"></small>
                                </div>
                                <div class="text-end">
                                    <small class="text-primary fw-bold" x-text="`R$ ${product.price.toFixed(2).replace('.', ',')}`"></small>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Sellers --}}
            <template x-if="sellers.length > 0">
                <div>
                    <div class="list-group-item bg-light border-0 mt-2">
                        <small class="text-muted fw-bold">VENDEDORES</small>
                    </div>
                    <template x-for="seller in sellers" :key="seller.slug">
                        <a :href="`/seller/${seller.slug}`"
                           class="list-group-item list-group-item-action border-0"
                           @click="showSuggestions = false">
                            <i class="bi bi-shop text-primary me-2"></i>
                            <span x-text="seller.name"></span>
                            <small class="text-muted ms-2">
                                <i class="bi bi-geo-alt"></i>
                                <span x-text="seller.location"></span>
                            </small>
                        </a>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loading" x-cloak class="search-loading position-absolute end-0 top-50 translate-middle-y me-5">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-d-none">Buscando...</span>
        </div>
    </div>
</form>

@once
<style>
[x-cloak] {
    display: none !important;
}
</style>
@endonce

