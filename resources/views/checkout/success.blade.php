@extends('layouts.public')

@section('title', 'Vale do Sol')

@section('page-content')
 <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
 @php
 $orderId = request()->query('order_id');
 $paymentId = request()->query('payment_id');
 $status = request()->query('status');
 $paymentMethod = request()->query('payment_method');
 $qrCode = request()->query('qr_code');
 $qrCodeBase64 = request()->query('qr_code_base64');
 @endphp

 {{-- Success/Pending Icon --}}
 <div class="mb-8 flex justify-center">
 @if($status === 'pending' && $paymentMethod === 'bank_transfer')
 {{-- PIX Pending --}}
 <div class="flex h-20 w-20 items-center justify-center rounded-full bg-warning-100">
 <svg class="h-12 w-12 text-warning-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 </div>
 @else
 {{-- Success --}}
 <div class="flex h-20 w-20 items-center justify-center rounded-full bg-success-100">
 <svg class="h-12 w-12 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
 </svg>
 </div>
 @endif
 </div>

 {{-- Success Message --}}
 <div class="text-center">
 @if($status === 'pending' && $paymentMethod === 'bank_transfer')
 <h1 class="mb-4 text-3xl font-bold text-neutral-900 sm:text-4xl">
 Aguardando Pagamento PIX
 </h1>
 <p class="mb-2 text-lg text-neutral-600">
 Seu pedido foi criado. Escaneie o QR Code abaixo para finalizar o pagamento.
 </p>
 @else
 <h1 class="mb-4 text-3xl font-bold text-neutral-900 sm:text-4xl">
 Pagamento Confirmado!
 </h1>
 <p class="mb-2 text-lg text-neutral-600">
 Seu pedido foi realizado com sucesso.
 </p>
 @endif
 @if($paymentId)
 <p class="text-sm text-neutral-500">
 ID do Pagamento: <span class="font-mono font-medium">{{ $paymentId }}</span>
 </p>
 @endif
 </div>

 {{-- PIX QR Code Section --}}
 @if($status === 'pending' && $paymentMethod === 'bank_transfer' && $qrCodeBase64)
 <div class="mt-8 rounded-xl border border-primary-200 bg-white p-6 shadow-sm">
 <h2 class="mb-4 text-center text-lg font-semibold text-neutral-900">
 Pague com PIX
 </h2>

 {{-- QR Code Image --}}
 <div class="flex justify-center mb-4">
 <img
 src="data:image/png;base64,{{ $qrCodeBase64 }}"
 alt="QR Code PIX"
 loading="eager"
 decoding="async"
 class="w-64 h-64 border-2 border-neutral-200 rounded-lg"
 />
 </div>

 {{-- Copy PIX Code --}}
 @if($qrCode)
 <div class="space-y-3">
 <p class="text-center text-sm text-neutral-600">
 Ou copie o código PIX abaixo:
 </p>
 <div class="relative">
 <input
 type="text"
 id="pixCode"
 value="{{ $qrCode }}"
 readonly
 class="w-full rounded-lg border-neutral-300 bg-neutral-50 px-4 py-2 pr-24 font-mono text-sm text-neutral-700"
 />
 <button
 onclick="copyPixCode()"
 class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md bg-primary-600 px-4 py-1.5 text-sm font-medium text-white transition hover:bg-primary-700"
 >
 Copiar
 </button>
 </div>
 <p id="copyMessage" class="text-center text-sm text-success-600 hidden">
 ✓ Código copiado!
 </p>
 </div>
 @endif

 {{-- PIX Instructions --}}
 <div class="mt-6 space-y-2 rounded-lg bg-primary-50 p-4 text-sm text-primary-900">
 <p class="font-semibold">Como pagar:</p>
 <ol class="ml-4 list-decimal space-y-1">
 <li>Abra o app do seu banco</li>
 <li>Escolha pagar via PIX</li>
 <li>Escaneie o QR Code ou cole o código</li>
 <li>Confirme o pagamento</li>
 </ol>
 <p class="mt-3 text-xs text-primary-700">
 ⏱️ O pagamento será confirmado em até 1 minuto.
 </p>
 </div>
 </div>
 @endif

 {{-- Order Details --}}
 @if($order)
 <div class="mt-8 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
 <h2 class="mb-4 text-lg font-semibold text-neutral-900">Detalhes do Pedido</h2>

 <div class="space-y-3 text-sm">
 <div class="flex justify-between">
 <span class="text-neutral-600">Número do Pedido</span>
 <span class="font-medium text-neutral-900">#{{ $order->id }}</span>
 </div>

 <div class="flex justify-between">
 <span class="text-neutral-600">Vendedor</span>
 <span class="font-medium text-neutral-900">{{ $order->seller->store_name }}</span>
 </div>

 <div class="flex justify-between">
 <span class="text-neutral-600">Status</span>
 <span class="inline-flex items-center rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-800">
 {{ $order->status === 'paid' ? 'Pago' : ucfirst($order->status) }}
 </span>
 </div>

 <div class="flex justify-between border-t border-neutral-200 pt-3">
 <span class="font-semibold text-neutral-900">Total Pago</span>
 <span class="text-lg font-bold text-primary-600">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
 </div>
 </div>
 </div>
 @endif

 {{-- Next Steps --}}
 <div class="mt-8 rounded-xl border border-primary-200 bg-primary-50 p-6">
 <h3 class="mb-3 flex items-center gap-2 text-lg font-semibold text-primary-900">
 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 Próximos Passos
 </h3>
 <ul class="space-y-2 text-sm text-primary-900">
 @if($status === 'pending' && $paymentMethod === 'bank_transfer')
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Complete o pagamento via PIX usando o QR Code acima.</span>
 </li>
 @endif
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Você receberá um e-mail de confirmação com os detalhes do pedido.</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>O vendedor será notificado e preparará seu pedido para envio.</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Você pode acompanhar o status do seu pedido na área"Meus Pedidos".</span>
 </li>
 </ul>
 </div>

 {{-- Actions --}}
 <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
 <a
 href="#"
 class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
 >
 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
 </svg>
 Ver Meus Pedidos
 </a>
 <a
 href="{{ route('home') }}"
 class="inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-white px-8 py-3 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
 >
 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
 </svg>
 Voltar ao Início
 </a>
 </div>
 </div>

 <script>
 function copyPixCode() {
 const pixCodeInput = document.getElementById('pixCode');
 const copyMessage = document.getElementById('copyMessage');

 // Select and copy
 pixCodeInput.select();
 pixCodeInput.setSelectionRange(0, 99999); // For mobile
 document.execCommand('copy');

 // Show confirmation message
 copyMessage.classList.remove('hidden');
 setTimeout(() => {
 copyMessage.classList.add('hidden');
 }, 3000);
 }
 </script>
@endsection
