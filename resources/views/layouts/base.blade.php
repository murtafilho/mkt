<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Title --}}
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    {{-- Additional Head Content --}}
    @stack('head')

    {{-- Vite Assets: SCSS (Bootstrap + Custom) + JS --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Additional Styles --}}
    @stack('styles')
</head>
<body class="@yield('body-class', '')">
    {{-- Main Content: Child layouts define structure here --}}
    @yield('content')

    {{-- Additional Scripts --}}
    @stack('scripts')
</body>
</html>

