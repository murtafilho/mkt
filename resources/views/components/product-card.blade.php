@props(['product', 'searchTerm' => null, 'size' => 'default'])

@php
$cardClasses = $size === 'large' ? 'h-100' : '';
$imageClasses = $size === 'large' ? 'ratio ratio-4x3' : 'ratio ratio-1x1';
$titleClasses = $size === 'large' ? 'fs-6 fw-semibold' : 'small fw-semibold';
@endphp

<div class="card border position-relative overflow-hidden {{ $cardClasses }}" style="transition: all 0.3s ease;">
    {{-- Product Image --}}
    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none {{ $imageClasses }} overflow-hidden" style="background: linear-gradient(135deg, #f5f5f5, #e5e5e5);">
        @if($product->hasImages())
            <img src="{{ $product->getMainImage()->getUrl('medium') }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 decoding="async"
                 class="object-fit-cover w-100 h-100"
                 style="transition: transform 0.3s ease;"
                 onmouseover="this.style.transform='scale(1.1)'"
                 onmouseout="this.style.transform='scale(1)'">
        @else
            <div class="d-flex align-items-center justify-content-center w-100 h-100">
                <svg class="text-muted" width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        <div class="position-absolute top-0 end-0 m-2">
            <span class="badge bg-secondary text-dark fw-semibold" style="font-size: 0.7rem;">
                🏘️ Local
            </span>
        </div>

        @if($product->original_price > $product->sale_price)
            <div class="position-absolute top-0 start-0 m-2">
                <span class="badge bg-danger fw-semibold" style="font-size: 0.7rem;">
                    -{{ number_format((($product->original_price - $product->sale_price) / $product->original_price) * 100, 0) }}%
                </span>
            </div>
        @endif
    </a>

    {{-- Product Info --}}
    <div class="card-body p-3">
        {{-- Seller Badge --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            @if($product->seller->hasMedia('seller_logo'))
                <img src="{{ $product->seller->getFirstMediaUrl('seller_logo', 'logo_thumb') }}"
                     alt="{{ $product->seller->store_name }}"
                     class="rounded-circle"
                     width="24" height="24"
                     style="object-fit: cover;">
            @else
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                     style="width: 24px; height: 24px;">
                    <span style="font-size: 0.7rem;">👤</span>
                </div>
            @endif
            <a href="{{ route('seller.show', $product->seller->slug) }}"
               class="text-muted text-decoration-none text-truncate"
               style="font-size: 0.75rem;">
                {{ $product->seller->store_name }}
            </a>
        </div>

        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none">
            <h3 class="text-dark {{ $titleClasses }} mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ $product->name }}
            </h3>
        </a>

        @if($searchTerm && stripos($product->name, $searchTerm) === false && stripos($product->description, $searchTerm) !== false)
            {{-- Show description snippet when search term is found in description but not in name --}}
            @php
                $pos = stripos($product->description, $searchTerm);
                $start = max(0, $pos - 20);
                $snippet = Str::limit(substr($product->description, $start), 80);
                $highlighted = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark class="bg-warning px-1">$1</mark>', $snippet);
            @endphp
            <p class="text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {!! $highlighted !!}
            </p>
        @endif

        <div class="d-flex align-items-center justify-content-between mt-3">
            <div>
                @if($product->original_price > $product->sale_price)
                    <p class="text-muted text-decoration-line-through mb-0" style="font-size: 0.75rem;">
                        R$ {{ number_format($product->original_price, 2, ',', '.') }}
                    </p>
                @endif
                <p class="fs-5 fw-bold text-primary mb-0">
                    R$ {{ number_format($product->sale_price, 2, ',', '.') }}
                </p>
            </div>

            {{-- Add to Cart Button with Alpine.js state --}}
            <div x-data="{
                adding: false,
                justAdded: false,
                async addToCart() {
                    if (this.adding || this.justAdded) return;

                    this.adding = true;
                    const result = await $store.cart.addItem({{ $product->id }}, 1);
                    this.adding = false;

                    if (result.success) {
                        this.justAdded = true;
                        setTimeout(() => { this.justAdded = false; }, 2000);
                    }
                }
            }">
                <button
                    type="button"
                    @click="addToCart()"
                    :disabled="adding || justAdded"
                    :class="{
                        'btn-success': justAdded,
                        'btn-primary': !justAdded && !adding,
                        'btn-secondary': adding
                    }"
                    class="btn btn-sm p-2 d-flex align-items-center justify-content-center"
                    :title="justAdded ? 'Adicionado!' : 'Adicionar ao carrinho'"
                    style="width: 40px; height: 40px; transition: all 0.3s ease;">

                    {{-- Loading State --}}
                    <span x-show="adding" x-cloak>
                        <span class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </span>
                    </span>

                    {{-- Success State --}}
                    <svg x-show="justAdded" x-cloak width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>

                    {{-- Default State (Add Icon) --}}
                    <svg x-show="!adding && !justAdded" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-4px);
    border-color: var(--bs-primary) !important;
}
</style>
@endpush
