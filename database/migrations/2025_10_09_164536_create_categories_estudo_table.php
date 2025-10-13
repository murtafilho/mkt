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
        Schema::create('categories_estudo', function (Blueprint $table) {
            $table->id();
            $table->integer('original_id')->nullable()->comment('ID original do banco valedosol_db');
            $table->foreignId('parent_id')->nullable()->constrained('categories_estudo')->onDelete('set null');
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('source', 50)->default('valedosol_db')->comment('Origem dos dados');
            $table->timestamps();

            // Indexes
            $table->index('original_id');
            $table->index('slug');
            $table->index('parent_id');
            $table->index('is_active');
            $table->index('source');
            $table->index(['parent_id', 'is_active']);
            $table->index(['is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_estudo');
    }
};
