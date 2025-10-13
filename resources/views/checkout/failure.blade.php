@extends('layouts.public')

@section('title', 'Vale do Sol')

@section('page-content')
 <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
 {{-- Error Icon --}}
 <div class="mb-8 flex justify-center">
 <div class="flex h-20 w-20 items-center justify-center rounded-full bg-error-100">
 <svg class="h-12 w-12 text-error-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
 </svg>
 </div>
 </div>

 {{-- Error Message --}}
 <div class="text-center">
 <h1 class="mb-4 text-3xl font-bold text-neutral-900 sm:text-4xl">
 Pagamento Não Realizado
 </h1>
 <p class="mb-2 text-lg text-neutral-600">
 Não foi possível processar seu pagamento.
 </p>
 @if($paymentId)
 <p class="text-sm text-neutral-500">
 ID da Tentativa: <span class="font-mono font-medium">{{ $paymentId }}</span>
 </p>
 @endif
 </div>

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
 <span class="inline-flex items-center rounded-full bg-error-100 px-2.5 py-0.5 text-xs font-medium text-error-800">
 {{ $order->status === 'failed' ? 'Falhou' : ucfirst($order->status) }}
 </span>
 </div>

 <div class="flex justify-between border-t border-neutral-200 pt-3">
 <span class="font-semibold text-neutral-900">Valor do Pedido</span>
 <span class="text-lg font-bold text-neutral-900">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
 </div>
 </div>
 </div>
 @endif

 {{-- Possible Reasons --}}
 <div class="mt-8 rounded-xl border border-warning-200 bg-warning-50 p-6">
 <h3 class="mb-3 flex items-center gap-2 text-lg font-semibold text-warning-900">
 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
 </svg>
 Possíveis Motivos
 </h3>
 <ul class="space-y-2 text-sm text-warning-900">
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Dados do cartão incorretos ou cartão expirado</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Limite insuficiente no cartão</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Problema na conexão durante o pagamento</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Recusa pela operadora do cartão</span>
 </li>
 </ul>
 </div>

 {{-- Actions --}}
 <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
 <a
 href="{{ route('checkout.index') }}"
 class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
 >
 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
 </svg>
 Tentar Novamente
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

 {{-- Support --}}
 <div class="mt-8 text-center">
 <p class="text-sm text-neutral-600">
 Precisa de ajuda?
 <a href="#" class="font-medium text-primary-600 hover:text-primary-700">Entre em contato com o suporte</a>
 </p>
 </div>
 </div>
@endsection
