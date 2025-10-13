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

<div x-data="imageUploadSimple('{{ $name }}', '{{ $currentImageUrl }}')" class="space-y-3">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-neutral-700">
            {{ $label }}
        </label>
    @endif

    <!-- Preview Area -->
    <div x-show="previewUrl || currentImage" class="relative border-2 border-neutral-300 rounded-lg p-2">
        <img
            :src="previewUrl || currentImage"
            alt="Preview"
            class="w-full h-auto max-h-96 object-contain rounded-lg"
        >
        <!-- Remove Button -->
        <button
            @click.prevent="removeImage()"
            type="button"
            class="absolute top-4 right-4 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors shadow-lg"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Upload Area -->
    <div x-show="!previewUrl && !currentImage" class="border-2 border-dashed border-neutral-300 rounded-lg p-8 text-center bg-white">
        <svg class="mx-auto h-16 w-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <div class="mt-4">
            <label for="{{ $name }}" class="cursor-pointer inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                Escolher Imagem
            </label>
        </div>
        @if($helpText)
            <p class="mt-2 text-xs text-neutral-500">
                {{ $helpText }}
            </p>
        @endif
    </div>

    <!-- Error Message -->
    <div x-show="error" class="text-sm text-red-600" x-text="error"></div>

    <!-- Real File Input -->
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="file"
        class="hidden"
        accept="{{ $acceptedTypes }}"
        @change="handleFileSelect($event)"
        x-ref="fileInput"
    >

    <!-- Delete Marker (if removing existing image) -->
    <input x-show="markedForDeletion" type="hidden" :name="'{{ $name }}' + '_delete'" value="1">
</div>

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
