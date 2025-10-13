<x-layouts.admin>
 <x-slot:header>Pedido #{{ $order->order_number }}</x-slot>
 <x-slot:title>Pedido #{{ $order->order_number }} - Admin</x-slot>

 <div class="space-y-6">
 <!-- Back Button -->
 <div>
 <a href="{{ route('admin.orders.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
 ← Voltar para lista de pedidos
 </a>
 </div>

 <!-- Flash Messages -->
 @if(session('success'))
 <div class="bg-success-100 border border-success-400 text-success-700 px-4 py-3 rounded">
 {{ session('success') }}
 </div>
 @endif

 @if(session('error'))
 <div class="bg-danger-100 border border-danger-400 text-danger-700 px-4 py-3 rounded">
 {{ session('error') }}
 </div>
 @endif

 <!-- Order Header -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <div>
 <h2 class="text-2xl font-bold">Pedido #{{ $order->order_number }}</h2>
 <p class="text-sm text-neutral-500 mt-1">
 Criado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}
 </p>
 </div>
 <span class="badge badge-{{ $order->status }} text-lg px-4 py-2">{{ $order->status_label }}</span>
 </div>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <!-- Left Column: Details -->
 <div class="lg:col-span-2 space-y-6">
 <!-- Customer Information -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Informações do Cliente</h3>
 <dl class="space-y-2 text-sm">
 <div class="flex justify-between">
 <dt class="font-medium">Nome:</dt>
 <dd>{{ $order->user->name }}</dd>
 </div>
 <div class="flex justify-between">
 <dt class="font-medium">Email:</dt>
 <dd>{{ $order->user->email }}</dd>
 </div>
 @if($order->user->phone)
 <div class="flex justify-between">
 <dt class="font-medium">Telefone:</dt>
 <dd>{{ $order->user->phone }}</dd>
 </div>
 @endif
 </dl>
 </div>

 <!-- Seller Information -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Informações do Vendedor</h3>
 <div class="flex items-center mb-3">
 @if($order->seller->hasMedia('seller_logo'))
 <img src="{{ $order->seller->getFirstMediaUrl('seller_logo', 'thumb') }}"
 alt="{{ $order->seller->store_name }}"
 loading="lazy"
 decoding="async"
 class="h-10 w-10 rounded-full object-cover">
 @else
 <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
 <span class="text-sm font-medium">{{ substr($order->seller->store_name, 0, 2) }}</span>
 </div>
 @endif
 <div class="ml-3">
 <a href="{{ route('admin.sellers.show', $order->seller) }}" class="font-medium hover:text-primary-600">
 {{ $order->seller->store_name }}
 </a>
 <p class="text-sm text-neutral-500">{{ $order->seller->user->email }}</p>
 </div>
 </div>
 </div>

 <!-- Delivery Address -->
 @if($order->address)
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Endereço de Entrega</h3>
 <div class="text-sm space-y-1">
 <p>{{ $order->address->street }}, {{ $order->address->number }}</p>
 @if($order->address->complement)
 <p>{{ $order->address->complement }}</p>
 @endif
 <p>{{ $order->address->neighborhood }}</p>
 <p>{{ $order->address->city }} - {{ $order->address->state }}</p>
 <p class="font-medium">CEP: {{ $order->address->postal_code }}</p>
 </div>
 </div>
 @else
 <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
 <p class="text-sm text-yellow-800">
 <strong>⚠️ Atenção:</strong> Endereço de entrega não disponível para este pedido (pedido criado antes da implementação do sistema de endereços).
 </p>
 </div>
 @endif

 <!-- Order Items -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Itens do Pedido</h3>
 <div class="space-y-4">
 @foreach($order->items as $item)
 <div class="flex items-center gap-4 pb-4 border-b last:border-b-0">
 @if($item->product && $item->product->hasMedia('product_images'))
 <img src="{{ $item->product->getFirstMediaUrl('product_images', 'thumb') }}"
 alt="{{ $item->product_name }}"
 loading="lazy"
 decoding="async"
 class="h-16 w-16 rounded object-cover">
 @else
 <div class="h-16 w-16 rounded bg-neutral-200 flex items-center justify-center">
 <svg class="h-8 w-8 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 @endif
 <div class="flex-1">
 <h4 class="font-medium">{{ $item->product_name }}</h4>
 <p class="text-sm text-neutral-500">Quantidade: {{ $item->quantity }}</p>
 <p class="text-sm text-neutral-500">Preço unitário: R$ {{ number_format($item->unit_price, 2, ',', '.') }}</p>
 </div>
 <div class="text-right">
 <p class="font-semibold">R$ {{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }}</p>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 <!-- Order Timeline -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Histórico do Pedido</h3>
 <x-order-timeline :order="$order" />
 </div>
 </div>

 <!-- Right Column: Actions -->
 <div class="space-y-6">
 <!-- Order Summary -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Resumo do Pedido</h3>
 <dl class="space-y-2 text-sm">
 <div class="flex justify-between">
 <dt>Subtotal:</dt>
 <dd>R$ {{ number_format($order->subtotal, 2, ',', '.') }}</dd>
 </div>
 <div class="flex justify-between">
 <dt>Frete:</dt>
 <dd>R$ {{ number_format($order->shipping_fee, 2, ',', '.') }}</dd>
 </div>
 @if($order->discount > 0)
 <div class="flex justify-between text-success-600">
 <dt>Desconto:</dt>
 <dd>- R$ {{ number_format($order->discount, 2, ',', '.') }}</dd>
 </div>
 @endif
 <div class="flex justify-between pt-2 border-t font-semibold text-base">
 <dt>Total:</dt>
 <dd>R$ {{ number_format($order->total, 2, ',', '.') }}</dd>
 </div>
 </dl>

 @if($order->tracking_code)
 <div class="mt-4 pt-4 border-t">
 <dt class="text-sm font-medium mb-1">Código de Rastreio:</dt>
 <dd class="text-sm font-mono bg-neutral-50 px-3 py-2 rounded">
 {{ $order->tracking_code }}
 </dd>
 </div>
 @endif

 @if($order->paid_at)
 <div class="mt-4 pt-4 border-t">
 <dt class="text-sm font-medium">Pago em:</dt>
 <dd class="text-sm">{{ $order->paid_at->format('d/m/Y H:i') }}</dd>
 </div>
 @endif

 @if($order->notes)
 <div class="mt-4 pt-4 border-t">
 <dt class="text-sm font-medium mb-1">Observações:</dt>
 <dd class="text-sm text-neutral-600">{{ $order->notes }}</dd>
 </div>
 @endif
 </div>

 <!-- Update Status Form -->
 @can('update', Order::class)
 @if($order->status !== 'cancelled' && $order->status !== 'delivered')
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Atualizar Status</h3>
 <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
 @csrf
 @method('PATCH')

 <div class="space-y-4">
 <div>
 <label for="status" class="block text-sm font-medium mb-2">Novo Status</label>
 <select name="status" id="status" class="w-full" required>
 <option value="">Selecione um status</option>
 <option value="awaiting_payment">Aguardando Pagamento</option>
 <option value="paid">Pago</option>
 <option value="preparing">Preparando</option>
 <option value="shipped">Enviado</option>
 <option value="delivered">Entregue</option>
 </select>
 </div>

 <div id="tracking-code-field" style="display: none;">
 <label for="tracking_code" class="block text-sm font-medium mb-2">Código de Rastreio</label>
 <input type="text" name="tracking_code" id="tracking_code" class="w-full" placeholder="Ex: BR123456789BR">
 </div>

 <div>
 <label for="note" class="block text-sm font-medium mb-2">Observação (opcional)</label>
 <textarea name="note" id="note" rows="3" class="w-full" placeholder="Adicione uma observação sobre esta atualização..."></textarea>
 </div>

 <button type="submit" class="btn-primary w-full">Atualizar Status</button>
 </div>
 </form>
 </div>
 @endif

 <!-- Cancel Order -->
 @if(in_array($order->status, ['awaiting_payment', 'paid', 'preparing']))
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4 text-danger-600">Cancelar Pedido</h3>
 <p class="text-sm text-neutral-600 mb-4">
 Atenção: Esta ação irá cancelar o pedido e restaurar o estoque dos produtos.
 </p>
 <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.');">
 @csrf
 @method('DELETE')
 <button type="submit" class="btn-danger w-full">Cancelar Pedido</button>
 </form>
 </div>
 @endif
 @endcan
 </div>
 </div>
 </div>

 @push('scripts')
 <script>
 // Show/hide tracking code field based on status selection
 document.getElementById('status').addEventListener('change', function() {
 const trackingCodeField = document.getElementById('tracking-code-field');
 if (this.value === 'shipped') {
 trackingCodeField.style.display = 'block';
 document.getElementById('tracking_code').required = true;
 } else {
 trackingCodeField.style.display = 'none';
 document.getElementById('tracking_code').required = false;
 }
 });
 </script>
 @endpush
</x-layouts.admin>
