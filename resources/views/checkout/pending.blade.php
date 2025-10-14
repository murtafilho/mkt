@extends('layouts.public')

@section('title', 'Pagamento em Análise - Vale do Sol')

@section('page-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            {{-- Pending Icon --}}
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width: 80px; height: 80px;">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            {{-- Pending Message --}}
            <div class="text-center mb-4">
                <h1 class="display-5 fw-bold text-dark mb-3">
                    Pagamento em Análise
                </h1>
                <p class="lead text-muted mb-2">
                    Seu pagamento está sendo processado.
                </p>
                @if($paymentId)
                    <p class="small text-muted">
                        ID do Pagamento: <span class="font-monospace fw-medium">{{ $paymentId }}</span>
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
                        <span class="badge bg-warning-subtle text-warning">
                            {{ $order->status === 'pending' ? 'Pendente' : ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <span class="fw-semibold">Valor do Pedido</span>
                        <span class="h5 fw-bold text-primary mb-0">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- What to Expect --}}
            <div class="alert alert-info border-info">
                <h3 class="h6 d-flex align-items-center gap-2 fw-semibold mb-3">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    O que esperar
                </h3>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 text-info mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Seu pagamento está sendo analisado pela operadora do cartão</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 text-info mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">O processo pode levar até 48 horas úteis</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 text-info mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Você receberá um e-mail assim que o pagamento for confirmado</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-0">
                        <svg class="flex-shrink-0 text-info mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Acompanhe o status na área "Meus Pedidos"</span>
                    </li>
                </ul>
            </div>

            {{-- Common Reasons --}}
            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h3 class="h6 fw-semibold mb-3">
                        Motivos comuns para pagamento pendente:
                    </h3>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2 small text-muted">
                            Pagamento via boleto ou transferência bancária (aguardando confirmação)
                        </li>
                        <li class="mb-2 small text-muted">
                            Primeira compra com este cartão (análise de segurança)
                        </li>
                        <li class="mb-0 small text-muted">
                            Valor alto da transação (verificação adicional)
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mb-4">
                <a
                    href="{{ route('customer.orders.index') }}"
                    class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2"
                >
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Ver Meus Pedidos
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
                    Dúvidas sobre seu pedido?
                    <a href="#" class="fw-medium text-primary text-decoration-none">Entre em contato com o suporte</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
