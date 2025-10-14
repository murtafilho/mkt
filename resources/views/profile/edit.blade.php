@extends('layouts.app')

@section('title', 'Perfil - MKT')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <h1 class="h3 fw-bold mb-4">Meu Perfil</h1>

            {{-- Update Profile Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Update Password --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Delete Account --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4 p-lg-5">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
