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
        Schema::table('payments', function (Blueprint $table) {
            // Add installments field
            $table->integer('installments')->default(1)->after('amount');

            // Add fee and net amount fields
            $table->decimal('fee_amount', 10, 2)->default(0.00)->after('installments');
            $table->decimal('net_amount', 10, 2)->nullable()->after('fee_amount');

            // Add payment type (credit_card, debit_card, pix, boleto, etc)
            $table->string('payment_type', 50)->nullable()->after('payment_method');

            // Add indexes
            $table->index('payment_method');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['payment_type']);
            $table->dropColumn(['installments', 'fee_amount', 'net_amount', 'payment_type']);
        });
    }
};
