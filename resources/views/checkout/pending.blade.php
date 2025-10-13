@extends('layouts.public')

@section('title', 'Vale do Sol')

@section('page-content')
 <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
 {{-- Pending Icon --}}
 <div class="mb-8 flex justify-center">
 <div class="flex h-20 w-20 items-center justify-center rounded-full bg-warning-100">
 <svg class="h-12 w-12 text-warning-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 </div>
 </div>

 {{-- Pending Message --}}
 <div class="text-center">
 <h1 class="mb-4 text-3xl font-bold text-neutral-900 sm:text-4xl">
 Pagamento em Análise
 </h1>
 <p class="mb-2 text-lg text-neutral-600">
 Seu pagamento está sendo processado.
 </p>
 @if($paymentId)
 <p class="text-sm text-neutral-500">
 ID do Pagamento: <span class="font-mono font-medium">{{ $paymentId }}</span>
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
 <span class="inline-flex items-center rounded-full bg-warning-100 px-2.5 py-0.5 text-xs font-medium text-warning-800">
 {{ $order->status === 'pending' ? 'Pendente' : ucfirst($order->status) }}
 </span>
 </div>

 <div class="flex justify-between border-t border-neutral-200 pt-3">
 <span class="font-semibold text-neutral-900">Valor do Pedido</span>
 <span class="text-lg font-bold text-primary-600">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
 </div>
 </div>
 </div>
 @endif

 {{-- What to Expect --}}
 <div class="mt-8 rounded-xl border border-info-200 bg-info-50 p-6">
 <h3 class="mb-3 flex items-center gap-2 text-lg font-semibold text-info-900">
 <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 O que esperar
 </h3>
 <ul class="space-y-2 text-sm text-info-900">
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Seu pagamento está sendo analisado pela operadora do cartão</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>O processo pode levar até 48 horas úteis</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Você receberá um e-mail assim que o pagamento for confirmado</span>
 </li>
 <li class="flex items-start gap-2">
 <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <span>Acompanhe o status na área"Meus Pedidos"</span>
 </li>
 </ul>
 </div>

 {{-- Common Reasons --}}
 <div class="mt-8 rounded-xl border border-neutral-200 bg-neutral-50 p-6">
 <h3 class="mb-3 text-base font-semibold text-neutral-900">
 Motivos comuns para pagamento pendente:
 </h3>
 <ul class="space-y-1.5 text-sm text-neutral-700">
 <li class="flex items-start gap-2">
 <span class="text-neutral-400">•</span>
 <span>Pagamento via boleto ou transferência bancária (aguardando confirmação)</span>
 </li>
 <li class="flex items-start gap-2">
 <span class="text-neutral-400">•</span>
 <span>Primeira compra com este cartão (análise de segurança)</span>
 </li>
 <li class="flex items-start gap-2">
 <span class="text-neutral-400">•</span>
 <span>Valor alto da transação (verificação adicional)</span>
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

 {{-- Support --}}
 <div class="mt-8 text-center">
 <p class="text-sm text-neutral-600">
 Dúvidas sobre seu pedido?
 <a href="#" class="font-medium text-primary-600 hover:text-primary-700">Entre em contato com o suporte</a>
 </p>
 </div>
 </div>
@endsection
