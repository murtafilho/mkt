{{-- Main Header --}}
<header class="sticky-top" style="min-height: 80px; display: flex; align-items: center;">
    <div class="container px-3 px-lg-4 h-100">
        <div class="row align-items-center justify-content-between g-2 g-lg-3 h-100">
            {{-- Logo + Site Name (Mobile: 50% | Desktop: 3 cols ~25%) --}}
            <div class="col-6 col-lg-3">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                    {{-- Logo Estático --}}
                    <img src="{{ asset('images/logo-principal.svg') }}"
                         alt="{{ config('app.name') }}"
                         style="width: 48px; height: 48px;"
                         class="me-2">
                    <span class="site-name d-none d-md-inline" style="font-weight: 600; color: #588c4c; font-size: 1.5rem;">
                        {{ config('app.name') }}
                    </span>
                </a>
            </div>

            {{-- Search Bar (Desktop: 6 cols ~50%) - Desktop Only --}}
            <div class="col-lg-6 d-none d-lg-block">
                @include('components.search-bar')
            </div>

            {{-- User Actions (Mobile: 50% | Desktop: 3 cols ~25%) --}}
            <div class="col-6 col-lg-3">
                <div class="user-actions">
                    {{-- Cart Button - Bootstrap Offcanvas --}}
                    <div class="position-relative d-inline-block">
                        <button
                            type="button"
                            class="btn btn-icon-cart"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#cartOffcanvas"
                            aria-controls="cartOffcanvas"
                            aria-label="Carrinho de compras">
                            <i class="bi bi-cart"></i>
                        </button>
                        <span class="cart-badge" style="display: none;">0</span>
                    </div>

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
            <div class="col-12 d-lg-none">
                @include('components.search-bar')
            </div>
        </div>
    </div>
</header>

