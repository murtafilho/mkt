@extends('layouts.guest')

@section('guest-content')
    {{-- Page Title --}}
    <div class="text-center mb-4">
        <h2 class="h4 fw-bold mb-2">Criar Conta</h2>
        <p class="text-muted small mb-0">Cadastre-se no Vale do Sol</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">
                <i class="bi bi-person me-1"></i>
                Nome Completo
            </label>
            <input id="name" type="text" name="name" 
                   class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}"
                   placeholder="Seu nome completo" 
                   required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

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
                   required autocomplete="username">
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
                   placeholder="Mínimo 8 caracteres" 
                   required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">
                <i class="bi bi-info-circle me-1"></i>
                Use pelo menos 8 caracteres com letras e números
            </div>
        </div>

        {{-- Confirmar Senha --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                <i class="bi bi-lock-fill me-1"></i>
                Confirmar Senha
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation" 
                   class="form-control @error('password_confirmation') is-invalid @enderror"
                   placeholder="Digite a senha novamente" 
                   required autocomplete="new-password">
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit Button --}}
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-person-plus me-2"></i>
                Criar Minha Conta
            </button>
        </div>

        {{-- Link para login --}}
        <div class="text-center pt-3 border-top">
            <p class="small text-muted mb-0">
                Já possui uma conta?
                <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-medium">
                    Faça login
                </a>
            </p>
        </div>
    </form>
@endsection
