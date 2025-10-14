@extends('layouts.admin')

@section('title', 'Vendedores - Admin')

@section('header', 'Gerenciar Vendedores')

@section('page-content')
<div class="container-fluid" x-data="sellersTable()">
    {{-- Filters & Actions Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                {{-- Quick Search (Alpine.js - Instant Filter) --}}
                <div class="col-md-4">
                    <label for="quickSearch" class="form-label">
                        <i class="bi bi-search me-1"></i>
                        Busca Rápida
                    </label>
                    <input 
                        type="text" 
                        id="quickSearch" 
                        x-model="searchTerm"
                        @input="filterTable()"
                        placeholder="Buscar em todos os campos..." 
                        class="form-control">
                    <small class="text-muted" x-show="searchTerm" x-text="`${filteredCount} resultado(s) encontrado(s)`"></small>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select 
                        id="statusFilter" 
                        x-model="statusFilter"
                        @change="filterTable()"
                        class="form-select">
                        <option value="">Todos</option>
                        <option value="pending">Pendente</option>
                        <option value="active">Ativo</option>
                        <option value="suspended">Suspenso</option>
                    </select>
                </div>

                {{-- Items Per Page --}}
                <div class="col-md-2">
                    <label for="perPage" class="form-label">Mostrar</label>
                    <select 
                        id="perPage" 
                        x-model="perPage"
                        @change="updatePagination()"
                        class="form-select">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button 
                            type="button" 
                            @click="clearFilters()" 
                            class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-x-circle me-1"></i>
                            Limpar
                        </button>
                        <button 
                            type="button" 
                            @click="exportCSV()" 
                            class="btn btn-success">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            {{-- Bulk Actions (when items selected) --}}
            <div x-show="selectedItems.length > 0" class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        <span x-text="selectedItems.length"></span> seller(s) selecionado(s)
                    </span>
                    <div class="btn-group" role="group">
                        <button 
                            type="button" 
                            @click="bulkApprove()" 
                            class="btn btn-sm btn-success">
                            <i class="bi bi-check-lg me-1"></i>
                            Aprovar Selecionados
                        </button>
                        <button 
                            type="button" 
                            @click="bulkSuspend()" 
                            class="btn btn-sm btn-danger">
                            <i class="bi bi-pause-circle me-1"></i>
                            Suspender Selecionados
                        </button>
                        <button 
                            type="button" 
                            @click="clearSelection()" 
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x me-1"></i>
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Filters Display --}}
    <div x-show="searchTerm || statusFilter" class="mb-3">
        <div class="d-flex gap-2 flex-wrap">
            <span x-show="searchTerm" class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                Busca: "<span x-text="searchTerm"></span>"
                <button type="button" @click="searchTerm = ''; filterTable()" class="btn-close btn-close-sm ms-2"></button>
            </span>
            <span x-show="statusFilter" class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                Status: <span x-text="statusFilter"></span>
                <button type="button" @click="statusFilter = ''; filterTable()" class="btn-close btn-close-sm ms-2"></button>
            </span>
        </div>
    </div>

    {{-- Sellers Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-people me-2"></i>
                    Vendedores
                    <span class="badge bg-secondary ms-2" x-text="`${filteredCount} / ${totalCount}`"></span>
                </h5>
                <div class="form-check">
                    <input 
                        type="checkbox" 
                        id="selectAll"
                        @change="toggleSelectAll($event.target.checked)"
                        :checked="selectedItems.length === filteredSellers.length && filteredSellers.length > 0"
                        class="form-check-input">
                    <label for="selectAll" class="form-check-label small text-muted">
                        Selecionar todos
                    </label>
                </div>
            </div>
        </div>

        @if($sellers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <span class="visually-hidden">Seleção</span>
                            </th>
                            <th scope="col" @click="sortBy('store_name')" class="sortable-th">
                                <div class="d-flex align-items-center gap-2">
                                    Loja
                                    <i :class="getSortIcon('store_name')"></i>
                                </div>
                            </th>
                            <th scope="col">Proprietário</th>
                            <th scope="col">Documento</th>
                            <th scope="col" @click="sortBy('status')" class="sortable-th">
                                <div class="d-flex align-items-center gap-2">
                                    Status
                                    <i :class="getSortIcon('status')"></i>
                                </div>
                            </th>
                            <th scope="col" @click="sortBy('created_at')" class="sortable-th">
                                <div class="d-flex align-items-center gap-2">
                                    Cadastro
                                    <i :class="getSortIcon('created_at')"></i>
                                </div>
                            </th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="seller in paginatedSellers" :key="seller.id">
                            <tr>
                                <td>
                                    <input 
                                        type="checkbox" 
                                        :value="seller.id"
                                        @change="toggleSelection(seller.id)"
                                        :checked="selectedItems.includes(seller.id)"
                                        class="form-check-input">
                                </td>
                                <td>
                                    <div class="fw-medium" x-text="seller.store_name"></div>
                                </td>
                                <td>
                                    <div class="fw-medium" x-text="seller.user_name"></div>
                                    <small class="text-muted" x-text="seller.user_email"></small>
                                </td>
                                <td>
                                    <span class="text-muted" x-text="seller.document_number"></span>
                                </td>
                                <td>
                                    <span x-html="getStatusBadge(seller.status)"></span>
                                </td>
                                <td>
                                    <small class="text-muted" x-text="seller.created_at"></small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a :href="`{{ route('admin.sellers.index') }}/${seller.id}`" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button 
                                            type="button"
                                            @click="performAction(seller.id, seller.status)"
                                            x-html="getActionButton(seller.status)"
                                            class="btn btn-sm"
                                            :class="getActionClass(seller.status)">
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Custom Pagination --}}
            <div class="card-footer bg-transparent border-0">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div>
                        <small class="text-muted">
                            Mostrando 
                            <span x-text="Math.min((currentPage - 1) * perPage + 1, filteredCount)"></span>
                            a 
                            <span x-text="Math.min(currentPage * perPage, filteredCount)"></span>
                            de 
                            <span x-text="filteredCount"></span>
                            resultado(s)
                        </small>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item" :class="currentPage === 1 ? 'disabled' : ''">
                                <button class="page-link" @click="currentPage = 1" :disabled="currentPage === 1">
                                    <i class="bi bi-chevron-double-left"></i>
                                </button>
                            </li>
                            <li class="page-item" :class="currentPage === 1 ? 'disabled' : ''">
                                <button class="page-link" @click="currentPage--" :disabled="currentPage === 1">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                            </li>
                            <template x-for="page in visiblePages" :key="page">
                                <li class="page-item" :class="currentPage === page ? 'active' : ''">
                                    <button class="page-link" @click="currentPage = page" x-text="page"></button>
                                </li>
                            </template>
                            <li class="page-item" :class="currentPage === totalPages ? 'disabled' : ''">
                                <button class="page-link" @click="currentPage++" :disabled="currentPage === totalPages">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </li>
                            <li class="page-item" :class="currentPage === totalPages ? 'disabled' : ''">
                                <button class="page-link" @click="currentPage = totalPages" :disabled="currentPage === totalPages">
                                    <i class="bi bi-chevron-double-right"></i>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="bi bi-people display-1 text-muted mb-3"></i>
                <h5 class="card-title">Nenhum vendedor cadastrado ainda.</h5>
                <p class="card-text text-muted">
                    Os vendedores cadastrados aparecerão aqui.
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function sellersTable() {
    return {
        // Data
        allSellers: {!! json_encode($sellers->map(function($seller) {
            return [
                'id' => $seller->id,
                'store_name' => $seller->store_name,
                'user_name' => $seller->user->name,
                'user_email' => $seller->user->email,
                'document_number' => $seller->document_number,
                'status' => $seller->status,
                'created_at' => $seller->created_at->format('d/m/Y'),
            ];
        })) !!},
        
        // Filters
        searchTerm: '',
        statusFilter: '',
        
        // Sorting
        sortField: '{{ $sortField ?? '' }}',
        sortDirection: '{{ $sortDirection ?? 'asc' }}',
        
        // Pagination
        currentPage: 1,
        perPage: 5,
        
        // Selection
        selectedItems: [],
        
        // Computed
        get filteredSellers() {
            let filtered = this.allSellers;
            
            // Search filter
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(seller => 
                    seller.store_name.toLowerCase().includes(term) ||
                    seller.user_name.toLowerCase().includes(term) ||
                    seller.user_email.toLowerCase().includes(term) ||
                    seller.document_number.toLowerCase().includes(term)
                );
            }
            
            // Status filter
            if (this.statusFilter) {
                filtered = filtered.filter(seller => seller.status === this.statusFilter);
            }
            
            // Sort
            if (this.sortField) {
                filtered.sort((a, b) => {
                    let aVal = a[this.sortField];
                    let bVal = b[this.sortField];
                    
                    if (this.sortDirection === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });
            }
            
            return filtered;
        },
        
        get filteredCount() {
            return this.filteredSellers.length;
        },
        
        get totalCount() {
            return this.allSellers.length;
        },
        
        get totalPages() {
            return Math.ceil(this.filteredCount / this.perPage);
        },
        
        get paginatedSellers() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredSellers.slice(start, end);
        },
        
        get visiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },
        
        // Methods
        filterTable() {
            this.currentPage = 1;
        },
        
        updatePagination() {
            this.currentPage = 1;
        },
        
        clearFilters() {
            this.searchTerm = '';
            this.statusFilter = '';
            this.filterTable();
        },
        
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.filterTable();
        },
        
        getSortIcon(field) {
            if (this.sortField !== field) {
                return 'bi bi-arrow-down-up text-muted opacity-50';
            }
            return this.sortDirection === 'asc' 
                ? 'bi bi-arrow-up text-primary' 
                : 'bi bi-arrow-down text-primary';
        },
        
        getStatusBadge(status) {
            const badges = {
                'active': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Ativo</span>',
                'pending': '<span class="badge bg-warning"><i class="bi bi-clock me-1"></i>Pendente</span>',
                'suspended': '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Suspenso</span>',
            };
            return badges[status] || '<span class="badge bg-secondary">Inativo</span>';
        },
        
        getActionButton(status) {
            const actions = {
                'pending': '<i class="bi bi-check-lg"></i>',
                'active': '<i class="bi bi-pause-circle"></i>',
                'suspended': '<i class="bi bi-play-circle"></i>',
            };
            return actions[status] || '';
        },
        
        getActionClass(status) {
            const classes = {
                'pending': 'btn-success',
                'active': 'btn-danger',
                'suspended': 'btn-success',
            };
            return classes[status] || 'btn-secondary';
        },
        
        async performAction(sellerId, status) {
            const actions = {
                'pending': { route: 'approve', message: 'Aprovar este vendedor?' },
                'active': { route: 'suspend', message: 'Suspender este vendedor? Todos os produtos serão despublicados.' },
                'suspended': { route: 'reactivate', message: 'Reativar este vendedor?' },
            };
            
            const action = actions[status];
            if (!action || !confirm(action.message)) return;
            
            try {
                const response = await fetch(`{{ route('admin.sellers.index') }}/${sellerId}/${action.route}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao processar ação. Tente novamente.');
                }
            } catch (error) {
                alert('Erro ao processar ação. Tente novamente.');
            }
        },
        
        // Selection
        toggleSelection(sellerId) {
            const index = this.selectedItems.indexOf(sellerId);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            } else {
                this.selectedItems.push(sellerId);
            }
        },
        
        toggleSelectAll(checked) {
            if (checked) {
                this.selectedItems = this.paginatedSellers.map(s => s.id);
            } else {
                this.selectedItems = [];
            }
        },
        
        clearSelection() {
            this.selectedItems = [];
        },
        
        // Bulk Actions
        async bulkApprove() {
            if (!confirm(`Aprovar ${this.selectedItems.length} vendedor(es)?`)) return;

            try {
                const response = await fetch('{{ route('admin.sellers.bulk.approve') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        seller_ids: this.selectedItems
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao aprovar vendedores. Tente novamente.');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao processar ação. Tente novamente.');
            }
        },

        async bulkSuspend() {
            if (!confirm(`Suspender ${this.selectedItems.length} vendedor(es)? Todos os produtos serão despublicados.`)) return;

            try {
                const response = await fetch('{{ route('admin.sellers.bulk.suspend') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        seller_ids: this.selectedItems
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao suspender vendedores. Tente novamente.');
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao processar ação. Tente novamente.');
            }
        },
        
        // Export
        exportCSV() {
            const headers = ['Loja', 'Proprietário', 'Email', 'Documento', 'Status', 'Cadastro'];
            const rows = this.filteredSellers.map(s => [
                s.store_name,
                s.user_name,
                s.user_email,
                s.document_number,
                s.status,
                s.created_at,
            ]);
            
            let csv = headers.join(',') + '\n';
            rows.forEach(row => {
                csv += row.map(cell => `"${cell}"`).join(',') + '\n';
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `vendedores_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();
        },
    }
}
</script>
@endpush
@endsection
