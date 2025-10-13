<x-layouts.admin title="Configurações do Site">
    <x-slot:header>Configurações do Site</x-slot:header>

<div class="space-y-6">
<!-- Header -->
<div class="bg-neutral-800 border-b border-neutral-700 px-6 py-4">
<h1 class="text-2xl font-bold text-neutral-100">Configurações do Site</h1>
<p class="mt-1 text-sm text-neutral-400">Personalize a aparência e comportamento do marketplace</p>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" 
    x-data="settingsForm()" class="px-6 space-y-6">
@csrf
@method('PUT')

<!-- Alerts -->
@if ($errors->any())
<div class="bg-red-900/50 border border-red-700 text-red-200 px-4 py-3 rounded-lg">
<p class="font-semibold">Há erros no formulário:</p>
<ul class="mt-2 list-disc list-inside text-sm">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

@if (session('success'))
<div class="bg-green-900/50 border border-green-700 text-green-200 px-4 py-3 rounded-lg">
✅ {{ session('success') }}
</div>
@endif

<!-- Informações do Site -->
<div class="bg-neutral-800 border border-neutral-700 rounded-lg p-6">
<h2 class="text-lg font-semibold text-neutral-100 mb-4">📝 Informações do Site</h2>

<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Nome do Site
</label>
<input type="text" name="site_name" 
    value="{{ old('site_name', $settings['site'] ['site_name']) }}"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
</div>

<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Slogan
</label>
<input type="text" name="site_tagline" 
    value="{{ old('site_tagline', $settings['site']['site_tagline']) }}"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
</div>

<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Descrição (SEO)
</label>
<textarea name="site_description" rows="3"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">{{ old('site_description', $settings['site']['site_description']) }}</textarea>
</div>
</div>
</div>

<!-- Logo (SVG) -->
<div class="bg-neutral-800 border border-neutral-700 rounded-lg p-6">
<h2 class="text-lg font-semibold text-neutral-100 mb-4">🎨 Logo (SVG)</h2>

<div class="bg-blue-900/30 border border-blue-700 rounded-lg p-4 mb-4 text-sm text-blue-200">
💡 <strong>Dica:</strong> Cole o código SVG da sua logo. Formato vetorial garante qualidade em qualquer tamanho.
</div>

<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Código SVG
</label>
<textarea name="logo_svg" rows="8" 
    x-model="logoSvg"
    placeholder="<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 60'>...</svg>"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 font-mono text-xs focus:border-primary-500 focus:ring-primary-500">{{ old('logo_svg', $settings['branding']['logo_svg']) }}</textarea>
</div>

<!-- Preview -->
<div x-show="logoSvg" class="bg-neutral-900 border border-neutral-600 rounded-lg p-4">
<p class="text-sm text-neutral-400 mb-2">Preview:</p>
<div x-html="logoSvg" class="bg-white p-4 rounded"></div>
</div>

<div class="grid grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Largura (px)
</label>
<input type="number" name="logo_width" 
    value="{{ old('logo_width', $settings['branding']['logo_width']) }}"
    min="50" max="500"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
</div>

<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Altura (px)
</label>
<input type="number" name="logo_height" 
    value="{{ old('logo_height', $settings['branding']['logo_height']) }}"
    min="20" max="200"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
</div>
</div>
</div>
</div>

<!-- Hero Section -->
<div class="bg-neutral-800 border border-neutral-700 rounded-lg p-6">
<h2 class="text-lg font-semibold text-neutral-100 mb-4">🖼️ Hero da Homepage</h2>

<div class="space-y-4">
<!-- Hero Type -->
<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Tipo de Hero
</label>
<select name="hero_type" x-model="heroType"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
<option value="gradient">Gradiente CSS</option>
<option value="image">Imagem de Fundo</option>
</select>
</div>

<!-- Hero Image (shown only if type = image) -->
<div x-show="heroType === 'image'" class="space-y-2">
@if (!empty($settings['hero']['hero_image']))
<div class="bg-neutral-900 border border-neutral-600 rounded-lg p-4">
<p class="text-sm text-neutral-400 mb-2">Imagem Atual:</p>
<img src="{{ asset('storage/' . $settings['hero']['hero_image']) }}" 
    alt="Hero" class="max-w-md rounded">
