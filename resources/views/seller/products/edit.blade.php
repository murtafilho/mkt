@extends('layouts.seller')

@section('title', 'Editar Produto')

@section('header', 'Editar Produto')

@section('page-content')
    <div class="container-fluid py-4">
        {{-- Header com ações --}}
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
            <div>
                <h1 class="h2 fw-bold text-dark mb-1">Editar Produto</h1>
                <p class="text-muted mb-0">{{ $product->name }}</p>
            </div>

            <div class="d-flex gap-2">
                @if($product->status === 'draft')
                    <form action="{{ route('seller.products.publish', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Publicar
                        </button>
                    </form>
                @elseif($product->status === 'published')
                    <form action="{{ route('seller.products.unpublish', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-pause-circle me-1"></i>
                            Despublicar
                        </button>
                    </form>
                @endif

                <form action="{{ route('seller.products.destroy', $product) }}" method="POST"
                      onsubmit="return confirm('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.')"
                      class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Excluir
                    </button>
                </form>
            </div>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>
                <strong>{{ session('error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading fw-semibold mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Há erros no formulário ({{ $errors->count() }} erro{{ $errors->count() > 1 ? 's' : '' }})
                </h5>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li class="fw-medium">{{ $error }}</li>
                    @endforeach
                </ul>

                @if(config('app.debug'))
                    <details class="mt-3">
                        <summary class="user-select-none small fw-semibold">
                            <i class="bi bi-code-square me-1"></i>Ver detalhes técnicos (debug)
                        </summary>
                        <pre class="mt-2 small bg-white rounded p-2 mb-2" style="max-height: 200px; overflow-y: auto;">{{ print_r($errors->toArray(), true) }}</pre>
                        <hr class="my-2">
                        <p class="small fw-semibold mb-1">Request Data:</p>
                        <pre class="small bg-white rounded p-2 mb-0" style="max-height: 200px; overflow-y: auto;">{{ print_r(request()->all(), true) }}</pre>
                    </details>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Estatísticas do Produto --}}
        <div class="card mb-4 border-primary">
            <div class="card-body bg-primary bg-opacity-10">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-3">
                        <p class="small text-secondary mb-1">Visualizações</p>
                        <p class="h4 fw-bold text-primary mb-0">{{ $product->views }}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="small text-secondary mb-1">Estoque Atual</p>
                        <p class="h4 fw-bold mb-0 {{ $product->stock < ($product->min_stock ?? 5) ? 'text-danger' : 'text-success' }}">
                            {{ $product->stock }}
                        </p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="small text-secondary mb-1">Status</p>
                        <p class="small fw-semibold mb-0">
                            @if($product->status === 'published')
                                <span class="text-success"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Publicado</span>
                            @elseif($product->status === 'draft')
                                <span class="text-secondary"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Rascunho</span>
                            @elseif($product->status === 'out_of_stock')
                                <span class="text-danger"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Sem Estoque</span>
                            @else
                                <span class="text-muted"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Inativo</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="small text-secondary mb-1">Preço</p>
                        <p class="h5 fw-bold text-primary mb-0">R$ {{ number_format($product->sale_price, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert de estoque baixo --}}
        @if($product->stock < ($product->min_stock ?? 5))
            <div class="alert alert-warning d-flex align-items-start" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                <div>
                    <p class="fw-semibold mb-1">Estoque Baixo!</p>
                    <p class="mb-0 small">O estoque está abaixo do nível mínimo recomendado ({{ $product->min_stock ?? 5 }} unidades)</p>
                </div>
            </div>
        @endif

        {{-- Form (AJAX submission) --}}
        <form @submit.prevent="submitForm" x-data="productForm()" class="vstack gap-4">
            @csrf
            @method('PUT')

            {{-- Informações Básicas --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Informações Básicas</h2>

                    <div class="vstack gap-3">
                        {{-- Nome do Produto --}}
                        <div>
                            <label for="name" class="form-label small fw-medium">
                                Nome do Produto <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name', $product->name) }}"
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Descrição Curta --}}
                        <div>
                            <label for="short_description" class="form-label small fw-medium">
                                Descrição Curta
                            </label>
                            <input type="text" name="short_description" id="short_description"
                                   value="{{ old('short_description', $product->short_description) }}"
                                   maxlength="500"
                                   placeholder="Resumo do produto (até 500 caracteres)"
                                   class="form-control">
                            <div class="form-text">Aparecerá nas listagens de produtos</div>
                        </div>

                        {{-- Descrição Completa --}}
                        <div>
                            <label for="description" class="form-label small fw-medium">
                                Descrição Completa <span class="text-danger">*</span>
                            </label>
                            <textarea name="description" id="description" rows="6" required
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Categoria --}}
                        <div>
                            <label for="category_id" class="form-label small fw-medium">
                                Categoria <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" id="category_id" required
                                    class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">Selecione uma categoria</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @if($category->children->isNotEmpty())
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;&nbsp;&nbsp;└─ {{ $child->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- SKU --}}
                        <div>
                            <label for="sku" class="form-label small fw-medium">
                                SKU (Código do Produto)
                            </label>
                            <input type="text" name="sku" id="sku"
                                   value="{{ old('sku', $product->sku) }}"
                                   class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Precificação --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Precificação</h2>

                    <div class="row g-3 mb-4">
                        {{-- Preço de Custo --}}
                        <div class="col-12 col-md-4">
                            <label for="cost_price" class="form-label small fw-medium">
                                Preço de Custo
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="cost_price" id="cost_price"
                                       value="{{ old('cost_price', $product->cost_price) }}"
                                       step="0.01" min="0"
                                       x-model="costPrice"
                                       @input="calculateMargin"
                                       class="form-control">
                            </div>
                        </div>

                        {{-- Preço Original --}}
                        <div class="col-12 col-md-4">
                            <label for="original_price" class="form-label small fw-medium">
                                Preço Original <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="original_price" id="original_price" required
                                       value="{{ old('original_price', $product->original_price) }}"
                                       step="0.01" min="0"
                                       x-model="originalPrice"
                                       @input="calculateMargin"
                                       class="form-control @error('original_price') is-invalid @enderror">
                                @error('original_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Preço de Venda --}}
                        <div class="col-12 col-md-4">
                            <label for="sale_price" class="form-label small fw-medium">
                                Preço de Venda <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="sale_price" id="sale_price" required
                                       value="{{ old('sale_price', $product->sale_price) }}"
                                       step="0.01" min="0"
                                       x-model="salePrice"
                                       @input="calculateMargin"
                                       class="form-control @error('sale_price') is-invalid @enderror">
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Calculadora de Margem --}}
                    <div class="bg-light rounded p-3">
                        <div class="row g-3 small">
                            <div class="col-12 col-md-4">
                                <span class="text-secondary">Margem de Lucro:</span>
                                <span class="ms-2 fw-bold"
                                      :class="margin >= 0 ? 'text-success' : 'text-danger'"
                                      x-text="formatPercent(margin)"></span>
                            </div>
                            <div class="col-12 col-md-4">
                                <span class="text-secondary">Desconto:</span>
                                <span class="ms-2 fw-bold text-warning" x-text="formatPercent(discount)"></span>
                            </div>
                            <div class="col-12 col-md-4">
                                <span class="text-secondary">Lucro Estimado:</span>
                                <span class="ms-2 fw-bold"
                                      :class="profit >= 0 ? 'text-success' : 'text-danger'"
                                      x-text="formatCurrency(profit)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estoque --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Estoque e Inventário</h2>

                    <div class="row g-3">
                        {{-- Quantidade em Estoque --}}
                        <div class="col-12 col-md-6">
                            <label for="stock" class="form-label small fw-medium">
                                Quantidade em Estoque <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="stock" id="stock" required
                                   value="{{ old('stock', $product->stock) }}"
                                   min="0"
                                   class="form-control @error('stock') is-invalid @enderror">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Estoque Mínimo --}}
                        <div class="col-12 col-md-6">
                            <label for="min_stock" class="form-label small fw-medium">
                                Estoque Mínimo
                            </label>
                            <input type="number" name="min_stock" id="min_stock"
                                   value="{{ old('min_stock', $product->min_stock) }}"
                                   min="0"
                                   class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dimensões --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Dimensões e Peso</h2>

                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <label for="weight" class="form-label small fw-medium">Peso (kg)</label>
                            <input type="number" name="weight" id="weight"
                                   value="{{ old('weight', $product->weight) }}"
                                   step="0.01" min="0"
                                   class="form-control">
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="width" class="form-label small fw-medium">Largura (cm)</label>
                            <input type="number" name="width" id="width"
                                   value="{{ old('width', $product->width) }}"
                                   step="0.01" min="0"
                                   class="form-control">
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="height" class="form-label small fw-medium">Altura (cm)</label>
                            <input type="number" name="height" id="height"
                                   value="{{ old('height', $product->height) }}"
                                   step="0.01" min="0"
                                   class="form-control">
                        </div>

                        <div class="col-6 col-md-3">
                            <label for="depth" class="form-label small fw-medium">Profundidade (cm)</label>
                            <input type="number" name="depth" id="depth"
                                   value="{{ old('depth', $product->depth) }}"
                                   step="0.01" min="0"
                                   class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Imagens --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Imagens do Produto</h2>

                    @if($product->getMedia('product_images')->count() === 0)
                        <div class="alert alert-warning d-flex align-items-start mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                            <p class="mb-0 small">
                                <strong>Atenção:</strong> Este produto ainda não possui imagens. Adicione pelo menos 1 imagem para publicá-lo.
                            </p>
                        </div>
                    @endif

                    <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 me-2"></i>
                        <p class="mb-0 small">
                            <strong>Dica:</strong> A primeira imagem é considerada a imagem principal do produto e aparecerá nas listagens.
                        </p>
                    </div>

                    <x-image-uploader
                        name="images"
                        label="Gerenciar Imagens"
                        :multiple="true"
                        :max-files="4"
                        :max-size="5120"
                        aspect-ratio="1:1"
                        :min-width="800"
                        :min-height="800"
                        help-text="Envie de 1 a 4 imagens quadradas (1:1). Ideal: 2048x2048px. Formatos: JPEG, JPG, PNG, WEBP. Máximo 5MB cada."
                        :existing-images="$product->getMedia('product_images')"
                        :delete-route="url('/seller/products/' . $product->id . '/images')"
                        :sortable="true"
                    />
                </div>
            </div>

            {{-- Status --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Status e Opções</h2>

                    <div class="vstack gap-3">
                        <div>
                            <label for="status" class="form-label small fw-medium">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" required class="form-select">
                                <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                <option value="published" {{ old('status', $product->status) == 'published' ? 'selected' : '' }}>Publicado</option>
                                <option value="out_of_stock" {{ old('status', $product->status) == 'out_of_stock' ? 'selected' : '' }}>Sem Estoque</option>
                                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                   class="form-check-input">
                            <label for="is_featured" class="form-check-label">
                                <span class="fw-medium">Produto em Destaque</span>
                                <div class="form-text d-d-inline-d-block ms-2">Este produto aparecerá nas áreas de destaque da loja</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botões --}}
            <div class="d-flex align-items-center justify-content-between pt-3 pb-2">
                <a href="{{ route('seller.products.index') }}" class="btn btn-link text-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para lista
                </a>

                <button type="submit" :disabled="isSubmitting" class="btn btn-primary px-4">
                    <span x-show="!isSubmitting">
                        <i class="bi bi-save me-2"></i>
                        Salvar Alterações
                    </span>
                    <span x-show="isSubmitting" class="d-flex align-items-center">
                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                        Salvando...
                    </span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function productForm() {
        return {
            // Form fields
            costPrice: {{ old('cost_price', $product->cost_price ?? 0) }},
            originalPrice: {{ old('original_price', $product->original_price) }},
            salePrice: {{ old('sale_price', $product->sale_price) }},
            margin: 0,
            discount: 0,
            profit: 0,
            isSubmitting: false,

            init() {
                this.calculateMargin();
            },

            calculateMargin() {
                const cost = parseFloat(this.costPrice) || 0;
                const original = parseFloat(this.originalPrice) || 0;
                const sale = parseFloat(this.salePrice) || 0;

                if (cost > 0 && sale > 0) {
                    this.margin = ((sale - cost) / cost) * 100;
                    this.profit = sale - cost;
                } else {
                    this.margin = 0;
                    this.profit = 0;
                }

                if (original > 0 && sale > 0 && sale < original) {
                    this.discount = ((original - sale) / original) * 100;
                } else {
                    this.discount = 0;
                }
            },

            formatPercent(value) {
                return value.toFixed(1) + '%';
            },

            formatCurrency(value) {
                return 'R$ ' + value.toFixed(2).replace('.', ',');
            },

            /**
             * AJAX Form Submission (following official guide pattern)
             */
            async submitForm(event) {
                if (this.isSubmitting) return;

                this.isSubmitting = true;
                const form = event.target;

                try {
                    // Build FormData from form
                    const formData = new FormData(form);

                    // Add images from Alpine.js image-uploader component
                    const imageUploaderComponent = Alpine.$data(document.querySelector('[x-data]'));
                    if (imageUploaderComponent && imageUploaderComponent.files && imageUploaderComponent.files.length > 0) {
                        imageUploaderComponent.files.forEach((file, index) => {
                            formData.append(`images[${index}]`, file);
                        });
                    }

                    console.log('Submitting form with', formData.get('name'), 'and', imageUploaderComponent?.files?.length || 0, 'images');

                    // Submit via fetch
                    const response = await fetch('{{ route('seller.products.update', $product) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });

                    console.log('✅ Response received, status:', response.status);

                    // Get response as text first
                    const responseText = await response.text();
                    console.log('📄 Response text (first 500 chars):', responseText.substring(0, 500));

                    // Try to parse as JSON
                    let result;
                    try {
                        result = JSON.parse(responseText);
                        console.log('✅ Parsed as JSON:', result);
                    } catch (parseError) {
                        console.error('❌ Response is NOT JSON!');
                        console.error('Parse error:', parseError);
                        console.error('Full response:', responseText);
                        alert('ERRO: Servidor retornou HTML ao invés de JSON. Verifique o console (F12) para detalhes.');
                        this.isSubmitting = false;
                        return;
                    }

                    if (response.ok) {
                        // Success - redirect to products list
                        console.log('✅ Update successful!');
                        alert('Produto atualizado com sucesso!');
                        window.location.href = '{{ route('seller.products.index') }}';
                    } else {
                        // Validation errors
                        console.error('❌ Validation errors:', result.errors);
                        console.error('❌ Error message:', result.message);
                        alert('Erro ao atualizar produto: ' + (result.message || 'Verifique os campos e tente novamente.'));
                        this.isSubmitting = false;
                    }
                } catch (error) {
                    console.error('❌ Fatal error:', error);
                    console.error('Error stack:', error.stack);
                    alert('Erro fatal ao enviar formulário: ' + error.message + '\nAbra o console (F12) para mais detalhes.');
                    this.isSubmitting = false;
                }
            }
        }
    }
    </script>
    @endpush
@endsection
