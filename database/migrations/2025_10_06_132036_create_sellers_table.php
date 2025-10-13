<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('document_number', 20)->unique(); // CPF or CNPJ
            $table->enum('person_type', ['individual', 'business']); // fisica, juridica
            $table->string('company_name')->nullable(); // razao_social
            $table->string('trade_name')->nullable(); // nome_fantasia
            $table->string('state_registration', 20)->nullable(); // inscricao_estadual
            $table->string('business_phone', 20);
            $table->string('business_email');
            $table->decimal('commission_percentage', 5, 2)->default(10.00);
            $table->string('mercadopago_account_id')->nullable();
            $table->enum('status', ['pending', 'active', 'inactive', 'suspended'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('status');
            $table->index('document_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
