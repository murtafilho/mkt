@props(['filters' => []])

@if(count($filters) > 0)
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach($filters as $key => $value)
            @if($value && !is_array($value))
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm">
                    <span>{{ $key }}: {{ $value }}</span>
                    <a href="{{ request()->fullUrlWithQuery([$key => null]) }}" class="hover:text-primary-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                </div>
            @endif
        @endforeach

        @php
            $hasActiveFilters = collect($filters)->filter(fn($value) => $value && !is_array($value))->isNotEmpty();
        @endphp

        @if($hasActiveFilters)
            <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 px-3 py-1 bg-neutral-100 text-neutral-700 rounded-full text-sm hover:bg-neutral-200">
                Limpar todos
            </a>
        @endif
    </div>
@endif
