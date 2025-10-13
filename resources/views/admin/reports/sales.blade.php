<x-layouts.admin>
 <x-slot:header>Relatório de Vendas</x-slot>
 <x-slot:title>Relatório de Vendas - Admin</x-slot>

 <div class="space-y-6">
 <!-- Back Button -->
 <div>
 <a href="{{ route('admin.reports.index') }}" class="text-sm text-primary-600 hover:text-primary-700">
 ← Voltar para Relatórios
 </a>
 </div>

 <!-- Filters -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <form method="GET" class="space-y-4">
 <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
 <div>
 <label for="date_from" class="block text-sm font-medium mb-2">Data Início</label>
 <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full">
 </div>

 <div>
 <label for="date_to" class="block text-sm font-medium mb-2">Data Fim</label>
 <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full">
 </div>

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

 <div class="flex items-end">
 <button type="submit" class="btn-primary w-full">Filtrar</button>
 </div>
 </div>

 <div class="flex justify-between items-center pt-4 border-t">
 <a href="{{ route('admin.reports.sales') }}" class="btn-secondary">Limpar Filtros</a>
 <a href="{{ route('admin.reports.sales.export', request()->query()) }}" class="btn-primary">
 Exportar CSV
 </a>
 </div>
 </form>
 </div>

 <!-- Metrics Cards -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-sm font-medium text-neutral-500 mb-2">Total de Pedidos</h3>
 <p class="text-3xl font-bold">{{ $totalOrders }}</p>
 </div>

 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-sm font-medium text-neutral-500 mb-2">Receita Total</h3>
 <p class="text-3xl font-bold">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
 </div>

 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-sm font-medium text-neutral-500 mb-2">Ticket Médio</h3>
 <p class="text-3xl font-bold">R$ {{ number_format($averageOrderValue, 2, ',', '.') }}</p>
 </div>
 </div>

 <!-- Orders by Status -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Pedidos por Status</h3>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-neutral-200">
 <thead>
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Status</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Quantidade</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Receita</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-neutral-200">
 @forelse($ordersByStatus as $item)
 <tr>
 <td class="px-6 py-4">
 <span class="badge badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
 </td>
 <td class="px-6 py-4 font-semibold">{{ $item->count }}</td>
 <td class="px-6 py-4 font-semibold">R$ {{ number_format($item->revenue, 2, ',', '.') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="3" class="px-6 py-4 text-center text-neutral-500">Nenhum dado disponível</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <!-- Daily Sales Chart -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Vendas Diárias</h3>
 <canvas id="dailySalesChart" height="80"></canvas>
 </div>

 <!-- Top Sellers -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Top 10 Vendedores</h3>
 <div class="overflow-x-auto">
 <table class="min-w-full divide-y divide-neutral-200">
 <thead>
 <tr>
 <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Vendedor</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Pedidos</th>
 <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Receita</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-neutral-200">
 @forelse($topSellers as $item)
 <tr>
 <td class="px-6 py-4">{{ $item->seller->store_name }}</td>
 <td class="px-6 py-4 font-semibold">{{ $item->order_count }}</td>
 <td class="px-6 py-4 font-semibold">R$ {{ number_format($item->revenue, 2, ',', '.') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="3" class="px-6 py-4 text-center text-neutral-500">Nenhum dado disponível</td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>

 @push('scripts')
 {{-- Chart.js is now imported via app.js (NPM) - no CDN needed --}}
 <script>
 const dailySalesData = @json($dailySales);

 const labels = dailySalesData.map(item => {
 const date = new Date(item.date);
 return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
 });

 const revenueData = dailySalesData.map(item => parseFloat(item.revenue));
 const orderData = dailySalesData.map(item => parseInt(item.order_count));

 const ctx = document.getElementById('dailySalesChart');
 new Chart(ctx, {
 type: 'line',
 data: {
 labels: labels,
 datasets: [
 {
 label: 'Receita (R$)',
 data: revenueData,
 borderColor: 'rgb(59, 130, 246)',
 backgroundColor: 'rgba(59, 130, 246, 0.1)',
 borderWidth: 2,
 yAxisID: 'y',
 },
 {
 label: 'Pedidos',
 data: orderData,
 borderColor: 'rgb(249, 115, 22)',
 backgroundColor: 'rgba(249, 115, 22, 0.1)',
 borderWidth: 2,
 yAxisID: 'y1',
 }
 ]
 },
 options: {
 responsive: true,
 interaction: {
 mode: 'index',
 intersect: false,
 },
 scales: {
 y: {
 type: 'linear',
 display: true,
 position: 'left',
 ticks: {
 callback: function(value) {
 return 'R$ ' + value.toLocaleString('pt-BR');
 }
 }
 },
 y1: {
 type: 'linear',
 display: true,
 position: 'right',
 grid: {
 drawOnChartArea: false,
 },
 },
 }
 }
 });
 </script>
 @endpush
</x-layouts.admin>
