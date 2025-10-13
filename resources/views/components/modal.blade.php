@props([
 'name',
 'show' => false,
 'maxWidth' => 'lg'
])

@php
$maxWidthClass = [
 'sm' => 'modal-sm',
 'md' => '',
 'lg' => 'modal-lg',
 'xl' => 'modal-xl',
 '2xl' => 'modal-xl',
][$maxWidth];
@endphp

{{-- Bootstrap Modal --}}
<div class="modal fade" 
     id="modal-{{ $name }}" 
     tabindex="-1" 
     aria-labelledby="modal-{{ $name }}-label" 
     aria-hidden="true"
     x-data="{ show: @js($show) }"
     x-init="
        $watch('show', value => {
            const modal = bootstrap.Modal.getOrCreateInstance('#modal-{{ $name }}');
            if (value) {
                modal.show();
            } else {
                modal.hide();
            }
        });
        
        // Listen for Bootstrap modal events
        document.getElementById('modal-{{ $name }}').addEventListener('hidden.bs.modal', () => {
            show = false;
        });
     "
     x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
     x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null">
     
    <div class="modal-dialog {{ $maxWidthClass }}">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>
