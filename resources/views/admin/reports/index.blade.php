<x-layouts.admin>
 <x-slot:header>Relatórios</x-slot>
 <x-slot:title>Relatórios - Admin</x-slot>

 <div class="space-y-6">
 <!-- Page Header -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h2 class="text-2xl font-bold mb-2">Central de Relatórios</h2>
 <p class="text-neutral-600">Acesse relatórios detalhados e exporte dados do marketplace.</p>
 </div>

 <!-- Report Cards Grid -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <!-- Sales Report -->
 <a href="{{ route('admin.reports.sales') }}" class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6 hover:shadow-md transition-shadow">
 <div class="flex items-center mb-4">
 <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
 <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
 </svg>
 </div>
 <h3 class="ml-4 text-lg font-medium">Relatório de Vendas</h3>
 </div>
 <p class="text-sm text-neutral-600 mb-4">
 Análise completa de vendas, faturamento e desempenho por período.
 </p>
 <ul class="text-sm text-neutral-500 space-y-1 mb-4">
 <li>• Receita total e por vendedor</li>
 <li>• Pedidos por status</li>
 <li>• Gráficos de vendas diárias</li>
 <li>• Exportação em CSV</li>
 </ul>
 <span class="text-primary-600 text-sm font-medium">Ver relatório →</span>
 </a>

 <!-- Products Report -->
 <a href="{{ route('admin.reports.products') }}" class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6 hover:shadow-md transition-shadow">
 <div class="flex items-center mb-4">
 <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
 <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
 </svg>
 </div>
 <h3 class="ml-4 text-lg font-medium">Relatório de Produtos</h3>
 </div>
 <p class="text-sm text-neutral-600 mb-4">
 Visão geral do catálogo, estoque e produtos por vendedor.
 </p>
 <ul class="text-sm text-neutral-500 space-y-1 mb-4">
 <li>• Total de produtos (publicados e rascunhos)</li>
 <li>• Alertas de estoque baixo</li>
 <li>• Produtos por vendedor e categoria</li>
 <li>• Filtros avançados</li>
 </ul>
 <span class="text-primary-600 text-sm font-medium">Ver relatório →</span>
 </a>

 <!-- Sellers Report -->
 <a href="{{ route('admin.reports.sellers') }}" class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6 hover:shadow-md transition-shadow">
 <div class="flex items-center mb-4">
 <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
 <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
 </svg>
 </div>
 <h3 class="ml-4 text-lg font-medium">Relatório de Vendedores</h3>
 </div>
 <p class="text-sm text-neutral-600 mb-4">
 Performance dos vendedores, produtos cadastrados e receita gerada.
 </p>
 <ul class="text-sm text-neutral-500 space-y-1 mb-4">
 <li>• Status dos vendedores (ativos, pendentes)</li>
 <li>• Total de produtos e estoque</li>
 <li>• Pedidos e receita por vendedor</li>
 <li>• Ranking de performance</li>
 </ul>
 <span class="text-primary-600 text-sm font-medium">Ver relatório →</span>
 </a>
 </div>

 <!-- Quick Stats -->
 <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
 <h3 class="text-lg font-medium mb-4">Estatísticas Rápidas</h3>
 <p class="text-sm text-neutral-500 mb-4">Para análises detalhadas, acesse os relatórios específicos acima.</p>
 <div class="flex gap-4">
 <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Ver Dashboard Principal</a>
 <a href="{{ route('admin.reports.sales') }}" class="btn-primary">Acessar Relatórios de Vendas</a>
 </div>
 </div>
 </div>
</x-layouts.admin>
