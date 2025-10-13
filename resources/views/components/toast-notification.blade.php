{{-- Toast Notification Component --}}
@if(session()->has('message') || session()->has('success') || session()->has('error') || session()->has('verified'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;">
    <div id="notificationToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
         style="background-color: {{ session('error') ? '#dc3545' : '#09947d' }};">
        <div class="d-flex">
            <div class="toast-body">
                @if(session('verified'))
                    <i class="bi bi-check-circle-fill me-2"></i>
                @elseif(session('success'))
                    <i class="bi bi-check-circle-fill me-2"></i>
                @elseif(session('error'))
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                @else
                    <i class="bi bi-info-circle-fill me-2"></i>
                @endif
                {{ session('message') ?? session('success') ?? session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toastEl = document.getElementById('notificationToast');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 5000
            });
            toast.show();
        }
    });
</script>
@endif
