@extends('layouts.admin')

@section('title', 'Configurações do Site - Admin')

@section('header', 'Configurações do Site')

@section('page-content')
    <div class="container-fluid">
        {{-- Page Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-gear-fill text-white fs-5"></i>
                    </div>
                    <div>
                        <h1 class="h3 fw-bold mb-1 text-dark">Configurações do Site</h1>
                        <p class="text-muted mb-0">Personalize a aparência e comportamento do marketplace</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Há erros no formulário:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}"
              method="POST"
              enctype="multipart/form-data"
              x-data="settingsForm()">
            @csrf
            @method('PUT')

            {{-- Informações do Site --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-info-circle text-info"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0 text-dark">Informações do Site</h3>
                                    <p class="text-muted small mb-0">Configure as informações básicas do marketplace</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="site_name" class="form-label fw-medium">Nome do Site</label>
                                    <input type="text"
                                           name="site_name"
                                           id="site_name"
                                           value="{{ old('site_name', $settings['site']['site_name']) }}"
                                           class="form-control form-control-lg"
                                           placeholder="Ex: Vale do Sol Marketplace">
                                </div>

                                <div class="col-12">
                                    <label for="site_tagline" class="form-label fw-medium">Slogan</label>
                                    <input type="text"
                                           name="site_tagline"
                                           id="site_tagline"
                                           value="{{ old('site_tagline', $settings['site']['site_tagline']) }}"
                                           class="form-control form-control-lg"
                                           placeholder="Ex: Seu marketplace local">
                                </div>

                                <div class="col-12">
                                    <label for="site_description" class="form-label fw-medium">Descrição (SEO)</label>
                                    <textarea name="site_description"
                                              id="site_description"
                                              rows="3"
                                              class="form-control"
                                              placeholder="Descrição que aparecerá nos resultados de busca...">{{ old('site_description', $settings['site']['site_description']) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logo e Hero --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-image text-secondary"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0 text-dark">Logo e Hero</h3>
                                    <p class="text-muted small mb-0">Configure o logo e seção hero da homepage</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label for="logo_png_file" class="form-label fw-medium">Logo PNG</label>
                                    <input type="file"
                                           name="logo_png_file"
                                           id="logo_png_file"
                                           accept=".png"
                                           @change="handlePngUpload($event)"
                                           class="form-control">
                                    <div class="form-text">Tamanho recomendado: 200x200px. Máximo: 2MB</div>
                                </div>

                                @if($settings['branding']['logo_png'])
                                <div class="col-12" x-show="!logoPreview">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p class="small text-muted mb-2">Logo Atual:</p>
                                            <img src="{{ $settings['branding']['logo_png'] }}" alt="Logo" class="img-fluid" style="max-width: 200px; background: white; padding: 1rem; border-radius: 0.5rem;">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="col-12" x-show="logoPreview" x-cloak>
                                    <div class="card bg-light border-success">
                                        <div class="card-body">
                                            <p class="small text-success mb-2">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Preview do Novo Logo:
                                            </p>
                                            <img :src="logoPreview" alt="Preview" class="img-fluid" style="max-width: 200px; background: white; padding: 1rem; border-radius: 0.5rem;">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="hero_title" class="form-label fw-medium">Título do Hero</label>
                                    <input type="text"
                                           name="hero_title"
                                           id="hero_title"
                                           value="{{ old('hero_title', $settings['hero']['hero_title']) }}"
                                           class="form-control"
                                           placeholder="Ex: Bem-vindo ao Vale do Sol">
                                </div>

                                <div class="col-md-6">
                                    <label for="hero_subtitle" class="form-label fw-medium">Subtítulo do Hero</label>
                                    <input type="text"
                                           name="hero_subtitle"
                                           id="hero_subtitle"
                                           value="{{ old('hero_subtitle', $settings['hero']['hero_subtitle']) }}"
                                           class="form-control"
                                           placeholder="Ex: Seu marketplace local">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cores do Site --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-palette text-warning"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0 text-dark">Cores do Site</h3>
                                    <p class="text-muted small mb-0">Configure as cores principais do marketplace</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label for="color_primary" class="form-label fw-medium">Cor Primária</label>
                                    <div class="input-group">
                                        <input type="color"
                                               name="color_primary"
                                               id="color_primary"
                                               value="{{ old('color_primary', $settings['colors']['color_primary']) }}"
                                               class="form-control form-control-color"
                                               style="width: 60px; height: 44px;">
                                        <input type="text"
                                               class="form-control"
                                               value="{{ old('color_primary', $settings['colors']['color_primary']) }}"
                                               readonly>
                                    </div>
                                    <div class="form-text">Usado em botões, links, destaques</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="color_secondary" class="form-label fw-medium">Cor Secundária</label>
                                    <div class="input-group">
                                        <input type="color"
                                               name="color_secondary"
                                               id="color_secondary"
                                               value="{{ old('color_secondary', $settings['colors']['color_secondary']) }}"
                                               class="form-control form-control-color"
                                               style="width: 60px; height: 44px;">
                                        <input type="text"
                                               class="form-control"
                                               value="{{ old('color_secondary', $settings['colors']['color_secondary']) }}"
                                               readonly>
                                    </div>
                                    <div class="form-text">Usado em badges, tags, elementos secundários</div>
                                </div>

                                <div class="col-md-4">
                                    <label for="color_accent" class="form-label fw-medium">Cor de Destaque</label>
                                    <div class="input-group">
                                        <input type="color"
                                               name="color_accent"
                                               id="color_accent"
                                               value="{{ old('color_accent', $settings['colors']['color_accent']) }}"
                                               class="form-control form-control-color"
                                               style="width: 60px; height: 44px;">
                                        <input type="text"
                                               class="form-control"
                                               value="{{ old('color_accent', $settings['colors']['color_accent']) }}"
                                               readonly>
                                    </div>
                                    <div class="form-text">Usado em promoções, CTAs especiais</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Configurações Avançadas --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 border-bottom">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-sliders text-success"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-semibold mb-0 text-dark">Configurações Avançadas</h3>
                                    <p class="text-muted small mb-0">Ajustes adicionais do sistema</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="hero_type" class="form-label fw-medium">Tipo de Hero da Homepage</label>
                                    <select name="hero_type" id="hero_type" class="form-select">
                                        <option value="gradient" {{ old('hero_type', $settings['hero']['hero_type']) === 'gradient' ? 'selected' : '' }}>Gradiente CSS</option>
                                        <option value="image" {{ old('hero_type', $settings['hero']['hero_type']) === 'image' ? 'selected' : '' }}>Imagem de Fundo</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="hero_opacity" class="form-label fw-medium">Opacidade do Gradiente (%)</label>
                                    <input type="range" 
                                           name="hero_opacity" 
                                           id="hero_opacity" 
                                           min="10" 
                                           max="50" 
                                           value="{{ old('hero_opacity', $settings['hero']['hero_opacity'] ?? 20) }}"
                                           class="form-range">
                                    <div class="form-text">Valor atual: <span id="opacity-value">{{ old('hero_opacity', $settings['hero']['hero_opacity'] ?? 20) }}</span>%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center p-4 bg-light rounded">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Voltar ao Dashboard
                        </a>

                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-check-circle me-2"></i>
                            Salvar Configurações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function settingsForm() {
        return {
            logoPreview: null,

            // Handle PNG file upload
            handlePngUpload(event) {
                const file = event.target.files[0];

                if (!file) {
                    this.logoPreview = null;
                    return;
                }

                // Validate file type
                if (!file.type.match('image/png')) {
                    alert('Por favor, selecione um arquivo PNG válido.');
                    event.target.value = '';
                    this.logoPreview = null;
                    return;
                }

                // Validate file size (2MB = 2097152 bytes)
                if (file.size > 2097152) {
                    alert('O arquivo PNG é muito grande. Tamanho máximo: 2MB.');
                    event.target.value = '';
                    this.logoPreview = null;
                    return;
                }

                // Read file and show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.logoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            init() {
                // Range slider for opacity
                const opacitySlider = document.getElementById('hero_opacity');
                const opacityValue = document.getElementById('opacity-value');

                if (opacitySlider && opacityValue) {
                    opacitySlider.addEventListener('input', function() {
                        opacityValue.textContent = this.value;
                    });
                }
            }
        }
    }
    </script>
    @endpush
@endsection
