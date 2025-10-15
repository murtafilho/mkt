@extends('layouts.public')

@section('title', config('app.name') . ' - Favorencendo a vocação e o comércio local')

@section('page-content')

{{-- Hero Section - Com Gradient Background --}}
<section class="hero-section" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.20) 0%, rgba(210, 105, 30, 0.20) 100%);">
    <div class="container">
        <div class="row align-items-center py-5">
            {{-- Conteúdo (50%) --}}
            <div class="col-md-6 order-2 order-md-1">
                <div class="hero-content">
                    <h1 class="display-4 fw-bold mb-3">
                        Favorencendo a vocação e o comércio local
                </h1>
                    <p class="lead mb-4">
                        Descubra produtos e serviços dos seus vizinhos —
                        do Jardim Canadá a Pasárgada, somos todos uma comunidade.
                    </p>
                    
                    {{-- CTAs --}}
                    <div class="d-flex gap-3 flex-wrap">
                        @auth
                            @php
                                $user = Auth::user();
                                $hasSeller = $user->seller()->exists() && $user->seller->status === 'active';
                                $isAdmin = $user->hasRole('admin');
                            @endphp
                            
                            {{-- Se o usuário é seller ativo --}}
                            @if($hasSeller)
                                <a href="{{ route('seller.dashboard') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-shop me-2"></i>
                                    {{ Str::limit($user->seller->store_name, 20) }}
                                </a>
                                @if($isAdmin)
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-lg">
                                        <i class="bi bi-gear-fill me-2"></i>
                                        Administrador
                                    </a>
                                @endif
                            {{-- Se o usuário é admin (mas não seller) --}}
                            @elseif($isAdmin)
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-lg">
                                    <i class="bi bi-gear-fill me-2"></i>
                                    Administrador
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-compass me-2"></i>
                                    Explorar
                                </a>
                            {{-- Usuário autenticado mas não é seller --}}
                            @else
                                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-compass me-2"></i>
                                    Explorar Ofertas
                                </a>
                                <a href="{{ route('seller.register') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-shop me-2"></i>
                                    Quero Vender
                                </a>
                            @endif
                        @else
                            {{-- Usuário não autenticado (guest) --}}
                            <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-compass me-2"></i>
                                Explorar Ofertas
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-shop me-2"></i>
                                Quero Vender
                        </a>
                    @endauth
                </div>

                {{-- Trust Badges --}}
                    <div class="trust-badges mt-4">
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="badge-item">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <small class="ms-2">Vendedores Verificados</small>
                            </div>
                            <div class="badge-item">
                                <i class="bi bi-geo-alt text-primary fs-5"></i>
                                <small class="ms-2">100% Local</small>
                            </div>
                            <div class="badge-item">
                                <i class="bi bi-people text-info fs-5"></i>
                                <small class="ms-2">Comunidade Ativa</small>
                    </div>
                    </div>
                    </div>
                </div>
            </div>

            {{-- Imagem (50%) --}}
            <div class="col-md-6 order-1 order-md-2 mb-4 mb-md-0">
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop" 
                         alt="Comunidade Vale do Sol" 
                         class="img-fluid rounded-3 shadow-lg"
                         loading="eager">
                                </div>
                            </div>
                    </div>
                                </div>
</section>

{{-- Stats Bar --}}
<section class="stats-bar bg-white border-top border-bottom py-4">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-6 mb-0">{{ $stats['sellers_count'] ?? '150+' }}</h3>
                    <small class="text-muted">Vendedores Ativos</small>
                            </div>
                    </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-item">
                    <h3 class="display-6 mb-0">{{ $stats['products_count'] ?? '500+' }}</h3>
                    <small class="text-muted">Produtos e Serviços</small>
                            </div>
                    </div>
                    <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h3 class="display-6 mb-0">4.8★</h3>
                    <small class="text-muted">Avaliação Média</small>
                                </div>
                            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h3 class="display-6 mb-0">5km</h3>
                    <small class="text-muted">Raio de Entrega</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{-- Categories Grid --}}
