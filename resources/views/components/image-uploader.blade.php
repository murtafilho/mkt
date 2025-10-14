@props([
    'name' => 'images',
    'label' => '',
    'multiple' => false,
    'maxFiles' => 4,
    'maxSize' => 5120, // KB
    'accept' => 'image/jpeg,image/jpg,image/png,image/webp',
    'aspectRatio' => '1:1',
    'minWidth' => 800,
    'minHeight' => 800,
    'helpText' => '',
    'existingImages' => [],
    'deleteRoute' => null,
    'required' => false,
    'sortable' => false,
])

@php
    $inputId = $name . '_' . uniqid();

    // Transform Media objects to array format expected by JavaScript
    if (is_array($existingImages)) {
        // Already an array
        $processedImages = $existingImages;
    } else {
        // Collection of Media objects - transform to array
        $processedImages = $existingImages->map(function($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'preview_url' => $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl(),
                'original_url' => $media->getUrl(),
            ];
        })->toArray();
    }
@endphp

<div
    x-data="{
        files: [],
        previews: [],
        existingImages: @js($processedImages),
        errors: [],
        isDragging: false,
        multiple: @js($multiple),
        maxFiles: @js($maxFiles),
        maxSize: @js($maxSize),
        minWidth: @js($minWidth),
        minHeight: @js($minHeight),
        deleteRoute: @js($deleteRoute),

        get totalImages() {
            return this.existingImages.length + this.previews.length;
        },

        get canAddMore() {
            return this.multiple ? this.totalImages < this.maxFiles : this.totalImages === 0;
        },

        async handleFiles(fileList) {
            this.errors = [];
            const files = Array.from(fileList);

            if (!this.multiple && files.length > 1) {
                this.errors.push('Selecione apenas uma imagem.');
                return;
            }

            if (this.multiple) {
                const remaining = this.maxFiles - this.totalImages;
                if (files.length > remaining) {
                    this.errors.push(`Você pode adicionar no máximo ${remaining} imagem(ns).`);
                    return;
                }
            }

            for (const file of files) {
                const error = await this.validateFile(file);
                if (error) {
                    this.errors.push(error);
                    continue;
                }

                this.files.push(file);

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previews.push({
                        url: e.target.result,
                        name: file.name,
                        size: (file.size / 1024).toFixed(2) + ' KB'
                    });
                };
                reader.readAsDataURL(file);
            }

            // Update input files
            this.updateInputFiles();
        },

        async validateFile(file) {
            // Validate type
            const acceptedTypes = @js($accept).split(',');
            if (!acceptedTypes.includes(file.type)) {
                return `${file.name}: Tipo de arquivo não permitido. Use JPEG, JPG, PNG ou WEBP.`;
            }

            // Validate size
            const sizeKB = file.size / 1024;
            if (sizeKB > this.maxSize) {
                return `${file.name}: Tamanho máximo de ${(this.maxSize / 1024).toFixed(1)}MB excedido.`;
            }

            // Validate dimensions (async)
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => {
                    if (img.width < this.minWidth || img.height < this.minHeight) {
                        resolve(`${file.name}: Dimensões mínimas: ${this.minWidth}x${this.minHeight}px.`);
                    } else {
                        resolve(null);
                    }
                };
                img.onerror = () => resolve(`${file.name}: Erro ao carregar imagem.`);
                img.src = URL.createObjectURL(file);
            });
        },

        removePreview(index) {
            this.files.splice(index, 1);
            this.previews.splice(index, 1);
            this.updateInputFiles();
        },

        async removeExisting(imageId) {
            if (!this.deleteRoute) {
                this.existingImages = this.existingImages.filter(img => img.id !== imageId);
                return;
            }

            if (!confirm('Tem certeza que deseja remover esta imagem?')) {
                return;
            }

            try {
                const response = await fetch(`${this.deleteRoute}/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    this.existingImages = this.existingImages.filter(img => img.id !== imageId);
                } else {
                    alert('Erro ao remover imagem. Tente novamente.');
                }
            } catch (error) {
                alert('Erro ao remover imagem. Tente novamente.');
            }
        },

        updateInputFiles() {
            const input = this.$refs.fileInput;
            const dataTransfer = new DataTransfer();
            this.files.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
        },

        triggerFileInput() {
            if (this.canAddMore) {
                this.$refs.fileInput.click();
            }
        }
    }"
    class="vstack gap-3">
    @if($label)
        <label class="form-label fw-medium">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{-- Upload Area --}}
    <div
        x-show="canAddMore"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)"
        @click="triggerFileInput()"
        :class="isDragging ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary'"
        class="border border-2 border-dashed rounded-3 p-4 text-center cursor-pointer transition"
        style="cursor: pointer;"
    >
        <svg class="mx-auto mb-3 text-muted" style="width: 48px; height: 48px;" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <p class="mb-2 text-muted">
            <span class="fw-semibold">Clique para selecionar</span> ou arraste {{ $multiple ? 'imagens' : 'uma imagem' }}
        </p>
        @if($helpText)
            <p class="mb-0 small text-muted">{{ $helpText }}</p>
        @endif
    </div>

    {{-- Hidden File Input --}}
    <input
        x-ref="fileInput"
        type="file"
        id="{{ $inputId }}"
        name="{{ $multiple ? $name . '[]' : $name }}"
        accept="{{ $accept }}"
        @if($multiple) multiple @endif
        @change="handleFiles($event.target.files)"
        class="d-none"
        {{ $attributes }}
    >

    {{-- Errors --}}
    <div x-show="errors.length > 0" class="vstack gap-1">
        <template x-for="error in errors" :key="error">
            <p x-text="error" class="small text-danger mb-0"></p>
        </template>
    </div>

    @error($name)
        <p class="small text-danger mb-0">{{ $message }}</p>
    @enderror
    @error($name . '.0')
        <p class="small text-danger mb-0">{{ $message }}</p>
    @enderror

    {{-- Preview Grid --}}
    <div x-show="totalImages > 0" class="row g-3">
        {{-- Existing Images --}}
        <template x-for="(image, index) in existingImages" :key="'existing-' + image.id">
            <div class="col-6 col-md-3">
                <div class="position-relative rounded-3 overflow-hidden border border-2 border-primary bg-light image-uploader-item" style="aspect-ratio: 1/1;">
                    <img
                        :src="image.preview_url || image.original_url"
                        :alt="image.name"
                        class="w-100 h-100"
                        style="object-fit: cover;"
                    >
                    <div class="image-uploader-overlay position-absolute top-0 start-0 end-0 bottom-0 bg-dark opacity-0 transition"></div>
                    <button
                        type="button"
                        @click.stop="removeExisting(image.id)"
                        class="image-uploader-delete-btn btn btn-danger btn-sm rounded-circle position-absolute top-50 start-50 translate-middle opacity-0 transition"
                    >
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-primary">Atual</span>
                    </div>
                </div>
            </div>
        </template>

        {{-- New Previews --}}
        <template x-for="(preview, index) in previews" :key="'preview-' + index">
            <div class="col-6 col-md-3">
                <div class="position-relative rounded-3 overflow-hidden border border-2 border-secondary bg-light image-uploader-item" style="aspect-ratio: 1/1;">
                    <img
                        :src="preview.url"
                        :alt="preview.name"
                        class="w-100 h-100"
                        style="object-fit: cover;"
                    >
                    <div class="image-uploader-overlay position-absolute top-0 start-0 end-0 bottom-0 bg-dark opacity-0 transition"></div>
                    <button
                        type="button"
                        @click.stop="removePreview(index)"
                        class="image-uploader-delete-btn btn btn-danger btn-sm rounded-circle position-absolute top-50 start-50 translate-middle opacity-0 transition"
                    >
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge bg-success">Novo</span>
                    </div>
                    <div class="position-absolute bottom-0 start-0 end-0 m-2 bg-dark bg-opacity-75 text-white small px-2 py-1 rounded text-truncate">
                        <span x-text="preview.size"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Counter --}}
    <div x-show="multiple && totalImages > 0" class="small text-muted">
        <span x-text="totalImages"></span> de {{ $maxFiles }} imagens
    </div>
</div>

@push('styles')
<style>
/* Image Uploader - Bootstrap 5.3 Custom Styles */
.image-uploader-item:hover .image-uploader-overlay {
    opacity: 0.4 !important;
}

.image-uploader-item:hover .image-uploader-delete-btn {
    opacity: 1 !important;
}

.cursor-pointer {
    cursor: pointer;
}

.border-dashed {
    border-style: dashed !important;
}
</style>
@endpush
