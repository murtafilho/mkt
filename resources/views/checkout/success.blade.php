@extends('layouts.public')

@section('title', 'Pedido Confirmado - Vale do Sol')

@section('page-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            @php
            $orderId = request()->query('order_id');
            $paymentId = request()->query('payment_id');
            $status = request()->query('status');
            $paymentMethod = request()->query('payment_method');
            $qrCode = request()->query('qr_code');
            $qrCodeBase64 = request()->query('qr_code_base64');
            @endphp

            {{-- Success/Pending Icon --}}
            <div class="text-center mb-4">
                @if($status === 'pending' && $paymentMethod === 'bank_transfer')
                    {{-- PIX Pending --}}
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning" style="width: 80px; height: 80px;">
                        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @else
                    {{-- Success --}}
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success" style="width: 80px; height: 80px;">
                        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Success Message --}}
            <div class="text-center mb-4">
                @if($status === 'pending' && $paymentMethod === 'bank_transfer')
                    <h1 class="display-5 fw-bold text-dark mb-3">
                        Aguardando Pagamento PIX
                    </h1>
                    <p class="lead text-muted mb-2">
                        Seu pedido foi criado. Escaneie o QR Code abaixo para finalizar o pagamento.
                    </p>
                @else
                    <h1 class="display-5 fw-bold text-dark mb-3">
                        Pagamento Confirmado!
                    </h1>
                    <p class="lead text-muted mb-2">
                        Seu pedido foi realizado com sucesso.
                    </p>
                @endif
                @if($paymentId)
                    <p class="small text-muted">
                        ID do Pagamento: <span class="font-monospace fw-medium">{{ $paymentId }}</span>
                    </p>
                @endif
            </div>

            {{-- PIX QR Code Section --}}
            @if($status === 'pending' && $paymentMethod === 'bank_transfer' && $qrCodeBase64)
            <div class="card border-primary shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 text-center fw-semibold mb-4">
                        Pague com PIX
                    </h2>

                    {{-- QR Code Image --}}
                    <div class="d-flex justify-content-center mb-4">
                        <img
                            src="data:image/png;base64,{{ $qrCodeBase64 }}"
                            alt="QR Code PIX"
                            loading="eager"
                            decoding="async"
                            class="border border-2 border-secondary rounded"
                            style="width: 256px; height: 256px;"
                        />
                    </div>

                    {{-- Copy PIX Code --}}
                    @if($qrCode)
                    <div class="mb-3">
                        <p class="text-center small text-muted mb-2">
                            Ou copie o código PIX abaixo:
                        </p>
                        <div class="input-group">
                            <input
                                type="text"
                                id="pixCode"
                                value="{{ $qrCode }}"
                                readonly
                                class="form-control form-control-sm font-monospace bg-light"
                            />
                            <button
                                onclick="copyPixCode()"
                                class="btn btn-primary btn-sm"
                                type="button"
                            >
                                Copiar
                            </button>
                        </div>
                        <p id="copyMessage" class="text-center small text-success mt-2 d-none">
                            ✓ Código copiado!
                        </p>
                    </div>
                    @endif

                    {{-- PIX Instructions --}}
                    <div class="alert alert-primary mb-0">
                        <p class="fw-semibold mb-2">Como pagar:</p>
                        <ol class="mb-2 ps-3">
                            <li>Abra o app do seu banco</li>
                            <li>Escolha pagar via PIX</li>
                            <li>Escaneie o QR Code ou cole o código</li>
                            <li>Confirme o pagamento</li>
                        </ol>
                        <p class="small mb-0">
                            ⏱️ O pagamento será confirmado em até 1 minuto.
                        </p>
                    </div>
                </div>
            </div>
            @endif

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
                        <span class="badge bg-success-subtle text-success">
                            {{ $order->status === 'paid' ? 'Pago' : ucfirst($order->status) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <span class="fw-semibold">Total Pago</span>
                        <span class="h5 fw-bold text-primary mb-0">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Next Steps --}}
            <div class="alert alert-primary border-primary">
                <h3 class="h6 d-flex align-items-center gap-2 fw-semibold mb-3">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Próximos Passos
                </h3>
                <ul class="list-unstyled mb-0">
                    @if($status === 'pending' && $paymentMethod === 'bank_transfer')
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 text-primary mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Complete o pagamento via PIX usando o QR Code acima.</span>
                    </li>
                    @endif
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 text-primary mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Você receberá um e-mail de confirmação com os detalhes do pedido.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-2">
                        <svg class="flex-shrink-0 text-primary mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">O vendedor será notificado e preparará seu pedido para envio.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2 mb-0">
                        <svg class="flex-shrink-0 text-primary mt-1" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="small">Você pode acompanhar o status do seu pedido na área "Meus Pedidos".</span>
                    </li>
                </ul>
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4">
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
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyPixCode() {
    const pixCodeInput = document.getElementById('pixCode');
    const copyMessage = document.getElementById('copyMessage');

    // Select and copy
    pixCodeInput.select();
    pixCodeInput.setSelectionRange(0, 99999); // For mobile
    document.execCommand('copy');

    // Show confirmation message
    copyMessage.classList.remove('d-none');
    setTimeout(() => {
        copyMessage.classList.add('d-none');
    }, 3000);
}
</script>
@endpush
@endsection
