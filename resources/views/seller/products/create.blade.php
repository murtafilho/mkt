@extends('layouts.seller')

@section('title', 'Novo Produto')

@section('header', 'Novo Produto')

@section('page-content')
    <div class="container-fluid py-4">
        {{-- Header --}}
        <div class="mb-4">
            <h1 class="h2 fw-bold text-dark mb-2">Novo Produto</h1>
            <p class="text-muted mb-0">Preencha as informações para adicionar um novo produto à sua loja.</p>
        </div>

        {{-- Form --}}
        <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data"
              x-data="productForm()" class="vstack gap-4">
            @csrf

            {{-- Alert de erros --}}
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
                        {{-- Debug: Detalhes técnicos (apenas em desenvolvimento) --}}
                        <details class="mt-3">
                            <summary class="user-select-none small fw-semibold">
                                <i class="bi bi-code-square me-1"></i>Ver detalhes técnicos (debug)
                            </summary>
                            <pre class="mt-2 small bg-white rounded p-2 mb-0" style="max-height: 200px; overflow-y: auto;">{{ print_r($errors->toArray(), true) }}</pre>
                        </details>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Alert de sucesso --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Alert de erro geral --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    <strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                                   value="{{ old('name') }}"
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
                                   value="{{ old('short_description') }}"
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
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Descreva detalhadamente as características, especificações e benefícios do produto</div>
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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @if($category->children->isNotEmpty())
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
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
                                   value="{{ old('sku') }}"
                                   placeholder="Ex: PROD-001"
                                   class="form-control">
                            <div class="form-text">Deixe em branco para gerar automaticamente</div>
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
                                       value="{{ old('cost_price') }}"
                                       step="0.01" min="0"
                                       x-model="costPrice"
                                       @input="calculateMargin"
                                       placeholder="0,00"
                                       class="form-control">
                            </div>
                            <div class="form-text">Seu custo de aquisição</div>
                        </div>

                        {{-- Preço Original --}}
                        <div class="col-12 col-md-4">
                            <label for="original_price" class="form-label small fw-medium">
                                Preço Original <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" name="original_price" id="original_price" required
                                       value="{{ old('original_price') }}"
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
                                       value="{{ old('sale_price') }}"
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
                                   value="{{ old('stock', 0) }}"
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
                                   value="{{ old('min_stock', 5) }}"
                                   min="0"
                                   class="form-control">
                            <div class="form-text">Você será alertado quando o estoque atingir este nível</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dimensões (para cálculo de frete) --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-3">Dimensões e Peso</h2>
                    <p class="text-muted small mb-4">Essas informações são usadas para calcular o frete</p>

                    <div class="row g-3">
                        {{-- Peso --}}
                        <div class="col-6 col-md-3">
                            <label for="weight" class="form-label small fw-medium">
                                Peso (kg)
                            </label>
                            <input type="number" name="weight" id="weight"
                                   value="{{ old('weight') }}"
                                   step="0.01" min="0"
                                   placeholder="0.00"
                                   class="form-control">
                        </div>

                        {{-- Largura --}}
                        <div class="col-6 col-md-3">
                            <label for="width" class="form-label small fw-medium">
                                Largura (cm)
                            </label>
                            <input type="number" name="width" id="width"
                                   value="{{ old('width') }}"
                                   step="0.01" min="0"
                                   placeholder="0.00"
                                   class="form-control">
                        </div>

                        {{-- Altura --}}
                        <div class="col-6 col-md-3">
                            <label for="height" class="form-label small fw-medium">
                                Altura (cm)
                            </label>
                            <input type="number" name="height" id="height"
                                   value="{{ old('height') }}"
                                   step="0.01" min="0"
                                   placeholder="0.00"
                                   class="form-control">
                        </div>

                        {{-- Profundidade --}}
                        <div class="col-6 col-md-3">
                            <label for="depth" class="form-label small fw-medium">
                                Profundidade (cm)
                            </label>
                            <input type="number" name="depth" id="depth"
                                   value="{{ old('depth') }}"
                                   step="0.01" min="0"
                                   placeholder="0.00"
                                   class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Imagens --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Imagens do Produto</h2>

                    <div class="alert alert-info d-flex align-items-start" role="alert">
                        <i class="bi bi-info-circle-fill flex-shrink-0 me-2"></i>
                        <div>
                            <p class="fw-semibold mb-1">Regras para Publicação:</p>
                            <ul class="mb-0 ps-3">
                                <li>Produtos podem ser salvos como <strong>rascunho sem imagens</strong></li>
                                <li>Para <strong>publicar</strong>, o produto deve ter <strong>pelo menos 1 imagem</strong></li>
                                <li>A primeira imagem será a <strong>imagem principal</strong> exibida nas listagens</li>
                            </ul>
                        </div>
                    </div>

                    <x-image-uploader
                        name="images"
                        label="Imagens do Produto"
                        :multiple="true"
                        :max-files="4"
                        :max-size="5120"
                        aspect-ratio="1:1"
                        :min-width="800"
                        :min-height="800"
                        help-text="Envie de 1 a 4 imagens quadradas (1:1). Ideal: 2048x2048px. Formatos: JPEG, JPG, PNG, WEBP. Máximo 5MB cada."
                    />
                </div>
            </div>

            {{-- Status e Opções --}}
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 fw-semibold mb-4">Status e Opções</h2>

                    <div class="vstack gap-3">
                        {{-- Status --}}
                        <div>
                            <label for="status" class="form-label small fw-medium">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" required class="form-select">
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Rascunho (não visível)</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publicado (visível na loja)</option>
                                <option value="out_of_stock" {{ old('status') == 'out_of_stock' ? 'selected' : '' }}>Sem Estoque</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>

                        {{-- Produto em Destaque --}}
                        <div class="form-check">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                   {{ old('is_featured') ? 'checked' : '' }}
                                   class="form-check-input">
                            <label for="is_featured" class="form-check-label">
                                <span class="fw-medium">Produto em Destaque</span>
                                <div class="form-text d-d-inline-d-block ms-2">Este produto aparecerá nas áreas de destaque da loja</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Botões de Ação --}}
            <div class="d-flex align-items-center justify-content-between pt-3 pb-2">
                <a href="{{ route('seller.products.index') }}" class="btn btn-link text-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Voltar para lista
                </a>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-plus-circle me-2"></i>
                    Criar Produto
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function productForm() {
        return {
            costPrice: {{ old('cost_price', 0) }},
            originalPrice: {{ old('original_price', 0) }},
            salePrice: {{ old('sale_price', 0) }},
            margin: 0,
            discount: 0,
            profit: 0,

            init() {
                this.calculateMargin();
            },

            calculateMargin() {
                const cost = parseFloat(this.costPrice) || 0;
                const original = parseFloat(this.originalPrice) || 0;
                const sale = parseFloat(this.salePrice) || 0;

                // Margem de lucro (baseada no custo)
                if (cost > 0 && sale > 0) {
                    this.margin = ((sale - cost) / cost) * 100;
                    this.profit = sale - cost;
                } else {
                    this.margin = 0;
                    this.profit = 0;
                }

                // Desconto (baseado no preço original)
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
            }
        }
    }
    </script>
    @endpush
@endsection
