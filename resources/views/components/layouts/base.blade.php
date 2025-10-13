<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">

 {{-- Page Title: Can be overridden by child layouts --}}
 <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

 {{-- Fonts: Self-hosted via @fontsource (imported in app.css) --}}
 {{-- Removed external CDN dependency for better performance and privacy --}}

 {{-- Additional Head Content: For child layouts to inject extra meta/links --}}
 @stack('head')

 {{-- Vite Assets: Single source of truth --}}
 @vite(['resources/css/app.css', 'resources/js/app.js'])

 {{-- Additional Styles: For page-specific styles --}}
 @stack('styles')
</head>
<body {{ $attributes->merge(['class' => 'font-sans antialiased']) }}>
 {{--
 Main Content Slot:
 Child layouts will define their specific structure here
 (navigation, header, main content, footer, etc.)
 --}}
 {{ $slot }}

 {{-- Additional Scripts: For page-specific scripts --}}
 @stack('scripts')
</body>
</html>