<section class="categories-section py-5 bg-light">
        <div class="container">
        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold">Explore por Categoria</h2>
                <p class="text-muted">Do premium ao popular, tudo em um lugar</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                    Ver Todas <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            </div>

        {{-- Grid de Categorias --}}
        <div class="row g-4">
            @forelse($mainCategories ?? [] as $category)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('products.index', ['category' => $category->id]) }}" 
                   class="text-decoration-none">
                    <div class="card h-100 category-card">
                        <div class="card-body text-center p-4">
                            <div class="category-icon mb-3">
                                <i class="bi bi-{{ $category->icon ?? 'tag' }}"></i>
                            </div>
                            <h5 class="card-title mb-2">{{ $category->name }}</h5>
                            <small class="text-muted">
                                {{ $category->products_count ?? 0 }} itens
                            </small>
                        </div>
                </div>
                </a>
            </div>
            @empty
            {{-- Categorias Placeholder --}}
            @php
                $placeholderCategories = [
                    ['name' => 'Meio Ambiente', 'icon' => 'tree', 'count' => 45],
                    ['name' => 'Casa e Construção', 'icon' => 'hammer', 'count' => 78],
                    ['name' => 'Gastronomia', 'icon' => 'cup-hot', 'count' => 92],
                    ['name' => 'Saúde e Bem-estar', 'icon' => 'heart-pulse', 'count' => 34],
                    ['name' => 'Serviços', 'icon' => 'tools', 'count' => 56],
                    ['name' => 'Educação', 'icon' => 'book', 'count' => 23],
                    ['name' => 'Tecnologia', 'icon' => 'laptop', 'count' => 41],
                    ['name' => 'Artesanato', 'icon' => 'palette', 'count' => 67],
                ];
            @endphp
            @foreach($placeholderCategories as $cat)
                    <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ route('products.index') }}" class="text-decoration-none">
                    <div class="card h-100 category-card">
                        <div class="card-body text-center p-4">
                            <div class="category-icon mb-3">
                                <i class="bi bi-{{ $cat['icon'] }}"></i>
                            </div>
                            <h5 class="card-title mb-2">{{ $cat['name'] }}</h5>
                            <small class="text-muted">{{ $cat['count'] }} itens</small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
            @endforelse
            </div>
        </div>
    </section>

