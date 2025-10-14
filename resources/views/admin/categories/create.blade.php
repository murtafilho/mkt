@extends('layouts.admin')

@section('header', 'Nova Categoria')
@section('title', 'Nova Categoria - Admin')

@section('page-content')
    <div class="container" style="max-width: 800px;">
        <div class="card">
            <div class="card-body">
                <div class="mb-4">
                    <h2 class="h3 fw-bold text-dark mb-1">Criar Nova Categoria</h2>
                    <p class="text-muted mb-0">Preencha os dados da categoria</p>
                </div>

                <form method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nome <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Slug (auto-generated) -->
                    <div class="mb-3">
                        <label for="slug" class="form-label">
                            Slug (deixe vazio para gerar automaticamente)
                        </label>
                        <input type="text" name="slug" id="slug" 
                               class="form-control @error('slug') is-invalid @enderror" 
                               value="{{ old('slug') }}">
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Se vazio, será gerado automaticamente a partir do nome</div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            Descrição
                        </label>
                        <textarea name="description" id="description" rows="3" 
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
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
                                <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
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
                               value="{{ old('order', 0) }}" min="0">
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Menor número aparece primeiro</div>
                    </div>

                    <!-- Active Status -->
                    <div class="form-check mb-4">
                        <input type="checkbox" name="is_active" id="is_active" 
                               class="form-check-input" value="1" 
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">
                            Categoria ativa
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex align-items-center justify-content-end gap-3 pt-3 border-top">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Criar Categoria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // Auto-generate slug from name
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                // Only auto-generate if slug is empty or was auto-generated before
                if (!slugInput.dataset.manual) {
                    const slug = this.value
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '') // Remove accents
                        .replace(/[^\w\s-]/g, '') // Remove special chars
                        .replace(/\s+/g, '-') // Replace spaces with -
                        .replace(/-+/g, '-') // Replace multiple - with single -
                        .replace(/^-+|-+$/g, ''); // Trim - from start/end

                    slugInput.value = slug;
                }
            });

            // Mark as manual if user types directly in slug field
            slugInput.addEventListener('input', function() {
                slugInput.dataset.manual = 'true';
            });
        }
    });
    </script>
    @endpush
@endsection
