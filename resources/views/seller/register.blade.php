@extends('layouts.app')

@section('title', 'Tornar-se Vendedor - MKT')

@section('header', 'Tornar-se Vendedor')

@section('page-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <h5 class="alert-heading">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Erros de Validação
                            </h5>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            @if(config('app.debug'))
                                <details class="mt-3">
                                    <summary class="user-select-none small fw-semibold">
                                        <i class="bi bi-code-square me-1"></i>Ver detalhes técnicos (debug)
                                    </summary>
                                    <pre class="mt-2 small bg-white rounded p-2 mb-0" style="max-height: 200px; overflow-y: auto;">{{ print_r($errors->toArray(), true) }}</pre>
                                </details>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Session Messages --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-x-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('seller.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Section: Informações Básicas --}}
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Informações Básicas
                                    <span class="badge bg-primary ms-2">Obrigatório</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Store Name --}}
                                    <div class="col-md-6">
                                        <label for="store_name" class="form-label">
                                            Nome da Loja <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="store_name" id="store_name" required
                                               value="{{ old('store_name') }}"
                                               placeholder="Ex: Minha Loja Online"
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
                                                   value="{{ old('slug') }}"
                                                   placeholder="minha-loja-online"
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
                                        <textarea name="description" id="description" rows="3"
                                                  placeholder="Conte aos clientes sobre sua loja..."
                                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Uma breve descrição da sua loja</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Informações de Contato --}}
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-telephone me-2"></i>
                                    Informações de Contato
                                    <span class="badge bg-primary ms-2">Obrigatório</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Phone --}}
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">
                                            Telefone <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="phone" id="phone" required
                                               value="{{ old('phone') }}"
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
                                               value="{{ old('email') }}"
                                               placeholder="contato@minhaloja.com"
                                               class="form-control @error('email') is-invalid @enderror">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Deixe em branco para usar o e-mail da sua conta</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Endereço --}}
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    Endereço da Loja
                                    <span class="badge bg-primary ms-2">Obrigatório</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- CEP --}}
                                    <div class="col-md-3">
                                        <label for="postal_code" class="form-label">
                                            CEP <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="postal_code" id="postal_code" required
                                               value="{{ old('postal_code') }}"
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
                                                <option value="{{ $uf }}" {{ old('state') == $uf ? 'selected' : '' }}>
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
                                               value="{{ old('city') }}"
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
                                               value="{{ old('street') }}"
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
                                               value="{{ old('number') }}"
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
                                               value="{{ old('neighborhood') }}"
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
                                               value="{{ old('complement') }}"
                                               class="form-control @error('complement') is-invalid @enderror">
                                        @error('complement')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Documentos --}}
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Documentos
                                    <span class="badge bg-primary ms-2">Obrigatório</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    {{-- Document Type --}}
                                    <div class="col-md-6">
                                        <label for="document_type" class="form-label">
                                            Tipo de Documento <span class="text-danger">*</span>
                                        </label>
                                        <select name="document_type" id="document_type" required
                                                class="form-select @error('document_type') is-invalid @enderror">
                                            <option value="">Selecione</option>
                                            <option value="cpf" {{ old('document_type') == 'cpf' ? 'selected' : '' }}>CPF (Pessoa Física)</option>
                                            <option value="cnpj" {{ old('document_type') == 'cnpj' ? 'selected' : '' }}>CNPJ (Pessoa Jurídica)</option>
                                        </select>
                                        @error('document_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Document Number --}}
                                    <div class="col-md-6">
                                        <label for="document_number" class="form-label">
                                            Número do Documento <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="document_number" id="document_number" required
                                               value="{{ old('document_number') }}"
                                               placeholder="000.000.000-00 ou 00.000.000/0000-00"
                                               class="form-control @error('document_number') is-invalid @enderror">
                                        @error('document_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Logo --}}
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-header bg-transparent border-0">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-image me-2"></i>
                                    Logo da Loja
                                    <span class="badge bg-secondary ms-2">Opcional</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="logo" class="form-label">Logo da Loja</label>
                                    <input type="file" name="logo" id="logo" 
                                           accept="image/*"
                                           class="form-control @error('logo') is-invalid @enderror">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Formatos aceitos: JPEG, PNG, WEBP. Máximo 2MB. Você pode adicionar uma logo depois.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Terms and Conditions --}}
                        <div class="card border-0 bg-light mb-4">
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox" name="terms_accepted" id="terms_accepted" required
                                           class="form-check-input @error('terms_accepted') is-invalid @enderror">
                                    <label for="terms_accepted" class="form-check-label">
                                        Eu li e aceito os <a href="#" target="_blank" class="text-decoration-none">Termos de Uso</a> 
                                        e a <a href="#" target="_blank" class="text-decoration-none">Política de Privacidade</a>
                                        <span class="text-danger">*</span>
                                    </label>
                                    @error('terms_accepted')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-row">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-shop me-2"></i>
                                Criar Minha Loja
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Após o cadastro, sua conta será analisada e você receberá um e-mail de confirmação.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Document type change handler
document.getElementById('document_type').addEventListener('change', function() {
    const documentNumberInput = document.getElementById('document_number');
    if (this.value === 'cpf') {
        documentNumberInput.placeholder = '000.000.000-00';
    } else if (this.value === 'cnpj') {
        documentNumberInput.placeholder = '00.000.000/0000-00';
    } else {
        documentNumberInput.placeholder = '000.000.000-00 ou 00.000.000/0000-00';
    }
});

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

// Document number mask
document.getElementById('document_number').addEventListener('input', function() {
    const documentType = document.getElementById('document_type').value;
    let value = this.value.replace(/\D/g, '');
    
    if (documentType === 'cpf' && value.length <= 11) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    } else if (documentType === 'cnpj' && value.length <= 14) {
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }
    
    this.value = value;
});
</script>
@endpush
@endsection