{{-- Featured Products Section --}}
<section class="py-5 bg-white">
        <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Produtos em Destaque</h2>
            <p class="text-muted">Conheça as melhores ofertas da nossa comunidade</p>
        </div>

        <div class="row g-4">
            @forelse($featuredProducts ?? [] as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <x-product-card :product="$product" />
                </div>
            @empty
                {{-- Placeholder products --}}
                @for($i = 0; $i < 8; $i++)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card">
                        @if($i % 3 === 0)
                        <div class="product-badge badge-sale">
                            -30%
                        </div>
                        @elseif($i % 3 === 1)
                        <div class="product-badge badge-new">
                            Novo
                        </div>
                        @endif

                        <div class="product-image">
                            <img src="https://images.unsplash.com/photo-{{ 1542838132 + $i }}92c53300491e?w=400&h=300&fit=crop" 
                                 alt="Produto Local" 
                                 loading="lazy">
                        </div>

                        <div class="card-body">
                            <h5 class="product-title">Produto Local {{ $i + 1 }}</h5>
                            <div class="product-seller">
                                <i class="bi bi-shop"></i>
                                <span>Loja Comunitária</span>
                            </div>
                            <div class="product-rating">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                                <small>(24)</small>
                            </div>
                            <div class="product-price">
                                @if($i % 3 === 0)
                                <span class="price-original">R$ {{ 50 + ($i * 10) }},00</span>
                                <span class="price-current">R$ {{ 35 + ($i * 7) }},00</span>
                                <span class="price-discount">-30%</span>
                                @else
                                <span class="price-current">R$ {{ 45 + ($i * 8) }},00</span>
                                @endif
                            </div>
            </div>

                        <div class="card-footer bg-transparent border-0">
                            <div class="d-grid">
                                <a href="{{ route('products.index') }}" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i>
                                    Ver Produtos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            @endforelse
        </div>
            </div>
</section>

{{-- Call to Action - Conditional based on user status --}}
@auth
    @php
        $user = Auth::user();
        $hasSeller = $user->seller()->exists() && $user->seller->status === 'active';
        $isAdmin = $user->hasRole('admin');
    @endphp
    
    {{-- Se o usuário é seller ativo E admin --}}
    @if($hasSeller && $isAdmin)
        {{-- CTA da Loja --}}
        <section class="py-5 bg-primary bg-opacity-10">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-3">
                            <i class="bi bi-shop me-2 text-primary"></i>
                            {{ $user->seller->store_name }}
                        </h2>
                        <p class="lead mb-0 text-muted">
                            Acesse seu painel de vendedor para gerenciar produtos, pedidos e muito mais.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('seller.dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-shop me-2"></i>
                            {{ Str::limit(Auth::user()->seller->store_name, 20) }}
                        </a>
                    </div>
            </div>
        </div>
    </section>
        
        {{-- CTA Admin --}}
        <section class="py-5 bg-dark bg-opacity-10">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-3">
                            <i class="bi bi-gear-fill me-2 text-dark"></i>
                            Painel Administrativo
                        </h2>
                        <p class="lead mb-0 text-muted">
                            Gerencie vendedores, categorias e configurações da plataforma.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-lg">
                            <i class="bi bi-gear-fill me-2"></i>
                            Administrador
                        </a>
                    </div>
                </div>
            </div>
        </section>
    {{-- Se o usuário é seller ativo (mas não admin) --}}
    @elseif($hasSeller && !$isAdmin)
        <section class="py-5 bg-primary bg-opacity-10">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-3">
                            <i class="bi bi-shop me-2 text-primary"></i>
                            {{ $user->seller->store_name }}
                        </h2>
                        <p class="lead mb-0 text-muted">
                            Acesse seu painel de vendedor para gerenciar produtos, pedidos e muito mais.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('seller.dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-shop me-2"></i>
                            {{ Str::limit(Auth::user()->seller->store_name, 20) }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    {{-- Se o usuário é admin (mas não seller) --}}
    @elseif($isAdmin && !$hasSeller)
        <section class="py-5 bg-dark bg-opacity-10">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-3">
                            <i class="bi bi-gear-fill me-2 text-dark"></i>
                            Painel Administrativo
                        </h2>
                        <p class="lead mb-0 text-muted">
                            Gerencie vendedores, categorias e configurações da plataforma.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-dark btn-lg">
                            <i class="bi bi-gear-fill me-2"></i>
                            Administrador
                        </a>
                </div>
            </div>
        </div>
    </section>
    {{-- Usuário comum --}}
    @else
        <section class="py-5" style="background: linear-gradient(135deg, {{ '#0d6efd' }} 0%, {{ '#D2691E' }} 100%);">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8 text-white">
                        <h2 class="fw-bold mb-3">Quer vender no Vale do Sol?</h2>
                        <p class="lead mb-0">
                            Faça parte da nossa comunidade de vendedores locais.
                            Cadastro gratuito e comissões justas.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('seller.register') }}" class="btn btn-light btn-lg">
                            <i class="bi bi-shop me-2"></i>
                            Começar Agora
                        </a>
                    </div>
                </div>
            </div>
        </section>
        @endif
@else
    {{-- Guest user --}}
    <section class="py-5" style="background: linear-gradient(135deg, {{ '#588c4c' }} 0%, {{ '#D2691E' }} 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8 text-white">
                    <h2 class="fw-bold mb-3">Quer vender no Vale do Sol?</h2>
                    <p class="lead mb-0">
                        Faça parte da nossa comunidade de vendedores locais.
                        Cadastro gratuito e comissões justas.
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                        <i class="bi bi-shop me-2"></i>
                        Começar Agora
                    </a>
                </div>
            </div>
        </div>
    </section>
@endauth

@endsection
