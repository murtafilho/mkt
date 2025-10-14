@extends('layouts.base')

@section('content')
    {{-- Mobile-First Layout: Sidebar + Main Content --}}
    <div class="d-flex min-vh-100">
        {{-- Sidebar: Hidden mobile → Show desktop (md:) --}}
        <aside class="d-none d-md-flex flex-column" style="width: 16rem;">
            {{-- Sidebar Container: Fixed height with scroll --}}
            <div class="d-flex flex-column flex-grow-1 overflow-auto bg-white border-end">
                    {{-- Logo/Brand --}}
                    <div class="d-flex align-items-center h-100px flex-shrink-0 px-3 border-bottom" style="height: 4rem;">
                        <a href="/" class="d-flex align-items-center text-decoration-none">
                            @if(!empty($logoSettings['logo_svg']))
                                <div class="logo-svg-container me-2" style="width: 40px; height: 40px;">
                                    {!! $logoSettings['logo_svg'] !!}
                                </div>
                                <span class="fs-5 fw-bold text-dark">
                                    {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Admin</span>
                                </span>
                            @else
                                <i class="bi bi-shop me-2 fs-4 text-primary"></i>
                                <span class="fs-5 fw-bold text-dark">
                                    {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Admin</span>
                                </span>
                            @endif
                        </a>
                    </div>

                {{-- Navigation Links --}}
                <nav class="flex-grow-1 px-2 py-3" style="padding-bottom: 1rem;">
                    @php
                        $currentRoute = request()->route()->getName();
                    @endphp
                    <div class="d-flex flex-column gap-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="fw-medium">Dashboard</span>
                        </a>

                        <a href="{{ route('admin.sellers.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.sellers') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="fw-medium">Vendedores</span>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.categories') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span class="fw-medium">Categorias</span>
                        </a>

                        <a href="{{ route('admin.reports.products') }}" class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.reports.products') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="fw-medium">Produtos</span>
                        </a>

                        <a href="{{ route('admin.orders.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.orders') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span class="fw-medium">Pedidos</span>
                        </a>

                        <a href="{{ route('admin.reports.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.reports') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="fw-medium">Relatórios</span>
                        </a>

                        <a href="{{ route('admin.settings.index') }}" 
                           class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.settings') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                            <svg class="me-3 flex-shrink-0" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="fw-medium">Configurações</span>
                        </a>
                    </div>
                </nav>

                {{-- Sidebar Footer --}}
                <div class="flex-shrink-0 border-top p-3">
                    <a href="/" class="d-d-block text-decoration-none">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 fw-medium text-dark small">Administrador</p>
                                <p class="mb-0 text-muted" style="font-size: 0.75rem;">Ver site público</p>
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
                            data-bs-target="#adminSidebar"
                            aria-controls="adminSidebar">
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
                        {{-- Role Switch: Show only if user is also a seller --}}
                        @if(Auth::user()->seller && Auth::user()->seller->status === 'active')
                            <a href="{{ route('seller.dashboard') }}"
                               class="btn btn-sm btn-outline-primary d-none d-sm-inline-d-flex align-items-center gap-2">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span>Minha Loja</span>
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
                                @if(Auth::user()->seller && Auth::user()->seller->status === 'active')
                                    <span class="badge bg-warning text-dark d-none d-sm-inline">Admin + Seller</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <div class="dropdown-header">
                                        <small class="text-uppercase text-muted">Contexto Atual</small>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <div class="bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>
                                            <span class="fw-medium">Administração</span>
                                        </div>
                                    </div>
                                </li>

                                @if(Auth::user()->seller && Auth::user()->seller->status === 'active')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('seller.dashboard') }}">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            Ir para Minha Loja
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
    <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        {{-- Offcanvas Header --}}
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="adminSidebarLabel">
                    <a href="/" class="d-flex align-items-center text-decoration-none">
                        @if(!empty($logoSettings['logo_svg']))
                            <div class="logo-svg-container me-2" style="width: 36px; height: 36px;">
                                {!! $logoSettings['logo_svg'] !!}
                            </div>
                            <span class="fs-5 fw-bold text-dark">
                                {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Admin</span>
                            </span>
                        @else
                            <i class="bi bi-shop me-2 fs-4 text-primary"></i>
                            <span class="fs-5 fw-bold text-dark">
                                {{ $logoSettings['site_name'] }} <span class="fs-6 fw-normal text-secondary">Admin</span>
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
                    <a href="{{ route('admin.dashboard') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.dashboard') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="fw-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('admin.sellers.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.sellers') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="fw-medium">Vendedores</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.categories') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span class="fw-medium">Categorias</span>
                    </a>

                    <a href="{{ route('admin.reports.products') }}" class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.reports.products') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="fw-medium">Produtos</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.orders') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="fw-medium">Pedidos</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.reports') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="fw-medium">Relatórios</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}"
                       class="d-flex align-items-center rounded px-3 py-2 text-decoration-none {{ str_starts_with($currentRoute, 'admin.settings') ? 'text-white bg-primary' : 'text-secondary hover-bg-primary-subtle' }}">
                        <svg class="me-3" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="fw-medium">Configurações</span>
                    </a>
                </div>
            </nav>

            {{-- Offcanvas Footer --}}
            <div class="mt-auto border-top p-3">
                <a href="/" class="d-d-block text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 fw-medium text-dark small">Administrador</p>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Ver site público</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

