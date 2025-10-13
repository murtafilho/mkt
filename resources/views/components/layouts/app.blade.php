<x-layouts.base :title="$title ?? config('app.name', 'Laravel')" class="bg-light">
 <div class="min-vh-100">
 @include('layouts.navigation')

 {{-- Page Heading --}}
 @isset($header)
 <header class="bg-white shadow-sm">
 <div class="container py-4">
 <h2 class="h4 fw-semibold text-dark mb-0">
 {{ $header }}
 </h2>
 </div>
 </header>
 @endisset

 {{-- Page Content --}}
 <main>
 {{ $slot }}
 </main>
 </div>
</x-layouts.base>
