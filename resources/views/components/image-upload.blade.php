@props([
    'name',
    'label' => null,
    'collection' => 'default',
    'currentImage' => null,
    'currentImageUrl' => null,
    'acceptedTypes' => 'image/jpeg,image/png,image/webp',
    'maxSize' => 2048, // KB
    'width' => null,
    'height' => null,
    'aspectRatio' => null,
    'helpText' => null,
])

<div x-data="imageUploadSimple('{{ $name }}', '{{ $currentImageUrl }}')" class="vstack gap-3">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-medium">
            {{ $label }}
        </label>
    @endif

    <!-- Preview Area -->
    <div x-show="previewUrl || currentImage" class="position-relative border border-2 border-secondary rounded-3 p-2">
        <img
            :src="previewUrl || currentImage"
            alt="Preview"
            class="w-100 rounded-3"
            style="height: auto; max-height: 384px; object-fit: contain;"
        >
        <!-- Remove Button -->
        <button
            @click.prevent="removeImage()"
            type="button"
            class="btn btn-danger btn-sm rounded-circle position-absolute shadow"
            style="top: 1rem; right: 1rem;"
        >
            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Upload Area -->
    <div x-show="!previewUrl && !currentImage" class="border border-2 border-dashed border-secondary rounded-3 p-4 p-md-5 text-center bg-white">
        <svg class="mx-auto mb-3 text-muted" style="width: 64px; height: 64px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <div class="mt-3">
            <label for="{{ $name }}" class="btn btn-primary cursor-pointer">
                Escolher Imagem
            </label>
        </div>
        @if($helpText)
            <p class="mt-2 mb-0 small text-muted">
                {{ $helpText }}
            </p>
        @endif
    </div>

    <!-- Error Message -->
    <div x-show="error" class="small text-danger" x-text="error"></div>

    <!-- Real File Input -->
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="file"
        class="d-none"
        accept="{{ $acceptedTypes }}"
        @change="handleFileSelect($event)"
        x-ref="fileInput"
    >

    <!-- Delete Marker (if removing existing image) -->
    <input x-show="markedForDeletion" type="hidden" :name="'{{ $name }}' + '_delete'" value="1">
</div>

@push('scripts')
<script>
function imageUploadSimple(name, currentImageUrl) {
    return {
        name: name,
        currentImage: currentImageUrl || null,
        previewUrl: null,
        error: null,
        markedForDeletion: false,

        init() {
            // Initialize preview with current image if exists
            if (this.currentImage && this.currentImage !== '') {
                // Keep currentImage as is
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.error = null;
            this.markedForDeletion = false;

            // Create preview URL
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
                this.currentImage = null; // Clear current image when new one is selected
            };
            reader.readAsDataURL(file);
        },

        removeImage() {
            this.previewUrl = null;
            this.error = null;

            if (this.currentImage) {
                // Mark existing image for deletion
                this.markedForDeletion = true;
                this.currentImage = null;
            }

            // Reset file input
            this.$refs.fileInput.value = '';
        }
    }
}
</script>
@endpush

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
}

.border-dashed {
    border-style: dashed !important;
}
</style>
@endpush
