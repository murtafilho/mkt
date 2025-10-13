<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SellerPayment>
 */
class SellerPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grossAmount = fake()->randomFloat(2, 100, 2000);
        $mercadopagoFee = $grossAmount * 0.0499; // 4.99% MP fee
        $amountAfterMpFee = $grossAmount - $mercadopagoFee;
        $commissionPercentage = fake()->randomFloat(2, 5, 15);
        $commissionAmount = $amountAfterMpFee * ($commissionPercentage / 100);
        $netAmount = $amountAfterMpFee - $commissionAmount;

        return [
            'seller_id' => Seller::factory()->approved(),
            'order_id' => Order::factory()->paid(),
            'gross_amount' => $grossAmount,
            'mercadopago_fee' => $mercadopagoFee,
            'amount_after_mp_fee' => $amountAfterMpFee,
            'commission_percentage' => $commissionPercentage,
            'commission_amount' => $commissionAmount,
            'net_amount' => $netAmount,
            'mercadopago_split_id' => fake()->uuid(),
            'auto_transferred' => true,
        ];
    }

    /**
     * Indicate that the payment was not auto-transferred.
     */
    public function manualTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_transferred' => false,
        ]);
    }
}
