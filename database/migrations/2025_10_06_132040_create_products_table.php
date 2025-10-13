<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->string('short_description', 500)->nullable();
            $table->string('sku', 100)->unique();
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->decimal('weight', 8, 2)->nullable(); // grams
            $table->decimal('width', 8, 2)->nullable(); // cm
            $table->decimal('height', 8, 2)->nullable(); // cm
            $table->decimal('depth', 8, 2)->nullable(); // cm
            $table->boolean('has_variations')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'published', 'out_of_stock', 'inactive'])->default('draft');
            $table->integer('views')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Unique constraints
            $table->unique(['seller_id', 'slug']);

            // Single column indexes
            $table->index('seller_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('status');
            $table->index('is_featured');

            // Composite indexes for common queries
            $table->index(['seller_id', 'status']); // Seller's products by status
            $table->index(['category_id', 'status']); // Category's products by status
            $table->index(['is_featured', 'status']); // Featured products that are active
            $table->index(['status', 'created_at']); // Recent products by status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
