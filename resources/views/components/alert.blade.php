@props(['type' => 'info', 'title' => null, 'dismissible' => false])

<div {{ $attributes->merge(['class' => "alert alert-{$type}" . ($dismissible ? ' alert-dismissible fade show' : ''), 'role' => 'alert']) }}>
    @if($title)
        <h5 class="alert-heading mb-2">{{ $title }}</h5>
    @endif
    
    <div class="alert-content">
        {{ $slot }}
    </div>
    
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>

