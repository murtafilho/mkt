@extends('layouts.public')

@section('title', $seller->store_name . ' - MKT')

@section('page-content')
 <section class="bg-white py-5">
 <div class="container px-4 px-lg-5">
     {{-- Seller Banner --}}
     @if($seller->hasMedia('seller_banner'))
     <div class="overflow-hidden shadow-sm rounded mb-4">
         <img src="{{ $seller->getFirstMediaUrl('seller_banner', 'medium') }}"
              alt="{{ $seller->store_name }} - Banner"
              loading="eager"
              class="w-100"
              style="height: 250px; object-fit: cover;">
     </div>
     @endif

     {{-- Seller Info --}}
     <div class="card shadow-sm mb-4">
         <div class="card-body">
             <div class="d-flex flex-column flex-md-row gap-4">
                 <div class="flex-shrink-0">
                     @if($seller->hasMedia('seller_logo'))
                     <img src="{{ $seller->getFirstMediaUrl('seller_logo', 'medium') }}"
                          alt="{{ $seller->store_name }}"
                          loading="eager"
                          class="rounded"
                          style="width: 120px; height: 120px; object-fit: cover;">
                     @else
                     <div class="bg-light rounded d-flex align-items-center justify-content-center"
                          style="width: 120px; height: 120px;">
                         <i class="bi bi-shop fs-1 text-muted"></i>
                     </div>
                     @endif
                 </div>

                 <div class="flex-grow-1">
                     <h1 class="h3 fw-bold mb-3">{{ $seller->store_name }}</h1>

                     @if($seller->description)
                     <p class="text-muted mb-3">{{ $seller->description }}</p>
                     @endif

                     <div class="row g-3 small">
                         <div class="col-md-6">
                             <strong class="d-block mb-1">E-mail:</strong>
                             <span class="text-muted">{{ $seller->business_email }}</span>
                         </div>
                         <div class="col-md-6">
                             <strong class="d-block mb-1">Telefone:</strong>
                             <span class="text-muted">{{ $seller->business_phone }}</span>
                         </div>
                         <div class="col-md-6">
                             <strong class="d-block mb-1">Membro desde:</strong>
                             <span class="text-muted">{{ $seller->created_at->format('d/m/Y') }}</span>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>

     {{-- Products --}}
     <div class="card shadow-sm">
         <div class="card-body">
             <h2 class="h4 fw-bold mb-4">Produtos</h2>

             @if($products->count() > 0)
             <div class="row g-3 g-md-4">
                 @foreach($products as $product)
                     <div class="col-6 col-md-4 col-lg-3">
                         <x-product-card :product="$product" />
                     </div>
                 @endforeach
             </div>

             {{-- Pagination --}}
             <div class="mt-4">
                 {{ $products->links() }}
             </div>
             @else
             <p class="text-muted text-center py-5 mb-0">
                 Este vendedor ainda não possui produtos cadastrados.
             </p>
             @endif
         </div>
     </div>
 </div>
 </section>
@endsection
