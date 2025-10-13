<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Services\ProductService;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->productService = new ProductService;
    $this->seller = Seller::factory()->create(['status' => 'active', 'approved_at' => now()]);
    $this->category = Category::factory()->create();
});

test('can create product with valid data', function () {
    $data = [
        'seller_id' => $this->seller->id,
        'category_id' => $this->category->id,
        'name' => 'Produto Teste',
        'slug' => 'produto-teste',
        'description' => 'Descrição do produto',
        'sku' => 'SKU-001',
        'original_price' => 150.00,
        'sale_price' => 100.00,
        'stock' => 10,
        'status' => 'draft',
    ];

    $product = $this->productService->createProduct($data);

    expect($product)->toBeInstanceOf(Product::class);
    expect($product->name)->toBe('Produto Teste');
    expect($product->seller_id)->toBe($this->seller->id);
    expect($product->status)->toBe('draft');
});

test('can update product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'name' => 'Produto Original',
    ]);

    $data = [
        'name' => 'Produto Atualizado',
        'sale_price' => 150.00,
    ];

    $updated = $this->productService->updateProduct($product, $data);

    expect($updated->name)->toBe('Produto Atualizado');
    expect($updated->sale_price)->toBe('150.00');
});

test('can delete product', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    $result = $this->productService->deleteProduct($product);

    expect($result)->toBeTrue();
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('can get product by id', function () {
    $product = Product::factory()->create(['seller_id' => $this->seller->id]);

    $found = $this->productService->getProductById($product->id);

    expect($found)->toBeInstanceOf(Product::class);
    expect($found->id)->toBe($product->id);
});

test('can get product by slug', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'slug' => 'produto-unico',
    ]);

    $found = $this->productService->getProductBySlug('produto-unico');

    expect($found)->toBeInstanceOf(Product::class);
    expect($found->id)->toBe($product->id);
});

test('can get published products only', function () {
    Product::factory()->count(3)->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);
    Product::factory()->count(2)->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
    ]);

    $published = $this->productService->getPublishedProducts();

    expect($published)->toHaveCount(3);
    expect($published->first()->status)->toBe('published');
});

test('can get products by category', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->count(3)->create([
        'seller_id' => $this->seller->id,
        'category_id' => $category1->id,
        'status' => 'published',
    ]);
    Product::factory()->count(2)->create([
        'seller_id' => $this->seller->id,
        'category_id' => $category2->id,
        'status' => 'published',
    ]);

    $products = $this->productService->getProductsByCategory($category1);

    expect($products)->toHaveCount(3);
});

test('can get products by seller', function () {
    $seller1 = Seller::factory()->create();
    $seller2 = Seller::factory()->create();

    Product::factory()->count(4)->create(['seller_id' => $seller1->id]);
    Product::factory()->count(2)->create(['seller_id' => $seller2->id]);

    $products = $this->productService->getProductsBySeller($seller1);

    expect($products)->toHaveCount(4);
});

test('can search products by name', function () {
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'name' => 'Notebook Dell',
        'status' => 'published',
    ]);
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'name' => 'Mouse Logitech',
        'status' => 'published',
    ]);
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'name' => 'Teclado Dell',
        'status' => 'published',
    ]);

    $results = $this->productService->searchProducts('Dell');

    expect($results)->toHaveCount(2);
});

test('can increase stock', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
    ]);

    $updated = $this->productService->increaseStock($product, 5);

    expect($updated->stock)->toBe(15);
});

test('can decrease stock', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
    ]);

    $updated = $this->productService->decreaseStock($product, 3);

    expect($updated->stock)->toBe(7);
});

test('throws exception when decreasing stock below zero', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 5,
    ]);

    expect(fn () => $this->productService->decreaseStock($product, 10))
        ->toThrow(Exception::class, 'Estoque insuficiente');
});

test('can check if product is in stock', function () {
    $inStock = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 10,
    ]);
    $outOfStock = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'stock' => 0,
    ]);

    expect($this->productService->isInStock($inStock))->toBeTrue();
    expect($this->productService->isInStock($outOfStock))->toBeFalse();
});

test('can publish product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'draft',
        'stock' => 10,
    ]);

    // Add at least one image (required for publishing)
    $image = UploadedFile::fake()->image('product.jpg', 300, 300);
    $product->addMedia($image)->toMediaCollection('product_images');

    $published = $this->productService->publishProduct($product);

    expect($published->status)->toBe('published');
});

test('can unpublish product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    $unpublished = $this->productService->unpublishProduct($product);

    expect($unpublished->status)->toBe('draft');
});

test('can get featured products', function () {
    Product::factory()->count(3)->create([
        'seller_id' => $this->seller->id,
        'is_featured' => true,
        'status' => 'published',
    ]);
    Product::factory()->count(5)->create([
        'seller_id' => $this->seller->id,
        'is_featured' => false,
        'status' => 'published',
    ]);

    $featured = $this->productService->getFeaturedProducts();

    expect($featured)->toHaveCount(3);
    expect($featured->first()->is_featured)->toBeTrue();
});

test('can get products on sale', function () {
    Product::factory()->count(2)->create([
        'seller_id' => $this->seller->id,
        'original_price' => 150.00,
        'sale_price' => 100.00,
        'status' => 'published',
    ]);
    Product::factory()->count(3)->create([
        'seller_id' => $this->seller->id,
        'original_price' => 100.00,
        'sale_price' => 100.00, // Same price, not on sale
        'status' => 'published',
    ]);

    $onSale = $this->productService->getProductsOnSale();

    expect($onSale)->toHaveCount(2);
});

test('can filter products by price range', function () {
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'sale_price' => 50.00,
        'status' => 'published',
    ]);
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'sale_price' => 150.00,
        'status' => 'published',
    ]);
    Product::factory()->create([
        'seller_id' => $this->seller->id,
        'sale_price' => 250.00,
        'status' => 'published',
    ]);

    $filtered = $this->productService->filterByPriceRange(100, 200);

    expect($filtered)->toHaveCount(1);
    expect($filtered->first()->sale_price)->toBe('150.00');
});

test('can get latest products', function () {
    Product::factory()->count(10)->create([
        'seller_id' => $this->seller->id,
        'status' => 'published',
    ]);

    $latest = $this->productService->getLatestProducts(5);

    expect($latest)->toHaveCount(5);
});

test('can calculate discount percentage', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'original_price' => 200.00,
        'sale_price' => 150.00,
    ]);

    $discount = $this->productService->calculateDiscountPercentage($product);

    expect($discount)->toBe(25.0); // 25% discount
});
