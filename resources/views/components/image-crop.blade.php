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
        <label for="{{ $name }}" class="block text-sm font-medium text-neutral-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-danger-600">*</span>
            @endif
        </label>
    @endif

    <div class="space-y-4">
        <!-- Upload Area -->
        <div
            x-show="!preview"
            class="border-2 border-dashed border-neutral-300 rounded-lg p-6 text-center hover:border-primary-500 transition-colors cursor-pointer"
            @click="$refs.fileInput.click()"
        >
            <svg class="mx-auto h-12 w-12 text-neutral-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <p class="mt-2 text-sm text-neutral-600">Clique para selecionar ou arraste uma imagem</p>
            @if($helpText)
                <p class="mt-1 text-xs text-neutral-500">{{ $helpText }}</p>
            @endif
        </div>

        <!-- Preview Area -->
        <div x-show="preview" class="relative">
            <img :src="preview" :alt="fileName" class="max-w-full h-auto rounded-lg border border-neutral-300">
            <div class="mt-2 flex items-center justify-between">
                <div class="text-sm text-neutral-600">
                    <span x-text="fileName"></span>
                    (<span x-text="fileSize"></span> KB)
                </div>
                <button
                    type="button"
                    @click="clear()"
                    class="text-sm text-danger-600 hover:text-danger-800"
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
            class="hidden"
            {{ $attributes }}
        >
    </div>

    @error($name)
        <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
    @enderror
</div>
