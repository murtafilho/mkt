@props([
    'name',
    'label' => '',
    'aspectRatio' => '16/9',
    'minWidth' => 0,
    'minHeight' => 0,
    'maxSize' => 2048,
    'required' => false,
    'accept' => 'image/jpeg,image/png,image/jpg',
    'helpText' => ''
])

<div x-data="{
    preview: null,
    fileName: '',
    fileSize: 0,
    handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            this.fileName = file.name;
            this.fileSize = Math.round(file.size / 1024);
            const reader = new FileReader();
            reader.onload = (e) => {
                this.preview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },
    clear() {
        this.preview = null;
        this.fileName = '';
        this.fileSize = 0;
        $refs.fileInput.value = '';
    }
}">
    @if($label)
        <label for="{{ $name }}" class="form-label fw-medium mb-2">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="vstack gap-3">
        <!-- Upload Area -->
        <div
            x-show="!preview"
            class="border border-2 border-dashed border-secondary rounded-3 p-4 text-center cursor-pointer transition"
            @click="$refs.fileInput.click()"
        >
            <svg class="mx-auto mb-3 text-muted" style="width: 48px; height: 48px;" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p class="mb-2 small text-muted">Clique para selecionar ou arraste uma imagem</p>
            @if($helpText)
                <p class="mb-0 small text-muted">{{ $helpText }}</p>
            @endif
        </div>

        <!-- Preview Area -->
        <div x-show="preview" class="position-relative">
            <img :src="preview" :alt="fileName" class="mw-100 rounded-3 border border-secondary" style="height: auto;">
            <div class="mt-2 d-flex align-items-center justify-content-between">
                <div class="small text-muted">
                    <span x-text="fileName"></span>
                    (<span x-text="fileSize"></span> KB)
                </div>
                <button
                    type="button"
                    @click="clear()"
                    class="btn btn-link btn-sm text-danger p-0"
                >
                    Remover
                </button>
            </div>
        </div>

        <!-- Hidden File Input -->
        <input
            type="file"
            x-ref="fileInput"
            id="{{ $name }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            @change="handleFileChange($event)"
            class="d-none"
            {{ $attributes }}
        >
    </div>

    @error($name)
        <p class="mt-2 small text-danger mb-0">{{ $message }}</p>
    @enderror
</div>

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
}

.border-dashed {
    border-style: dashed !important;
}

.mw-100 {
    max-width: 100%;
}
</style>
@endpush
