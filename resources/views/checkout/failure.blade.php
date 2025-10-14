@extends('layouts.public')

@section('title', 'Pagamento Não Realizado - Vale do Sol')

@section('page-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            {{-- Error Icon --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger" style="width: 80px; height: 80px;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>

            {{-- Error Message --}}
            <div class="text-center mb-4">
                <h1 class="display-5 fw-bold text-dark mb-3">
                    Pagamento Não Realizado
                </h1>
                <p class="lead text-muted mb-2">
                    Não foi possível processar seu pagamento.
                </p>
                @if($paymentId)
                    <p class="small text-muted">
                        ID da Tentativa: <span class="font-monospace fw-medium">{{ $paymentId }}</span>
                    </p>
                @endif
            </div>

            {{-- Order Details --}}
            @if($order)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-3">Detalhes do Pedido</h2>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Número do Pedido</span>
                        <span class="fw-medium">#{{ $order->id }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Vendedor</span>
                        <span class="fw-medium">{{ $order->seller->store_name }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-danger-subtle text-danger">
                            {{ $order->status === 'failed' ? 'Falhou' : ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <span class="fw-semibold">Valor do Pedido</span>
                        <span class="h5 fw-bold mb-0">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Possible Reasons --}}
            <div class="alert alert-warning border-warning">
                <h3 class="h6 d-flex align-items-center gap-2 fw-semibold mb-3">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Possíveis Motivos
                </h3>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Dados do cartão incorretos ou cartão expirado</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Limite insuficiente no cartão</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Problema na conexão durante o pagamento</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-0">
                        <svg class="flex-shrink-0 mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Recusa pela operadora do cartão</span>
                    </li>
                </ul>
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
                <a
                    href="{{ route('checkout.index') }}"
                    class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2"
                >
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Tentar Novamente
                </a>
                <a
                    href="{{ route('home') }}"
                    class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2"
                >
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Voltar ao Início
                </a>
            </div>

            {{-- Support --}}
            <div class="text-center">
                <p class="small text-muted mb-0">
                    Precisa de ajuda?
                    <a href="#" class="fw-medium text-primary text-decoration-none">Entre em contato com o suporte</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
