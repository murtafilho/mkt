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
        Schema::create('seller_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('restrict');
            $table->foreignId('order_id')->constrained()->onDelete('restrict');
            $table->decimal('gross_amount', 10, 2);
            $table->decimal('mercadopago_fee', 10, 2);
            $table->decimal('amount_after_mp_fee', 10, 2);
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->string('mercadopago_split_id')->nullable();
            $table->boolean('auto_transferred')->default(true);
            $table->timestamps();

            $table->index('seller_id');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_payments');
    }
};
