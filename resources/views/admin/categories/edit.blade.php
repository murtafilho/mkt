<x-layouts.admin>
    <x-slot:header>Editar Categoria</x-slot>
    <x-slot:title>Editar {{ $category->name }} - Admin</x-slot>

    <div class="container" style="max-width: 800px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <h2 class="h3 fw-bold text-dark mb-1">Editar Categoria</h2>
                    <p class="text-muted mb-0">{{ $category->name }}</p>
                </div>

                <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nome <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">
                            Slug <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="slug" id="slug" 
                               class="form-control @error('slug') is-invalid @enderror" 
                               value="{{ old('slug', $category->slug) }}" required>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Descrição
                        </label>
                        <textarea name="description" id="description" rows="3" 
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Parent Category -->
                    <div class="mb-3">
                        <label for="parent_id" class="form-label">
                            Categoria Pai (opcional)
                        </label>
                        <select name="parent_id" id="parent_id" 
                                class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">Nenhuma (categoria principal)</option>
                            @foreach($categories as $cat)
                                @if($cat->id !== $category->id)
                                    <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Order -->
                    <div class="mb-3">
                        <label for="order" class="form-label">
                            Ordem de Exibição
                        </label>
                        <input type="number" name="order" id="order" 
                               class="form-control @error('order') is-invalid @enderror" 
                               value="{{ old('order', $category->order) }}" min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Menor número aparece primeiro</div>
                    </div>

                    <!-- Active Status -->
                    <div class="form-check mb-4">
                        <input type="checkbox" name="is_active" id="is_active" 
                               class="form-check-input" value="1" 
                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">
                            Categoria ativa
                        </label>
                    </div>

                    <!-- Info Box -->
                    @if($category->products()->count() > 0)
                        <div class="alert alert-info d-flex align-items-start">
                            <svg class="flex-shrink-0 me-2" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div class="small">
                                Esta categoria possui <strong>{{ $category->products()->count() }} produtos</strong> associados.
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-3 pt-3 border-top">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
