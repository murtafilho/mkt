<x-layouts.admin>
 <x-slot:header>Gerenciar Pedidos</x-slot>
 <x-slot:title>Pedidos - Admin</x-slot>

 <div class="space-y-6">
 <!-- Filters -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <form method="GET" class="space-y-4">
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
 <!-- Search -->
 <div>
 <label for="search" class="block text-sm font-medium mb-2">Buscar</label>
 <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nº pedido, cliente, email..." class="w-full">
 </div>

 <!-- Status Filter -->
 <div>
 <label for="status" class="block text-sm font-medium mb-2">Status</label>
 <select name="status" id="status" class="w-full">
 <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Todos ({{ $statusCounts['all'] }})</option>
 <option value="awaiting_payment" {{ request('status') === 'awaiting_payment' ? 'selected' : '' }}>Aguardando Pagamento ({{ $statusCounts['awaiting_payment'] }})</option>
 <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Pago ({{ $statusCounts['paid'] }})</option>
 <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparando ({{ $statusCounts['preparing'] }})</option>
 <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Enviado ({{ $statusCounts['shipped'] }})</option>
 <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregue ({{ $statusCounts['delivered'] }})</option>
 <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado ({{ $statusCounts['cancelled'] }})</option>
 </select>
 </div>

 <!-- Seller Filter -->
 <div>
 <label for="seller_id" class="block text-sm font-medium mb-2">Vendedor</label>
 <select name="seller_id" id="seller_id" class="w-full">
 <option value="">Todos os vendedores</option>
 @foreach($sellers as $seller)
 <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
 {{ $seller->store_name }}
 </option>
 @endforeach
 </select>
 </div>

 <!-- Date From -->
 <div>
 <label for="date_from" class="block text-sm font-medium mb-2">Data Início</label>
 <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full">
 </div>

 <!-- Date To -->
 <div>
 <label for="date_to" class="block text-sm font-medium mb-2">Data Fim</label>
 <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full">
 </div>
 </div>

 <div class="flex justify-between items-center pt-4 border-t">
 <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Limpar Filtros</a>
 <button type="submit" class="btn-primary">Filtrar</button>
 </div>
 </form>
 </div>

 <!-- Filter Chips -->
 @php
 $filterChips = [];

 if (request('status') && request('status') !== 'all') {
 $statusLabels = [
 'awaiting_payment' => 'Aguardando Pagamento',
 'paid' => 'Pago',
 'preparing' => 'Preparando',
 'shipped' => 'Enviado',
 'delivered' => 'Entregue',
 'cancelled' => 'Cancelado',
 ];
 $filterChips[] = [
 'label' => 'Status',
 'value' => request('status'),
 'display' => $statusLabels[request('status')] ?? request('status'),
 'removeUrl' => request()->fullUrlWithQuery(['status' => null]),
 ];
 }

 if (request('seller_id')) {
 $seller = $sellers->firstWhere('id', request('seller_id'));
 $filterChips[] = [
 'label' => 'Vendedor',
 'value' => request('seller_id'),
 'display' => $seller->store_name ?? 'N/A',
 'removeUrl' => request()->fullUrlWithQuery(['seller_id' => null]),
 ];
 }

 if (request('search')) {
 $filterChips[] = [
 'label' => 'Busca',
 'value' => request('search'),
 'display' => '"' . request('search') . '"',
 'removeUrl' => request()->fullUrlWithQuery(['search' => null]),
 ];
 }

 if (request('date_from') || request('date_to')) {
 $filterChips[] = [
 'label' => 'Período',
 'value' => 'date_range',
 'display' => (request('date_from') ?: '...') . ' até ' . (request('date_to') ?: '...'),
 'removeUrl' => request()->fullUrlWithQuery(['date_from' => null, 'date_to' => null]),
 ];
 }
 @endphp

 <x-filter-chips :filters="$filterChips" />

 <!-- Orders List -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg overflow-hidden">
 <div class="px-6 py-4 border-b">
 <h3 class="text-lg font-medium">
 Pedidos
 <span class="text-sm font-normal text-neutral-500">
 ({{ $orders->total() }} {{ $orders->total() === 1 ? 'pedido' : 'pedidos' }})
 </span>
 </h3>
 </div>

 @if($orders->count() > 0)
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-neutral-200">
 <thead class="bg-neutral-50">
 <tr>
 <x-sortable-th column="order_number" label="Pedido" :current-sort="$sortField" :current-direction="$sortDirection" />
 <x-sortable-th column="customer_name" label="Cliente" :current-sort="$sortField" :current-direction="$sortDirection" />
 <x-sortable-th column="seller_name" label="Vendedor" :current-sort="$sortField" :current-direction="$sortDirection" />
 <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Produtos</th>
 <x-sortable-th column="total" label="Valor" :current-sort="$sortField" :current-direction="$sortDirection" class="text-right" />
 <x-sortable-th column="status" label="Status" :current-sort="$sortField" :current-direction="$sortDirection" />
 <x-sortable-th column="created_at" label="Data" :current-sort="$sortField" :current-direction="$sortDirection" />
 <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 uppercase tracking-wider">Ações</th>
 </tr>
 </thead>
 <tbody class="bg-white divide-y divide-neutral-200">
 @foreach($orders as $order)
 <tr class="hover:bg-neutral-50">
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-medium">{{ $order->order_number }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm">{{ $order->user->name }}</div>
 <div class="text-xs text-neutral-500">{{ $order->user->email }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm">{{ $order->seller->store_name }}</div>
 </td>
 <td class="px-6 py-4">
 <div class="flex -space-x-2">
 @foreach($order->items->take(3) as $item)
 @if($item->product && $item->product->hasMedia('product_images'))
 <img src="{{ $item->product->getFirstMediaUrl('product_images', 'thumb') }}"
 alt="{{ $item->product->name }}"
 loading="lazy"
 decoding="async"
 class="h-8 w-8 rounded-full border-2 border-white object-cover"
 title="{{ $item->product->name }}">
 @endif
 @endforeach
 @if($order->items->count() > 3)
 <div class="h-8 w-8 rounded-full border-2 border-white bg-neutral-200 flex items-center justify-center text-xs">
 +{{ $order->items->count() - 3 }}
 </div>
 @endif
 </div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <div class="text-sm font-semibold">R$ {{ number_format($order->total, 2, ',', '.') }}</div>
 </td>
 <td class="px-6 py-4 whitespace-nowrap">
 <span class="badge badge-{{ $order->status }}">{{ $order->status_label }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500">
 {{ $order->created_at->format('d/m/Y H:i') }}
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
 <a href="{{ route('admin.orders.show', $order) }}" class="text-primary-600 hover:text-primary-900">
 Ver Detalhes
 </a>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>

 <!-- Pagination -->
 <div class="px-6 py-4 border-t">
 <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
 <div class="flex items-center space-x-2">
 <label for="per_page" class="text-sm text-neutral-600">Items por página:</label>
 <select name="per_page" id="per_page"
 onchange="window.location='{{ request()->fullUrlWithQuery(['per_page' => '']) }}' + this.value"
 class="rounded-md border-neutral-300 text-sm focus:border-primary-500 focus:ring-primary-500">
 <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
 <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
 <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
 </select>
 <span class="text-sm text-neutral-600">
 Mostrando {{ $orders->firstItem() }} a {{ $orders->lastItem() }} de {{ $orders->total() }} resultados
 </span>
 </div>

 <div>
 {{ $orders->links() }}
 </div>
 </div>
 </div>
 @else
 <div class="px-6 py-12 text-center">
 <svg class="mx-auto h-12 w-12 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
 </svg>
 <p class="mt-4 text-sm text-neutral-500">
 @if(request()->hasAny(['status', 'seller_id', 'search', 'date_from', 'date_to']))
 Nenhum pedido encontrado com os filtros aplicados.
 @else
 Nenhum pedido cadastrado ainda.
 @endif
 </p>
 </div>
 @endif
 </div>
 </div>
</x-layouts.admin>
