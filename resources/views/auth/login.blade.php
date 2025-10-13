@extends('layouts.guest')

@section('guest-content')
    {{-- Page Title --}}
    <div class="text-center mb-4">
        <h2 class="h4 fw-bold mb-2">Entrar na Conta</h2>
        <p class="text-muted small mb-0">Acesse sua conta no Vale do Sol</p>
    </div>

    {{-- Session Status --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- E-mail --}}
        <div class="mb-3">
            <label for="email" class="form-label">
                <i class="bi bi-envelope me-1"></i>
                E-mail
            </label>
            <input id="email" type="email" name="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" 
                   placeholder="seu@email.com"
                   required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Senha --}}
        <div class="mb-3">
            <label for="password" class="form-label">
                <i class="bi bi-lock me-1"></i>
                Senha
            </label>
            <input id="password" type="password" name="password" 
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="••••••••" 
                   required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Lembrar de mim --}}
        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label">
                Lembrar de mim
            </label>
        </div>

        {{-- Actions --}}
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Entrar
            </button>
        </div>

        {{-- Forgot Password --}}
        @if (Route::has('password.request'))
            <div class="text-center mb-3">
                <a class="btn btn-link text-decoration-none small" href="{{ route('password.request') }}">
                    <i class="bi bi-question-circle me-1"></i>
                    Esqueceu sua senha?
                </a>
            </div>
        @endif

        {{-- Link para cadastro --}}
        <div class="text-center pt-3 border-top">
            <p class="small text-muted mb-0">
                Não tem uma conta?
                <a href="{{ route('register') }}" class="text-primary text-decoration-none fw-medium">
                    Cadastre-se gratuitamente
                </a>
            </p>
        </div>
    </form>
@endsection
