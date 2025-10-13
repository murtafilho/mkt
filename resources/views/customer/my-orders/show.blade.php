<x-layouts.app title="Pedido #{{ $order->order_number }}">
 <x-slot:header>
 <h2 class="text-xl font-semibold text-neutral-900">Pedido #{{ $order->order_number }}</h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

 {{-- Breadcrumbs --}}
 <nav class="mb-6 text-sm text-neutral-500">
 <a href="{{ route('home') }}" class="hover:text-neutral-700">Início</a>
 <span class="mx-2">/</span>
 <a href="{{ route('customer.orders.index') }}" class="hover:text-neutral-700">Meus Pedidos</a>
 <span class="mx-2">/</span>
 <span class="text-neutral-900">Pedido #{{ $order->order_number }}</span>
 </nav>

 {{-- Flash messages --}}
 @if(session('success'))
 <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
 {{ session('success') }}
 </div>
 @endif

 @if(session('error'))
 <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
 {{ session('error') }}
 </div>
 @endif

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

 {{-- Left column: Order details --}}
 <div class="lg:col-span-2 space-y-6">

 {{-- Order Status Timeline --}}
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-neutral-900 mb-4">Status do Pedido</h3>
 <x-order-timeline :order="$order" />
 </div>

 {{-- Order Items --}}
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-neutral-900 mb-4">Itens do Pedido</h3>
 <div class="space-y-4">
 @foreach($order->items as $item)
 <div class="flex gap-4 pb-4 border-b border-neutral-200 last:border-0">
 {{-- Product image --}}
 @if($item->product && $item->product->hasMedia('product_images'))
 <img src="{{ $item->product->getFirstMedia('product_images')->getUrl('thumb') }}"
 alt="{{ $item->product->name }}"
 loading="lazy"
 decoding="async"
 class="w-20 h-20 object-cover rounded border border-neutral-200">
 @else
 <div class="w-20 h-20 bg-neutral-200 rounded flex items-center justify-center border border-neutral-300">
 <svg class="w-10 h-10 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 @endif

 {{-- Product info --}}
 <div class="flex-1">
 <h4 class="font-medium text-neutral-900">{{ $item->product->name ?? 'Produto não disponível' }}</h4>
 <p class="text-sm text-neutral-500 mt-1">Quantidade: {{ $item->quantity }}</p>
 <p class="text-sm text-neutral-500">
 Preço unitário: R$ {{ number_format($item->unit_price, 2, ',', '.') }}
 </p>
 </div>

 {{-- Subtotal --}}
 <div class="text-right">
 <p class="font-semibold text-neutral-900">
 R$ {{ number_format($item->unit_price * $item->quantity, 2, ',', '.') }}
 </p>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 {{-- Delivery Address --}}
 @if($order->address)
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-neutral-900 mb-4">Endereço de Entrega</h3>
 <div class="text-neutral-700 space-y-1">
 <p class="font-medium">{{ $order->address->recipient_name }}</p>
 <p>{{ $order->address->street }}, {{ $order->address->number }}</p>
 @if($order->address->complement)
 <p>{{ $order->address->complement }}</p>
 @endif
 <p>{{ $order->address->neighborhood }}</p>
 <p>{{ $order->address->city }}/{{ $order->address->state }}</p>
 <p>CEP: {{ $order->address->postal_code }}</p>
 @if($order->address->recipient_phone)
 <p class="mt-2">Telefone: {{ $order->address->recipient_phone }}</p>
 @endif
 </div>
 </div>
 @else
 <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
 <p class="text-sm text-yellow-800">
 <strong>Atenção:</strong> Endereço de entrega não disponível para este pedido.
 </p>
 </div>
 @endif

 {{-- Customer Notes --}}
 @if($order->notes)
 <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
 <h4 class="font-medium text-blue-900 mb-2">Observações do Pedido</h4>
 <p class="text-sm text-blue-800">{{ $order->notes }}</p>
 </div>
 @endif

 </div>

 {{-- Right column: Order summary --}}
 <div class="space-y-6">

 {{-- Order Summary --}}
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-neutral-900 mb-4">Resumo do Pedido</h3>

 <div class="space-y-2 mb-4">
 <div class="flex justify-between text-sm">
 <span class="text-neutral-600">Subtotal:</span>
 <span class="text-neutral-900">R$ {{ number_format($order->subtotal, 2, ',', '.') }}</span>
 </div>
 @if($order->shipping_fee > 0)
 <div class="flex justify-between text-sm">
 <span class="text-neutral-600">Frete:</span>
 <span class="text-neutral-900">R$ {{ number_format($order->shipping_fee, 2, ',', '.') }}</span>
 </div>
 @endif
 @if($order->discount > 0)
 <div class="flex justify-between text-sm">
 <span class="text-neutral-600">Desconto:</span>
 <span class="text-green-600">-R$ {{ number_format($order->discount, 2, ',', '.') }}</span>
 </div>
 @endif
 </div>

 <div class="pt-4 border-t border-neutral-200">
 <div class="flex justify-between text-lg font-semibold">
 <span>Total:</span>
 <span class="text-primary-600">R$ {{ number_format($order->total, 2, ',', '.') }}</span>
 </div>
 </div>

 {{-- Payment status --}}
 <div class="mt-4 pt-4 border-t border-neutral-200">
 <p class="text-sm text-neutral-600 mb-1">Status do Pagamento:</p>
 <span class="badge badge-{{ $order->status }}">
 {{ $order->status_label }}
 </span>
 </div>
 </div>

 {{-- Seller Info --}}
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-neutral-900 mb-4">Vendedor</h3>
 <div class="flex items-center gap-3 mb-4">
 @if($order->seller->hasMedia('seller_logo'))
 <img src="{{ $order->seller->getFirstMedia('seller_logo')->getUrl('thumb') }}"
 alt="{{ $order->seller->store_name }}"
 loading="lazy"
 decoding="async"
 class="w-12 h-12 object-cover rounded-full border-2 border-neutral-200">
 @else
 <div class="w-12 h-12 bg-neutral-200 rounded-full flex items-center justify-center">
 <svg class="w-6 h-6 text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
 </svg>
 </div>
 @endif
 <div>
 <p class="font-medium text-neutral-900">{{ $order->seller->store_name }}</p>
 <p class="text-sm text-neutral-500">Vendedor</p>
 </div>
 </div>
 <a href="{{ route('seller.show', $order->seller->slug) }}"
 class="block w-full text-center px-4 py-2 bg-neutral-50 text-neutral-700 rounded-lg hover:bg-neutral-200 transition-colors">
 Ver Loja
 </a>
 </div>

 {{-- Tracking Code (if available) --}}
 @if($order->tracking_code)
 <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
 <h3 class="text-lg font-semibold text-blue-900 mb-2 flex items-center">
 <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
 </svg>
 Rastreamento
 </h3>
 <p class="text-sm text-blue-700 mb-3 font-mono">{{ $order->tracking_code }}</p>
 <a href="https://rastreamento.correios.com.br/app/index.php"
 target="_blank"
 class="block w-full text-center px-4 py-2 bg-blue-600 text-neutral-900 rounded-lg hover:bg-blue-700 transition-colors text-sm">
 Rastrear no Site dos Correios
 </a>
 </div>
 @endif

 {{-- Cancel Order Button (if applicable) --}}
 @can('cancel', $order)
 <div class="bg-white rounded-lg shadow p-6">
 <h3 class="text-lg font-semibold text-neutral-900 mb-3">Cancelar Pedido</h3>
 <p class="text-sm text-neutral-600 mb-4">
 Você pode cancelar este pedido. O estoque dos produtos será restaurado automaticamente.
 </p>
 <form method="POST" action="{{ route('customer.orders.cancel', $order) }}"
 onsubmit="return confirm('Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.')">
 @csrf
 <button type="submit"
 class="w-full px-4 py-2 bg-red-600 text-neutral-900 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center">
 <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 Cancelar Pedido
 </button>
 </form>
 </div>
 @endcan

 </div>

 </div>

 </div>
 </div>
</x-layouts.app>
