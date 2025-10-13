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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('seller_id')->constrained()->onDelete('restrict');
            $table->string('order_number', 50)->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['awaiting_payment', 'paid', 'preparing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('awaiting_payment');
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('mercadopago_preference_id')->nullable();
            $table->string('mercadopago_payment_id')->nullable();
            $table->string('mercadopago_status', 50)->nullable();
            $table->json('mercadopago_details')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Single column indexes
            $table->index('user_id');
            $table->index('seller_id');
            $table->index('order_number');
            $table->index('status');
            $table->index('mercadopago_payment_id');

            // Composite indexes for common queries
            $table->index(['user_id', 'status']); // User's orders by status
            $table->index(['seller_id', 'status']); // Seller's orders by status
            $table->index(['seller_id', 'status', 'created_at']); // Seller's orders by status and date
            $table->index(['status', 'created_at']); // All orders by status and date (admin reports)
            $table->index(['user_id', 'created_at']); // User's recent orders
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
