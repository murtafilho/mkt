{{-- Top Bar - Localização e Links Úteis --}}
<div class="top-bar d-none d-md-block">
    <div class="container px-4 px-lg-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="bi bi-geo-alt"></i>
                    Atendemos: Vale do Sol, Pasárgada, Jardim Canadá
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small>
                    <a href="#" class="text-decoration-none">Ajuda</a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('register') }}" class="text-decoration-none">Vender no Marketplace</a>
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Main Header --}}
<header class="sticky-top">
    <div class="container px-4 px-lg-5">
        <div class="row align-items-center header-content g-2 g-lg-3">
            {{-- Logo (Mobile: 50% | Desktop: 2 cols ~16.6%) --}}
            <div class="col-6 col-lg-2">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="bi bi-sun me-2"></i>
                    {{ config('app.name') }}
                </a>
            </div>

            {{-- Search Bar (Desktop: 7 cols ~58.3%) - Desktop Only --}}
            <div class="col-lg-7 d-none d-lg-block">
                @include('components.search-bar')
            </div>

            {{-- User Actions (Mobile: 50% | Desktop: 3 cols 25%) --}}
            <div class="col-6 col-lg-3">
                <div class="user-actions" x-data>
                    {{-- Cart Button --}}
                    <button class="btn btn-icon-cart position-relative" 
                            type="button"
                            data-bs-toggle="offcanvas" 
                            data-bs-target="#cartOffcanvas"
                            aria-label="Carrinho de compras">
                        <i class="bi bi-cart"></i>
                        <span x-show="$store.cart.count > 0" 
                              x-text="$store.cart.count"
                              class="cart-badge"
                              x-transition></span>
                    </button>

                    {{-- User Menu --}}
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-icon-user dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    aria-label="Menu do usuário">
                                <i class="bi bi-person"></i>
                                <span class="d-none d-lg-inline">{{ Str::limit(Auth::user()->name, 15) }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                {{-- Admin Section --}}
                                @if(Auth::user()->hasRole('admin'))
                                    <li>
                                        <a class="dropdown-item text-warning fw-medium" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-shield-check me-2"></i>Admin
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif

                                {{-- Seller Section --}}
                                @if(Auth::user()->seller && Auth::user()->seller->status === 'active')
                                    <li>
                                        <a class="dropdown-item text-primary fw-medium" href="{{ route('seller.dashboard') }}">
                                            <i class="bi bi-shop me-2"></i>Minha Loja
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                @endif

                                {{-- Customer Actions --}}
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.orders.index') }}">
                                        <i class="bi bi-bag me-2"></i>Meus Pedidos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-person me-2"></i>Meu Perfil
                                    </a>
                                </li>

                                {{-- Logout --}}
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Sair
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        {{-- Guest Actions --}}
                        <a href="{{ route('login') }}" class="btn btn-text-only d-none d-md-inline-flex">
                            Entrar
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="bi bi-person-plus d-md-none"></i>
                            <span class="d-none d-md-inline">Cadastrar</span>
                        </a>
                    @endauth
                </div>
            </div>

            {{-- Mobile Search (100%) --}}
            <div class="col-12 d-lg-none mt-3">
                @include('components.search-bar')
            </div>
        </div>
    </div>

    {{-- Navigation Menu --}}
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container px-4 px-lg-5">
            {{-- Mobile Toggle --}}
            <button class="navbar-toggler" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            {{-- Nav Items --}}
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house me-1"></i>Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('produtos*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            <i class="bi bi-grid-3x3-gap me-1"></i>Produtos
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" 
                           href="#" 
                           role="button" 
                           data-bs-toggle="dropdown">
                            <i class="bi bi-tag me-1"></i>Categorias
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('products.index') }}">Todas as Categorias</a></li>
                            <li><hr class="dropdown-divider" /></li>
                            @foreach($headerCategories ?? [] as $category)
                                <li>
                                    <a class="dropdown-item" href="{{ route('products.index', ['category' => $category->id]) }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#sobre">
                            <i class="bi bi-info me-1"></i>Sobre
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

