<x-layouts.admin>
    <x-slot:header>Relatório de Produtos</x-slot>
    <x-slot:title>Relatório de Produtos - Admin</x-slot>

    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-primary-600 hover:text-primary-700">← Voltar para Relatórios</a>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="seller_id" class="block text-sm font-medium mb-2">Vendedor</label>
                        <select name="seller_id" id="seller_id" class="w-full">
                            <option value="">Todos</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>{{ $seller->store_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium mb-2">Status</label>
                        <select name="status" id="status" class="w-full">
                            <option value="">Todos</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicados</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunhos</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary">Filtrar</button>
                        <a href="{{ route('admin.reports.products') }}" class="btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Filter Chips -->
        @php
        $filterChips = [];

        if (request('seller_id')) {
            $seller = $sellers->firstWhere('id', request('seller_id'));
            $filterChips[] = [
                'label' => 'Vendedor',
                'value' => request('seller_id'),
                'display' => $seller->store_name ?? 'N/A',
                'removeUrl' => request()->fullUrlWithQuery(['seller_id' => null]),
            ];
        }

        if (request('status')) {
            $statusLabels = [
                'published' => 'Publicados',
                'draft' => 'Rascunhos',
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
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Total de Produtos</h3>
                <p class="text-3xl font-bold">{{ $totalProducts }}</p>
            </div>
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Publicados</h3>
                <p class="text-3xl font-bold text-success-600">{{ $publishedProducts }}</p>
            </div>
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Rascunhos</h3>
                <p class="text-3xl font-bold text-neutral-600">{{ $draftProducts }}</p>
            </div>
            <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg p-6">
                <h3 class="text-sm font-medium text-neutral-500 mb-2">Estoque Baixo</h3>
                <p class="text-3xl font-bold text-danger-600">{{ $lowStockProducts }}</p>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white shadow-sm border-b border-neutral-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium">
                    Produtos
                    <span class="text-sm font-normal text-neutral-500">
                        ({{ $products->total() }} {{ $products->total() === 1 ? 'produto' : 'produtos' }})
                    </span>
                </h3>
            </div>
            @if($products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-neutral-50">
                            <tr>
                                <x-sortable-th column="name" label="Produto" :current-sort="$sortField" :current-direction="$sortDirection" />
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Vendedor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Categoria</th>
                                <x-sortable-th column="price" label="Preço" :current-sort="$sortField" :current-direction="$sortDirection" />
                                <x-sortable-th column="stock" label="Estoque" :current-sort="$sortField" :current-direction="$sortDirection" />
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            @foreach($products as $product)
                                <tr class="hover:bg-neutral-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium">{{ $product->name }}</div>
                                        <div class="text-xs text-neutral-500">SKU: {{ $product->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $product->seller->store_name }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $product->category->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold">R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="{{ $product->stock <= 10 ? 'text-danger-600 font-semibold' : '' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="badge badge-{{ $product->status === 'published' ? 'success' : 'gray' }}">
                                            {{ $product->status === 'published' ? 'Publicado' : 'Rascunho' }}
                                        </span>
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
                                Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} de {{ $products->total() }} resultados
                            </span>
                        </div>

                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="mt-4 text-sm text-neutral-500">
                        @if(request()->hasAny(['seller_id', 'status']))
                            Nenhum produto encontrado com os filtros aplicados.
                        @else
                            Nenhum produto cadastrado.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
