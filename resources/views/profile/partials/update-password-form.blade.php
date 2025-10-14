<section>
    <header class="mb-4">
        <h2 class="h5 fw-bold text-dark mb-2">
            Atualizar Senha
        </h2>
        <p class="text-muted small">
            Certifique-se de que sua conta está usando uma senha longa e aleatória para se manter segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Senha Atual</label>
            <input type="password" 
                   id="update_password_current_password" 
                   name="current_password" 
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- New Password --}}
        <div class="mb-3">
            <label for="update_password_password" class="form-label">Nova Senha</label>
            <input type="password" 
                   id="update_password_password" 
                   name="password" 
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                   autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">Confirmar Senha</label>
            <input type="password" 
                   id="update_password_password_confirmation" 
                   name="password_confirmation" 
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">Salvar</button>

            @if (session('status') === 'password-updated')
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
