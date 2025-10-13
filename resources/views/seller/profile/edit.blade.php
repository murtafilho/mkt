@extends('layouts.seller')

@section('title', 'Editar Perfil - Vendedor')

@section('header', 'Editar Perfil do Vendedor')

@section('page-content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">Editar Perfil do Vendedor</h2>
            <p class="text-muted mb-0">Atualize as informações da sua loja</p>
        </div>
        <a href="{{ route('seller.show', $seller->slug) }}"
           target="_blank"
           class="btn btn-outline-secondary d-flex align-items-center">
            <i class="bi bi-eye me-2"></i>
            Ver Loja Pública
        </a>
    </div>

    <form method="POST" action="{{ route('seller.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Erros de Validação
                </h5>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Session Messages --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Left Column --}}
            <div class="col-lg-8">
                {{-- Store Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-shop me-2"></i>
                            Informações da Loja
                        </h5>
                        <div class="row g-3">
                            {{-- Store Name --}}
                            <div class="col-md-6">
                                <label for="store_name" class="form-label">
                                    Nome da Loja <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="store_name" id="store_name" required
                                       value="{{ old('store_name', $seller->store_name) }}"
                                       class="form-control @error('store_name') is-invalid @enderror">
                                @error('store_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Store Slug --}}
                            <div class="col-md-6">
                                <label for="slug" class="form-label">
                                    URL da Loja <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ url('/') }}/loja/</span>
                                    <input type="text" name="slug" id="slug" required
                                           value="{{ old('slug', $seller->slug) }}"
                                           class="form-control @error('slug') is-invalid @enderror">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Será usado na URL da sua loja</div>
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label for="description" class="form-label">
                                    Descrição da Loja
                                </label>
                                <textarea name="description" id="description" rows="4"
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $seller->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Conte aos clientes sobre sua loja</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-telephone me-2"></i>
                            Informações de Contato
                        </h5>
                        <div class="row g-3">
                            {{-- Phone --}}
                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Telefone <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="phone" id="phone" required
                                       value="{{ old('phone', $seller->phone) }}"
                                       placeholder="(11) 99999-9999"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    E-mail de Contato
                                </label>
                                <input type="email" name="email" id="email"
                                       value="{{ old('email', $seller->email) }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Deixe em branco para usar o e-mail da sua conta</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address Information --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-geo-alt me-2"></i>
                            Endereço da Loja
                        </h5>
                        <div class="row g-3">
                            {{-- CEP --}}
                            <div class="col-md-3">
                                <label for="postal_code" class="form-label">
                                    CEP <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="postal_code" id="postal_code" required
                                       value="{{ old('postal_code', $seller->address?->postal_code) }}"
                                       placeholder="00000-000"
                                       class="form-control @error('postal_code') is-invalid @enderror">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- State --}}
                            <div class="col-md-3">
                                <label for="state" class="form-label">
                                    Estado <span class="text-danger">*</span>
                                </label>
                                <select name="state" id="state" required
                                        class="form-select @error('state') is-invalid @enderror">
                                    <option value="">Selecione</option>
                                    @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf)
                                        <option value="{{ $uf }}" {{ old('state', $seller->address?->state) == $uf ? 'selected' : '' }}>
                                            {{ $uf }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- City --}}
                            <div class="col-md-6">
                                <label for="city" class="form-label">
                                    Cidade <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="city" id="city" required
                                       value="{{ old('city', $seller->address?->city) }}"
                                       class="form-control @error('city') is-invalid @enderror">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Street --}}
                            <div class="col-md-8">
                                <label for="street" class="form-label">
                                    Rua <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="street" id="street" required
                                       value="{{ old('street', $seller->address?->street) }}"
                                       class="form-control @error('street') is-invalid @enderror">
                                @error('street')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Number --}}
                            <div class="col-md-4">
                                <label for="number" class="form-label">
                                    Número <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="number" id="number" required
                                       value="{{ old('number', $seller->address?->number) }}"
                                       class="form-control @error('number') is-invalid @enderror">
                                @error('number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Neighborhood --}}
                            <div class="col-md-6">
                                <label for="neighborhood" class="form-label">
                                    Bairro <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="neighborhood" id="neighborhood" required
                                       value="{{ old('neighborhood', $seller->address?->neighborhood) }}"
                                       class="form-control @error('neighborhood') is-invalid @enderror">
                                @error('neighborhood')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Complement --}}
                            <div class="col-md-6">
                                <label for="complement" class="form-label">
                                    Complemento
                                </label>
                                <input type="text" name="complement" id="complement"
                                       value="{{ old('complement', $seller->address?->complement) }}"
                                       class="form-control @error('complement') is-invalid @enderror">
                                @error('complement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">
                {{-- Logo Upload --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-image me-2"></i>
                            Logo da Loja
                        </h5>
                        
                        {{-- Current Logo --}}
                        @if($seller->hasMedia('logo'))
                            <div class="text-center mb-3">
                                <img src="{{ $seller->getFirstMediaUrl('logo', 'thumb') }}" 
                                     alt="Logo atual" 
                                     class="img-thumbnail" 
                                     style="max-width: 200px; max-height: 200px;">
                                <p class="small text-muted mt-2">Logo atual</p>
                            </div>
                        @endif

                        {{-- Upload New Logo --}}
                        <div class="mb-3">
                            <label for="logo" class="form-label">Nova Logo</label>
                            <input type="file" name="logo" id="logo" 
                                   accept="image/*"
                                   class="form-control @error('logo') is-invalid @enderror">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Formatos aceitos: JPEG, PNG, WEBP. Máximo 2MB.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status Information --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Status da Loja
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Status Atual</label>
                            @switch($seller->status)
                                @case('active')
                                    <span class="badge bg-success">Ativo</span>
                                    @break
                                @case('pending')
                                    <span class="badge bg-warning">Pendente</span>
                                    @break
                                @case('suspended')
                                    <span class="badge bg-danger">Suspenso</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $seller->status }}</span>
                            @endswitch
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data de Cadastro</label>
                            <p class="mb-0">{{ $seller->created_at->format('d/m/Y') }}</p>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>
                                Alterações no perfil podem levar alguns minutos para serem refletidas na loja pública.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <a href="{{ route('seller.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Voltar
            </a>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-2"></i>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// CEP Auto-fill functionality
document.getElementById('postal_code').addEventListener('blur', function() {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('state').value = data.uf;
                    document.getElementById('city').value = data.localidade;
                    document.getElementById('street').value = data.logradouro;
                    document.getElementById('neighborhood').value = data.bairro;
                }
            })
            .catch(error => console.log('Erro ao buscar CEP:', error));
    }
});

// Phone mask
document.getElementById('phone').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    if (value.length <= 14) {
        this.value = value;
    }
});

// CEP mask
document.getElementById('postal_code').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
    if (value.length <= 9) {
        this.value = value;
    }
});
</script>
@endpush
@endsection