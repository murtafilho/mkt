@props(['product'])

<div x-data="productGallery()">
 {{-- Main Image --}}
 <div class="aspect-square overflow-hidden rounded-xl bg-gradient-to-br from-neutral-100 to-neutral-200 shadow-lg">
 @if($product->hasImages())
 <img :src="currentImage"
 alt="{{ $product->name }}"
 loading="eager"
 decoding="async"
 class="h-full w-full object-cover cursor-zoom-in"
 @click="openLightbox()">
 @else
 <div class="flex h-full items-center justify-center">
 <svg class="h-32 w-32 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 @endif
 </div>

 {{-- Thumbnails --}}
 @if($product->getMedia('product_images')->count() > 1)
 <div class="mt-4 grid grid-cols-4 gap-2">
 @foreach($product->getMedia('product_images') as $index => $media)
 <button type="button"
 @click="selectImage({{ $index }})"
 :class="currentIndex === {{ $index }} ? 'ring-2 ring-primary-500' : 'ring-1 ring-neutral-200'"
 class="aspect-square overflow-hidden rounded-lg transition hover:ring-2 hover:ring-primary-300">
 <img src="{{ $media->getUrl('thumb') }}"
 alt="{{ $product->name }} - Imagem {{ $index + 1 }}"
 loading="lazy"
 decoding="async"
 class="h-full w-full object-cover">
 </button>
 @endforeach
 </div>
 @endif
</div>

@push('scripts')
<script>
function productGallery() {
 return {
 currentIndex: 0,
 images: @json($product->getMedia('product_images')->map(fn($m) => $m->getUrl('medium'))),

 get currentImage() {
 return this.images[this.currentIndex] || '/images/product-placeholder.svg';
 },

 selectImage(index) {
 this.currentIndex = index;
 },

 openLightbox() {
 // Lightbox será implementado na Phase 2
 console.log('Lightbox:', this.currentImage);
 }
 }
}
</script>
@endpush
