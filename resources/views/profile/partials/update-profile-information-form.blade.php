<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-dark mb-2">
            Informações do Perfil
        </h2>
        <p class="text-muted small">
            Atualize as informações de perfil e endereço de e-mail da sua conta.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        {{-- Name --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name', $user->name) }}" 
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="small text-body-secondary mb-2">
                        Seu endereço de e-mail não foi verificado.
                    </p>
                    <button form="send-verification" class="btn btn-link btn-sm p-0">
                        Clique aqui para reenviar o e-mail de verificação.
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small text-success fw-medium">
                            Um novo link de verificação foi enviado para seu endereço de e-mail.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- CPF/CNPJ --}}
        <div class="mb-3">
            <label for="cpf_cnpj" class="form-label">
                CPF/CNPJ
                <span class="badge bg-primary ms-1">Obrigatório para PIX</span>
            </label>
            <input type="text"
                   id="cpf_cnpj"
                   name="cpf_cnpj"
                   class="form-control @error('cpf_cnpj') is-invalid @enderror"
                   value="{{ old('cpf_cnpj', $user->cpf_cnpj) }}"
                   placeholder="000.000.000-00 ou 00.000.000/0000-00"
                   maxlength="18"
                   autocomplete="off">
            @error('cpf_cnpj')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                Necessário para pagamentos via PIX no checkout
            </small>
        </div>

        {{-- Phone --}}
        <div class="mb-3">
            <label for="phone" class="form-label">Telefone</label>
            <input type="text"
                   id="phone"
                   name="phone"
                   class="form-control @error('phone') is-invalid @enderror"
                   value="{{ old('phone', $user->phone) }}"
                   placeholder="(00) 00000-0000"
                   maxlength="15"
                   autocomplete="tel">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">Salvar</button>

            @if (session('status') === 'profile-updated')
                <p class="small text-success mb-0" 
                   x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)">
                    Salvo.
                </p>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
// Máscara para CPF/CNPJ
document.addEventListener('DOMContentLoaded', function() {
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    const phoneInput = document.getElementById('phone');

    if (cpfCnpjInput) {
        cpfCnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length <= 11) {
                // CPF: 000.000.000-00
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                // CNPJ: 00.000.000/0000-00
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }

            e.target.value = value;
        });
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length <= 10) {
                // Fixo: (00) 0000-0000
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                // Celular: (00) 00000-0000
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }

            e.target.value = value;
        });
    }
});
</script>
@endpush
