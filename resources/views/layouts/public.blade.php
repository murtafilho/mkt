@extends('layouts.base')

@section('content')
    {{-- Header Unificado --}}
    @include('layouts.partials.header')

    {{-- Main Content --}}
    <main>
        @yield('page-content')
    </main>

    {{-- Cart Drawer (Alpine.js) --}}
    <x-cart-drawer />

    {{-- Footer --}}
    <footer class="py-5 bg-dark text-white">
        <div class="container px-4 px-lg-5">
            <div class="row g-4">
                {{-- About --}}
                <div class="col-lg-4 mb-3">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-sun me-2 text-warning"></i>
                        {{ config('app.name') }}
                    </h5>
                    <p class="small mb-3">
                        Favorencendo a vocação e o comércio local.
                        Conectando vendedores locais com consumidores conscientes.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white-50"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white-50"><i class="bi bi-whatsapp fs-5"></i></a>
                    </div>
                </div>

                {{-- Links --}}
                <div class="col-6 col-lg-2 mb-3">
                    <h6 class="fw-bold mb-3">Marketplace</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('products.index') }}" class="text-white-50 text-decoration-none small">Produtos</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none small">Vendedores</a></li>
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none small">Categorias</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2 mb-3">
                    <h6 class="fw-bold mb-3">Sua Conta</h6>
                    <ul class="list-unstyled">
                        @auth
                            <li class="mb-2"><a href="{{ route('customer.orders.index') }}" class="text-white-50 text-decoration-none small">Pedidos</a></li>
                            <li class="mb-2"><a href="{{ route('profile.edit') }}" class="text-white-50 text-decoration-none small">Perfil</a></li>
                        @else
                            <li class="mb-2"><a href="{{ route('login') }}" class="text-white-50 text-decoration-none small">Entrar</a></li>
                            <li class="mb-2"><a href="{{ route('register') }}" class="text-white-50 text-decoration-none small">Cadastrar</a></li>
                        @endauth
                        <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none small">Ajuda</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 mb-3">
                    <h6 class="fw-bold mb-3">Quer vender?</h6>
                    <p class="small text-white-50 mb-3">
                        Faça parte da nossa comunidade de vendedores locais.
                    </p>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-shop me-2"></i>
                        Começar Agora
                    </a>
                </div>
            </div>

            <hr class="my-4 bg-white opacity-25">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small class="text-white-50">
                        &copy; {{ date('Y') }} {{ config('app.name') }} Marketplace. Todos os direitos reservados.
                    </small>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <small class="text-white-50">
                        <a href="#" class="text-white-50 text-decoration-none me-3">Termos de Uso</a>
                        <a href="#" class="text-white-50 text-decoration-none">Privacidade</a>
                    </small>
                </div>
            </div>
        </div>
    </footer>
@endsection
