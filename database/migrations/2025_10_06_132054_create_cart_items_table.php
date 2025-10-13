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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->nullable();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Single column indexes
            $table->index('user_id');
            $table->index('session_id');
            $table->index('product_id');

            // Composite indexes for common queries
            $table->index(['user_id', 'product_id']); // Check if user already has product in cart
            $table->index(['session_id', 'product_id']); // Check if guest already has product in cart
            $table->index(['user_id', 'created_at']); // User's recent cart items
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
