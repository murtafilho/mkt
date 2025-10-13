@extends('layouts.guest')

@section('guest-content')
    <div class="text-center mb-4">
        <h1 class="h3 mb-3 fw-bold">Verificação de E-mail</h1>
    </div>

    <div class="alert alert-info mb-4" role="alert">
        <i class="bi bi-envelope-check me-2"></i>
        <strong>Obrigado por se cadastrar!</strong>
        <p class="mb-0 mt-2">
            Antes de começar, você poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar? 
            Se você não recebeu o e-mail, teremos prazer em enviar outro.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            Um novo link de verificação foi enviado para o endereço de e-mail que você forneceu durante o registro.
        </div>
    @endif

    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-2"></i>
                Reenviar E-mail de Verificação
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-muted">
                <i class="bi bi-box-arrow-right me-2"></i>
                Sair
            </button>
        </form>
    </div>
@endsection
