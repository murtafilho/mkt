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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf_cnpj', 14)->nullable()->unique()->after('email');
            $table->string('phone', 15)->nullable()->after('cpf_cnpj');
            $table->date('birth_date')->nullable()->after('phone');

            $table->index('cpf_cnpj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['cpf_cnpj']);
            $table->dropColumn(['cpf_cnpj', 'phone', 'birth_date']);
        });
    }
};