<button type="button" @click="deleteHeroImage()"
    class="mt-2 text-red-400 hover:text-red-300 text-sm">
🗑️ Remover imagem
</button>
</div>
@endif

<label class="block text-sm font-medium text-neutral-300 mb-2">
{{ !empty($settings['hero']['hero_image']) ? 'Substituir Imagem' : 'Upload Imagem' }}
</label>
<input type="file" name="hero_image" accept="image/*"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2">
<p class="text-xs text-neutral-500">Ideal: 1920x600px. Máximo: 2MB</p>
</div>

<!-- Hero Text -->
<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Título do Hero
</label>
<input type="text" name="hero_title" 
    value="{{ old('hero_title', $settings['hero']['hero_title']) }}"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
</div>

<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Subtítulo do Hero
</label>
<input type="text" name="hero_subtitle" 
    value="{{ old('hero_subtitle', $settings['hero']['hero_subtitle']) }}"
    class="w-full bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 focus:border-primary-500 focus:ring-primary-500">
</div>
</div>
</div>

<!-- Cores (Tailwind CSS 4.0 - OKLCH) -->
<div class="bg-neutral-800 border border-neutral-700 rounded-lg p-6">
<h2 class="text-lg font-semibold text-neutral-100 mb-4">🎨 Cores do Site</h2>

<div class="bg-amber-900/30 border border-amber-700 rounded-lg p-4 mb-4 text-sm text-amber-200">
⚠️ <strong>Importante:</strong> Este projeto usa Tailwind CSS 4.0 com formato OKLCH para cores de alta qualidade.
</div>

<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Cor Primária (OKLCH)
<a href="https://oklch.com" target="_blank" class="text-primary-400 hover:text-primary-300 text-xs ml-2">
🔗 Gerador OKLCH
</a>
</label>
<div class="flex gap-2">
<input type="text" name="color_primary" 
    value="{{ old('color_primary', $settings['colors']['color_primary']) }}"
    placeholder="oklch(0.58 0.18 145)"
    class="flex-1 bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 font-mono text-sm focus:border-primary-500 focus:ring-primary-500">
<div class="w-16 h-10 rounded border-2 border-neutral-600"
    style="background: {{ $settings['colors']['color_primary'] }}"></div>
</div>
<p class="text-xs text-neutral-500 mt-1">Usado em botões, links, destaques</p>
</div>

<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Cor Secundária (OKLCH)
</label>
<div class="flex gap-2">
<input type="text" name="color_secondary" 
    value="{{ old('color_secondary', $settings['colors']['color_secondary']) }}"
    placeholder="oklch(0.68 0.15 250)"
    class="flex-1 bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 font-mono text-sm focus:border-primary-500 focus:ring-primary-500">
<div class="w-16 h-10 rounded border-2 border-neutral-600"
    style="background: {{ $settings['colors']['color_secondary'] }}"></div>
</div>
<p class="text-xs text-neutral-500 mt-1">Usado em badges, tags, elementos secundários</p>
</div>

<div>
<label class="block text-sm font-medium text-neutral-300 mb-2">
Cor de Destaque (OKLCH)
</label>
<div class="flex gap-2">
<input type="text" name="color_accent" 
    value="{{ old('color_accent', $settings['colors']['color_accent']) }}"
    placeholder="oklch(0.75 0.20 85)"
    class="flex-1 bg-neutral-900 border-neutral-600 text-neutral-100 rounded-lg px-4 py-2 font-mono text-sm focus:border-primary-500 focus:ring-primary-500">
<div class="w-16 h-10 rounded border-2 border-neutral-600"
    style="background: {{ $settings['colors']['color_accent'] }}"></div>
</div>
<p class="text-xs text-neutral-500 mt-1">Usado em promoções, CTAs especiais</p>
</div>
</div>
</div>

<!-- Ordem das Seções da Home -->
<div class="bg-neutral-800 border border-neutral-700 rounded-lg p-6">
<h2 class="text-lg font-semibold text-neutral-100 mb-4">📐 Ordem das Seções da Homepage</h2>

