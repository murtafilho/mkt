<x-layouts.admin>
    <x-slot:header>Relatório de Vendedores</x-slot>
    <x-slot:title>Relatório de Vendedores - Admin</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-primary-600 hover:text-primary-700">← Voltar para Relatórios</a>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium mb-2">Status</label>
                        <select name="status" id="status" class="w-full">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspenso</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary">Filtrar</button>
                        <a href="{{ route('admin.reports.sellers') }}" class="btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filter Chips -->
        @php
        $filterChips = [];

        if (request('status')) {
            $statusLabels = [
                'pending' => 'Pendente',
                'active' => 'Ativo',
                'suspended' => 'Suspenso',
            ];
            $filterChips[] = [
                'label' => 'Status',
                'value' => request('status'),
                'display' => $statusLabels[request('status')] ?? request('status'),
                'removeUrl' => request()->fullUrlWithQuery(['status' => null]),
            ];
        }
        @endphp

        <x-filter-chips :filters="$filterChips" />

        <!-- Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Total de Vendedores</h3>
                <p class="text-3xl font-bold">{{ $totalSellers }}</p>
            </div>
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Ativos</h3>
                <p class="text-3xl font-bold text-success-600">{{ $activeSellers }}</p>
            </div>
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Pendentes</h3>
                <p class="text-3xl font-bold text-warning-600">{{ $pendingSellers }}</p>
            </div>
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Suspensos</h3>
                <p class="text-3xl font-bold text-danger-600">{{ $suspendedSellers }}</p>
            </div>
        </div>

        <!-- Sellers Table -->
        <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium">
                    Performance dos Vendedores
                    <span class="text-sm font-normal text-neutral-500">
                        ({{ $sellers->total() }} {{ $sellers->total() === 1 ? 'vendedor' : 'vendedores' }})
                    </span>
                </h3>
            </div>
            @if($sellers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-neutral-50">
                            <tr>
                                <x-sortable-th column="store_name" label="Vendedor" :current-sort="$sortField" :current-direction="$sortDirection" />
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                                <x-sortable-th column="products_count" label="Produtos" :current-sort="$sortField" :current-direction="$sortDirection" />
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Estoque</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Pedidos</th>
                                <x-sortable-th column="revenue" label="Receita" :current-sort="$sortField" :current-direction="$sortDirection" />
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($sellers as $seller)
                                <tr class="hover:bg-neutral-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium">
                                            <a href="{{ route('admin.sellers.show', $seller) }}" class="hover:text-primary-600">
                                                {{ $seller->store_name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-neutral-500">{{ $seller->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $seller->status === 'active',
                                            'bg-yellow-100 text-yellow-800' => $seller->status === 'pending',
                                            'bg-red-100 text-red-800' => $seller->status === 'suspended',
                                        ])>
                                            @if($seller->status === 'active')
                                                Ativo
                                            @elseif($seller->status === 'pending')
                                                Pendente
                                            @else
                                                Suspenso
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $seller->products_count ?? 0 }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $seller->products_sum_stock ?? 0 }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold">{{ $seller->total_orders ?? 0 }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold">R$ {{ number_format($seller->total_revenue ?? 0, 2, ',', '.') }}</td>
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
                                Mostrando {{ $sellers->firstItem() }} a {{ $sellers->lastItem() }} de {{ $sellers->total() }} resultados
                            </span>
                        </div>

                        <div>
                            {{ $sellers->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-4 text-sm text-neutral-500">
                        @if(request()->hasAny(['status']))
                            Nenhum vendedor encontrado com os filtros aplicados.
                        @else
                            Nenhum vendedor cadastrado.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
