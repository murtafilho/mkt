@props(['column', 'label', 'currentSort' => '', 'currentDirection' => 'asc'])

@php
    $isCurrent = $currentSort === $column;
    $nextDirection = $isCurrent && $currentDirection === 'asc' ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection]);
@endphp

<th {{ $attributes->merge(['scope' => 'col', 'class' => 'sortable-th']) }}>
    <a href="{{ $url }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
        <span>{{ $label }}</span>
        @if($isCurrent)
            @if($currentDirection === 'asc')
                <i class="bi bi-arrow-up text-primary"></i>
            @else
                <i class="bi bi-arrow-down text-primary"></i>
            @endif
        @else
            <i class="bi bi-arrow-down-up text-muted opacity-50"></i>
        @endif
    </a>
</th>

<style>
.sortable-th {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease;
}

.sortable-th:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.sortable-th a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sortable-th i {
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.sortable-th:hover i {
    opacity: 1 !important;
}
</style>
