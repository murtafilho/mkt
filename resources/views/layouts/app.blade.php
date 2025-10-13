@extends('layouts.base')

@section('content')
<div class="min-h-screen bg-neutral-50">
    @include('layouts.navigation')

    <!-- Page Heading -->
    @hasSection('header')
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('header')
        </div>
    </header>
    @endif

    <!-- Page Content -->
    <main>
        @yield('page-content')
    </main>
</div>
@endsection
