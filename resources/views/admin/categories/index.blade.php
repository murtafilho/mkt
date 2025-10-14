@extends('layouts.admin')

@section('title', 'Categorias - Admin')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <span>Gerenciar Categorias</span>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>
            Nova Categoria
        </a>
    </div>
@endsection

@section('page-content')
<div class="container-fluid" x-data="categoriesTable()">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-x-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters & Actions Bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                {{-- Quick Search --}}
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
                        placeholder="Buscar nome ou slug..." 
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
                        <option value="1">Ativas</option>
                        <option value="0">Inativas</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div class="col-md-2">
                    <label for="typeFilter" class="form-label">Tipo</label>
                    <select 
                        id="typeFilter" 
                        x-model="typeFilter"
                        @change="filterTable()"
                        class="form-select">
                        <option value="">Todas</option>
                        <option value="root">Principais</option>
                        <option value="sub">Subcategorias</option>
                    </select>
                </div>

                {{-- Items Per Page --}}
                <div class="col-md-1">
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
                    </select>
                </div>

                {{-- Actions --}}
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button 
                            type="button" 
                            @click="clearFilters()" 
                            class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-x-circle me-1"></i>
                            Limpar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Active Filters Display --}}
            <div x-show="searchTerm || statusFilter || typeFilter" class="mt-3 pt-3 border-top">
                <div class="d-flex gap-2 flex-wrap">
                    <span x-show="searchTerm" class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                        Busca: "<span x-text="searchTerm"></span>"
                        <button type="button" @click="searchTerm = ''; filterTable()" class="btn-close btn-close-sm ms-2"></button>
                    </span>
                    <span x-show="statusFilter" class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                        Status: <span x-text="statusFilter === '1' ? 'Ativas' : 'Inativas'"></span>
                        <button type="button" @click="statusFilter = ''; filterTable()" class="btn-close btn-close-sm ms-2"></button>
                    </span>
                    <span x-show="typeFilter" class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                        Tipo: <span x-text="typeFilter === 'root' ? 'Principais' : 'Subcategorias'"></span>
                        <button type="button" @click="typeFilter = ''; filterTable()" class="btn-close btn-close-sm ms-2"></button>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-tag me-2"></i>
                    Categorias
                    <span class="badge bg-secondary ms-2" x-text="`${filteredCount} / ${totalCount}`"></span>
                </h5>
            </div>
        </div>

        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" @click="sortBy('name')" class="sortable-th">
                                <div class="d-flex align-items-center gap-2">
                                    Categoria
                                    <i :class="getSortIcon('name')"></i>
                                </div>
                            </th>
                            <th scope="col">Slug</th>
                            <th scope="col" @click="sortBy('products_count')" class="sortable-th">
                                <div class="d-flex align-items-center gap-2">
                                    Produtos
                                    <i :class="getSortIcon('products_count')"></i>
                                </div>
                            </th>
                            <th scope="col" @click="sortBy('order')" class="sortable-th">
                                <div class="d-flex align-items-center gap-2">
                                    Ordem
                                    <i :class="getSortIcon('order')"></i>
                                </div>
                            </th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="category in paginatedCategories" :key="category.id">
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span x-show="category.is_sub" class="text-muted me-2">└─</span>
                                        <div>
                                            <div class="fw-medium" x-text="category.name"></div>
                                            <small x-show="category.parent_name" class="text-muted">
                                                Subcategoria de: <span x-text="category.parent_name"></span>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted" x-text="category.slug"></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark" x-text="category.products_count"></span>
                                </td>
                                <td>
                                    <small class="text-muted" x-text="category.order"></small>
                                </td>
                                <td>
                                    <form :action="`{{ route('admin.categories.index') }}/${category.id}/toggle`" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                :class="category.is_active ? 'bg-success' : 'bg-danger'"
                                                class="badge border-0 text-white" 
                                                style="cursor: pointer;"
                                                x-text="category.is_active ? 'Ativa' : 'Inativa'">
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a :href="`{{ route('admin.categories.index') }}/${category.id}/edit`" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                            Editar
                                        </a>
                                        <button 
                                            x-show="category.can_delete"
                                            type="button"
                                            @click="deleteCategory(category.id)"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                            Excluir
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
                <i class="bi bi-tags display-1 text-muted mb-3"></i>
                <h5 class="card-title">Nenhuma categoria encontrada</h5>
                <p class="card-text text-muted">
                    Crie a primeira categoria para organizar os produtos.
                </p>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i>
                    Criar Categoria
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function categoriesTable() {
    return {
        // Data
        allCategories: {!! json_encode($categories->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'products_count' => $category->products_count ?? 0,
                'order' => $category->order,
                'is_active' => $category->is_active,
                'is_sub' => $category->parent_id !== null,
                'parent_name' => $category->parent ? $category->parent->name : null,
                'can_delete' => $category->products_count == 0 && $category->children()->count() == 0,
            ];
        })) !!},
        
        // Filters
        searchTerm: '',
        statusFilter: '',
        typeFilter: '',
        
        // Sorting
        sortField: 'order',
        sortDirection: 'asc',
        
        // Pagination
        currentPage: 1,
        perPage: 5,
        
        // Computed
        get filteredCategories() {
            let filtered = this.allCategories;
            
            // Search filter
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(cat => 
                    cat.name.toLowerCase().includes(term) ||
                    cat.slug.toLowerCase().includes(term)
                );
            }
            
            // Status filter
            if (this.statusFilter) {
                const isActive = this.statusFilter === '1';
                filtered = filtered.filter(cat => cat.is_active === isActive);
            }
            
            // Type filter
            if (this.typeFilter === 'root') {
                filtered = filtered.filter(cat => !cat.is_sub);
            } else if (this.typeFilter === 'sub') {
                filtered = filtered.filter(cat => cat.is_sub);
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
            return this.filteredCategories.length;
        },
        
        get totalCount() {
            return this.allCategories.length;
        },
        
        get totalPages() {
            return Math.ceil(this.filteredCount / this.perPage);
        },
        
        get paginatedCategories() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredCategories.slice(start, end);
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
            this.typeFilter = '';
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
        
        async deleteCategory(categoryId) {
            if (!confirm('Excluir esta categoria? Esta ação não pode ser desfeita.')) return;
            
            try {
                const response = await fetch(`{{ route('admin.categories.index') }}/${categoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao excluir categoria. Tente novamente.');
                }
            } catch (error) {
                alert('Erro ao excluir categoria. Tente novamente.');
            }
        },
    }
}
</script>
@endpush
@endsection
