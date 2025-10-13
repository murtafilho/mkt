@extends('layouts.base')

@section('content')
    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center py-5" 
         style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(210, 105, 30, 0.05) 100%);">
        
        {{-- Logo/Brand --}}
        <div class="mb-4">
            <a href="/" class="text-decoration-none">
                <h1 class="display-6 fw-bold text-primary mb-0">
                    <i class="bi bi-sun me-2"></i>
                    {{ config('app.name') }}
                </h1>
            </a>
        </div>

        {{-- Card Container --}}
        <div class="card shadow-lg border-0" style="width: 100%; max-width: 28rem;">
            <div class="card-body p-4">
                @yield('guest-content')
            </div>
        </div>

        {{-- Footer Link --}}
        <div class="mt-4">
            <a href="/" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i>
                Voltar para o site
            </a>
        </div>
    </div>
@endsection
