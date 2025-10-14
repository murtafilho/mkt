<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Title --}}
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

