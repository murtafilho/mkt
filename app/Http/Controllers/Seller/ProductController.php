<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Display listing of seller's products.
     */
    public function index(Request $request)
    {
        Log::info('ProductController::index called', [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()->email,
            'has_seller' => Auth::user()->seller !== null,
        ]);

        /** @var Seller|null $seller */
        $seller = Auth::user()->seller;

        if (! $seller) {
            Log::error('ProductController::index - User has no seller profile', [
                'user_id' => Auth::id(),
            ]);
            abort(403, 'Você precisa ter um perfil de vendedor aprovado.');
        }

        $query = Product::query()
            ->where('seller_id', $seller->id)
            ->with('category');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%');
            });
        }

        // Sort (new system with whitelist)
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Whitelist sortable columns (security)
        $allowedSorts = ['name', 'category_id', 'price', 'stock', 'created_at'];
        if (! in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        // Pagination with per_page support
        $perPage = $request->input('per_page', 20);
        if (! in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $products = $query->paginate($perPage)->withQueryString();

        // Get categories for filter
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('seller.products.index', compact('products', 'categories', 'sortField', 'sortDirection'));
    }

    /**
     * Show form to create new product.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('seller.products.create', compact('categories'));
    }

    /**
     * Store new product.
     */
    public function store(StoreProductRequest $request)
    {
        Gate::authorize('create', Product::class);

        /** @var Seller $seller */
        $seller = Auth::user()->seller;

        try {
            $validated = $request->validated();

            Log::info('ProductController::store - Starting', [
                'user_id' => Auth::id(),
                'seller_id' => $seller->id,
                'seller_status' => $seller->status,
                'validated_data' => $validated,
            ]);

            $product = $this->productService->createProduct($seller, $validated);

            Log::info('ProductController::store - Product created successfully', [
                'product_id' => $product->id,
                'product_name' => $product->name,
            ]);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                $imagesCount = count($request->file('images'));
                Log::info('ProductController::store - Processing images', ['count' => $imagesCount]);

                $product->addMultipleMediaFromRequest(['images'])
                    ->each(fn ($fileAdder) => $fileAdder->toMediaCollection('product_images'));

                Log::info('ProductController::store - Images uploaded successfully');
            }

            return redirect()->route('seller.products.edit', $product)
                ->with('success', 'Produto criado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('ProductController::store - Validation failed', [
                'errors' => $e->errors(),
            ]);

            return back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', 'Há erros de validação. Verifique os campos destacados.');
        } catch (\Exception $e) {
            Log::error('ProductController::store - Exception caught', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar produto: '.$e->getMessage());
        }
    }

    /**
     * Show form to edit product.
     */
    public function edit(Product $product)
    {
        Gate::authorize('update', $product);

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('seller.products.edit', compact('product', 'categories'));
    }

    /**
     * Update product.
     * Handles both traditional form submission and AJAX requests.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        Gate::authorize('update', $product);

        try {
            $this->productService->updateProduct($product, $request->validated());

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                $product->addMultipleMediaFromRequest(['images'])
                    ->each(fn ($fileAdder) => $fileAdder->toMediaCollection('product_images'));
            }

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produto atualizado com sucesso!',
                    'product' => $product->load('media'),
                ]);
            }

            return back()->with('success', 'Produto atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Product update error:', ['error' => $e->getMessage()]);

            // Return JSON error for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete product (soft delete).
     */
    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);

        try {
            $this->productService->deleteProduct($product);

            return redirect()->route('seller.products.index')
                ->with('success', 'Produto removido com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete product image.
     */
    public function deleteImage(Product $product, Media $media)
    {
        Gate::authorize('update', $product);

        // Verify image belongs to this product
        if ($media->model_id !== $product->id || $media->model_type !== Product::class) {
            abort(403, 'Esta imagem não pertence a este produto.');
        }

        try {
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Imagem removida com sucesso!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover imagem.',
            ], 500);
        }
    }

    /**
     * Publish product.
     */
    public function publish(Product $product)
    {
        Gate::authorize('publish', $product);

        try {
            $this->productService->publishProduct($product);

            return back()->with('success', 'Produto publicado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unpublish product.
     */
    public function unpublish(Product $product)
    {
        Gate::authorize('update', $product);

        try {
            $this->productService->unpublishProduct($product);

            return back()->with('success', 'Produto despublicado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
