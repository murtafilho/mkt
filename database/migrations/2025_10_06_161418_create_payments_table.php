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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method'); // mercadopago, pix, boleto, etc
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            $table->string('external_payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable(); // Store MP response
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('external_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
