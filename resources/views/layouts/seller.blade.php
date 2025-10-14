@extends('layouts.base')

@section('content')
    {{-- Mobile-First Layout: Sidebar + Main Content --}}
    <div class="d-flex min-vh-100">
        {{-- Sidebar: Hidden mobile → Show desktop (md:) --}}
        <aside class="d-none d-md-flex flex-column" style="width: 16rem;">
            {{-- Sidebar Container: Fixed height with scroll --}}
            <div class="d-flex flex-column flex-grow-1 overflow-auto bg-white border-end">
                    {{-- Logo/Brand --}}
                    <div class="d-flex align-items-center flex-shrink-0 px-3 border-bottom" style="height: 4rem;">
                        <a href="/" class="d-flex align-items-center text-decoration-none">
                            @if(!empty($logoSettings['logo_svg']))
                                <div class="logo-svg-container me-2" style="width: 40px; height: 40px;">
                                    {!! $logoSettings['logo_svg'] !!}
                                </div>
                                <span class="fs-5 fw-bold text-dark">
                                    {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Vendedor</span>
                                </span>
                            @else
                                <i class="bi bi-shop me-2 fs-4 text-primary"></i>
                                <span class="fs-5 fw-bold text-dark">
                                    {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Vendedor</span>
                                </span>
                            @endif
                        </a>
                    </div>

                {{-- Navigation Links --}}
                <nav class="flex-grow-1 px-2 py-3">
                    @php
                        $currentRoute = request()->route()->getName();
                    @endphp
                    <div class="d-flex flex-column gap-1">
                        <a href="{{ route('seller.dashboard') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.dashboard') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="fw-medium">Dashboard</span>
                        </a>

                        <a href="{{ route('seller.products.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.products') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="fw-medium">Produtos</span>
                        </a>

                        <a href="{{ route('seller.orders.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.orders') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span class="fw-medium">Pedidos</span>
                        </a>

                        <a href="#" class="d-flex align-items-center rounded px-3 py-2 text-decoration-none text-secondary hover-bg-primary-subtle">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="fw-medium">Financeiro</span>
                        </a>

                        <a href="{{ route('seller.profile.show') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.profile') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="fw-medium">Meu Perfil</span>
                        </a>
                    </div>
                </nav>

                {{-- Sidebar Footer --}}
                <div class="flex-shrink-0 border-top p-3">
                    <a href="/" class="d-d-block text-decoration-none">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 small fw-medium text-dark">{{ auth()->user()->seller->store_name }}</p>
                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">Ver loja pública</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="d-flex flex-column flex-grow-1">
            {{-- Top Bar: Mobile menu + User dropdown --}}
            <header class="bg-white shadow-sm border-bottom">
                <div class="d-flex align-items-center justify-content-between px-3 px-lg-4" style="height: 4rem;">
                    {{-- Mobile Menu Button: Show mobile → Hide desktop --}}
                    <button type="button"
                            class="btn btn-link text-secondary p-0 d-md-none"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#sellerSidebar"
                            aria-controls="sellerSidebar">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    {{-- Page Title --}}
                    <div class="flex-grow-1">
                        <h1 class="mb-0 fs-5 fw-semibold text-dark">
                            @yield('header', 'Dashboard')
                        </h1>
                    </div>

                    {{-- Right Side: Role Switch + Notifications + User --}}
                    <div class="d-flex align-items-center gap-2">
                        {{-- Role Switch: Show only if user is also an admin --}}
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}"
                               class="btn btn-sm btn-outline-warning d-none d-sm-inline-d-flex align-items-center gap-2">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Administração</span>
                            </a>
                        @endif

                        {{-- Notifications --}}
                        <button type="button" class="btn btn-link text-secondary p-2">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>

                        {{-- User Dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none text-dark dropdown-toggle d-flex align-items-center gap-2" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <span class="d-none d-sm-inline">{{ Auth::user()->name }}</span>
                                @if(Auth::user()->hasRole('admin'))
                                    <span class="badge bg-warning text-dark d-none d-sm-inline">Admin + Seller</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <div class="dropdown-header">
                                        <small class="text-uppercase text-muted">Contexto Atual</small>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <div class="bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>
                                            <span class="fw-medium">Minha Loja</span>
                                        </div>
                                        <small class="text-muted">{{ auth()->user()->seller->store_name }}</small>
                                    </div>
                                </li>

                                @if(Auth::user()->hasRole('admin'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            Ir para Administração
                                        </a>
                                    </li>
                                @endif

                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">Meu Perfil</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/">Ver Site</a>
                                </li>

                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Sair</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Content: Mobile-first padding --}}
            <main class="flex-grow-1 p-3 p-lg-4 bg-light">
                @yield('page-content')
            </main>
        </div>
    </div>

    {{-- Bootstrap Offcanvas Mobile Sidebar --}}
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sellerSidebar" aria-labelledby="sellerSidebarLabel">
        {{-- Offcanvas Header --}}
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="sellerSidebarLabel">
                    <a href="/" class="d-flex align-items-center text-decoration-none">
                        @if(!empty($logoSettings['logo_svg']))
                            <div class="logo-svg-container me-2" style="width: 36px; height: 36px;">
                                {!! $logoSettings['logo_svg'] !!}
                            </div>
                            <span class="fs-5 fw-bold text-dark">
                                {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Vendedor</span>
                            </span>
                        @else
                            <i class="bi bi-shop me-2 fs-4 text-primary"></i>
                            <span class="fs-5 fw-bold text-dark">
                                {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Vendedor</span>
                            </span>
                        @endif
                    </a>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

        {{-- Offcanvas Body --}}
        <div class="offcanvas-body p-0">
            <nav class="flex-grow-1 px-2 py-3">
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('seller.dashboard') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.dashboard') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="fw-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('seller.products.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.products') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="fw-medium">Produtos</span>
                    </a>

                    <a href="{{ route('seller.orders.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.orders') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="fw-medium">Pedidos</span>
                    </a>

                    <a href="#" class="d-flex align-items-center rounded px-3 py-2 text-decoration-none text-secondary hover-bg-primary-subtle">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="fw-medium">Financeiro</span>
                    </a>

                    <a href="{{ route('seller.profile.show') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'seller.profile') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="fw-medium">Meu Perfil</span>
                    </a>
                </div>
            </nav>

            {{-- Offcanvas Footer --}}
            <div class="mt-auto border-top p-3">
                <a href="/" class="d-d-block text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 small fw-medium text-dark">{{ auth()->user()->seller->store_name }}</p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Ver loja pública</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

