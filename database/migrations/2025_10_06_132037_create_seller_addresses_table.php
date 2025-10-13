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
        Schema::create('seller_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['business', 'return']); // comercial, devolucao
            $table->string('postal_code', 9); // CEP
            $table->string('street');
            $table->string('number', 10);
            $table->string('complement', 100)->nullable();
            $table->string('neighborhood', 100);
            $table->string('city', 100);
            $table->char('state', 2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('seller_id');
            $table->index(['seller_id', 'type']); // Get business or return address for seller
            $table->index(['seller_id', 'is_default']); // Get default address for seller
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_addresses');
    }
};