<div class="bg-blue-900/30 border border-blue-700 rounded-lg p-4 mb-4 text-sm text-blue-200">
💡 <strong>Arraste e solte</strong> para reordenar as seções que aparecem na homepage.
</div>

<input type="hidden" name="home_sections_order" x-model="sectionsOrderJson">

<div class="space-y-2" x-ref="sortableContainer">
<template x-for="(section, index) in sectionsOrder" :key="section.id">
<div 
    class="bg-neutral-900 border border-neutral-600 rounded-lg p-4 cursor-move hover:border-primary-500 transition-colors"
    draggable="true"
    @dragstart="dragStart(index)"
    @dragover.prevent
    @drop="drop(index)">
    
    <div class="flex items-center gap-3">
        <!-- Drag handle -->
        <svg class="w-5 h-5 text-neutral-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"></path>
        </svg>
        
        <!-- Section info -->
        <div class="flex-1">
            <p class="font-medium text-neutral-100" x-text="section.name"></p>
            <p class="text-xs text-neutral-500" x-text="section.description"></p>
        </div>
        
        <!-- Order number -->
        <span class="text-neutral-500 font-mono text-sm" x-text="index + 1"></span>
    </div>
</div>
</template>
</div>
</div>

<!-- Actions -->
<div class="flex items-center justify-between border-t border-neutral-700 pt-6">
<a href="{{ route('admin.dashboard') }}" 
    class="text-neutral-400 hover:text-neutral-200">
← Voltar ao Dashboard
</a>

<button type="submit" 
    class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 py-2 rounded-lg transition-colors">
Salvar Configurações
</button>
</div>
</form>
</div>

@push('scripts')
<script>
function settingsForm() {
    return {
        logoSvg: @js(old('logo_svg', $settings['branding']['logo_svg'])),
        heroType: @js(old('hero_type', $settings['hero']['hero_type'])),
        sectionsOrder: [
            { id: 'featured', name: 'Produtos em Destaque', description: 'Produtos marcados como destaque' },
            { id: 'categories', name: 'Categorias', description: 'Grid de categorias principais' },
            { id: 'latest', name: 'Últimos Produtos', description: 'Produtos adicionados recentemente' },
            { id: 'deals', name: 'Promoções', description: 'Produtos com desconto' },
        ],
        draggedIndex: null,

        init() {
            // Load saved order
            const savedOrder = @js($settings['sections']['home_sections_order']);
            if (savedOrder && Array.isArray(savedOrder)) {
                // Reorder based on saved IDs
                const ordered = [];
                savedOrder.forEach(id => {
                    const section = this.sectionsOrder.find(s => s.id === id);
                    if (section) ordered.push(section);
                });
                // Add any missing sections at the end
                this.sectionsOrder.forEach(section => {
                    if (!ordered.find(s => s.id === section.id)) {
                        ordered.push(section);
                    }
                });
                this.sectionsOrder = ordered;
            }
            
            this.updateJson();
        },

        dragStart(index) {
            this.draggedIndex = index;
        },

        drop(dropIndex) {
            if (this.draggedIndex === null) return;

            const draggedItem = this.sectionsOrder[this.draggedIndex];
            this.sectionsOrder.splice(this.draggedIndex, 1);
            this.sectionsOrder.splice(dropIndex, 0, draggedItem);

            this.draggedIndex = null;
            this.updateJson();
        },

        updateJson() {
            this.sectionsOrderJson = JSON.stringify(this.sectionsOrder.map(s => s.id));
        },

        get sectionsOrderJson() {
            return JSON.stringify(this.sectionsOrder.map(s => s.id));
        },

        async deleteHeroImage() {
            if (!confirm('Tem certeza que deseja remover a imagem do hero?')) {
                return;
            }

            try {
                const response = await fetch('{{ route("admin.settings.deleteHeroImage") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Erro ao remover imagem');
                }
            } catch (error) {
                alert('Erro ao remover imagem');
            }
        }
    }
}
</script>
@endpush
</x-layouts.admin>

