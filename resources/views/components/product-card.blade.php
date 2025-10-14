@props(['product', 'searchTerm' => null, 'size' => 'default'])

<div class="card product-card h-100">
    {{-- Product Image - Fixed Height Container --}}
    <div class="product-image-container">
        <a href="{{ route('products.show', $product->slug) }}" class="product-image-link">
            @if($product->hasImages())
                <img src="{{ $product->getMainImage()->getUrl('medium') }}"
                     alt="{{ $product->name }}"
                     loading="lazy"
                     decoding="async"
                     class="product-image">
            @else
                <div class="product-image-placeholder">
                    <i class="bi bi-image"></i>
                </div>
            @endif
        </a>

        {{-- Badges --}}
        @if($product->original_price > $product->sale_price)
            <div class="product-badge product-badge-discount">
                -{{ number_format((($product->original_price - $product->sale_price) / $product->original_price) * 100, 0) }}%
            </div>
        @endif

        <div class="product-badge product-badge-local">
            <i class="bi bi-geo-alt-fill"></i>Local
        </div>
    </div>

    {{-- Product Info --}}
    <div class="card-body d-flex flex-column">
        {{-- Product Title --}}
        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none">
            <h3 class="product-title">
                {{ $product->name }}
            </h3>
        </a>

        {{-- Seller Info --}}
        <div class="product-seller">
            <a href="{{ route('seller.show', $product->seller->slug) }}" class="text-decoration-none">
                @if($product->seller->hasMedia('seller_logo'))
                    <img src="{{ $product->seller->getFirstMediaUrl('seller_logo', 'logo_thumb') }}"
                         alt="{{ $product->seller->store_name }}"
                         class="seller-logo">
                @else
                    <i class="bi bi-shop"></i>
                @endif
                <span>{{ $product->seller->store_name }}</span>
            </a>
        </div>

        @if($searchTerm && stripos($product->name, $searchTerm) === false && stripos($product->description, $searchTerm) !== false)
            @php
                $pos = stripos($product->description, $searchTerm);
                $start = max(0, $pos - 20);
                $snippet = Str::limit(substr($product->description, $start), 80);
                $highlighted = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark class="bg-warning px-1">$1</mark>', $snippet);
            @endphp
            <p class="product-description">
                {!! $highlighted !!}
            </p>
        @endif

        {{-- Price and Button Section --}}
        <div class="product-footer">
            <div class="product-price">
                @if($product->original_price > $product->sale_price)
                    <span class="price-original">R$ {{ number_format($product->original_price, 2, ',', '.') }}</span>
                @endif
                <span class="price-current">R$ {{ number_format($product->sale_price, 2, ',', '.') }}</span>
            </div>

            <button
                onclick="addToCartBtn(this, {{ $product->id }})"
                class="btn btn-primary btn-add-cart"
                title="Adicionar ao carrinho"
                aria-label="Adicionar {{ $product->name }} ao carrinho">
                <i class="bi bi-cart-plus"></i>
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Product Card - Consistent Design */
.product-card {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: var(--bs-primary);
}

/* Image Container - Fixed Height */
.product-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
}

.product-image-link {
    display: block;
    width: 100%;
    height: 100%;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.product-image-placeholder i {
    font-size: 3rem;
    color: #adb5bd;
}

/* Badges */
.product-badge {
    position: absolute;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}

.product-badge-discount {
    top: 8px;
    left: 8px;
    background: #dc3545;
    color: white;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.product-badge-local {
    top: 8px;
    right: 8px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(4px);
    color: #495057;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

/* Card Body */
.product-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 16px;
}

/* Product Title */
.product-title {
    font-size: 1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 8px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s ease;
}

.product-title:hover {
    color: var(--bs-primary);
}

/* Seller Info */
.product-seller {
    margin-bottom: 12px;
}

.product-seller a {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6c757d;
    font-size: 0.8rem;
    transition: color 0.2s ease;
}

.product-seller a:hover {
    color: var(--bs-primary);
}

.seller-logo {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    object-fit: cover;
}

/* Product Description */
.product-description {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 12px;
    line-height: 1.4;
}

/* Product Footer */
.product-footer {
    margin-top: auto;
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 12px;
}

/* Product Price */
.product-price {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.price-original {
    font-size: 0.8rem;
    color: #6c757d;
    text-decoration: line-through;
}

.price-current {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--bs-primary);
    line-height: 1;
}

/* Add to Cart Button */
.btn-add-cart {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: none;
    background: var(--bs-primary);
    color: white;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.btn-add-cart:hover {
    background: var(--bs-primary);
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-add-cart:active {
    transform: scale(0.95);
}

.btn-add-cart i {
    font-size: 1.1rem;
}

/* Success state */
.btn-add-cart.success {
    background: #28a745 !important;
}

.btn-add-cart.success i::before {
    content: "\f26b"; /* bi-check2 */
}

/* Responsive */
@media (max-width: 767.98px) {
    .product-image-container {
        height: 180px;
    }
    
    .product-card .card-body {
        padding: 12px;
    }
    
    .product-title {
        font-size: 0.9rem;
    }
    
    .price-current {
        font-size: 1.1rem;
    }
    
    .btn-add-cart {
        width: 40px;
        height: 40px;
    }
}
</style>
@endpush
