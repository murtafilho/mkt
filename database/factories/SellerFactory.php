<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seller>
 */
class SellerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $personType = fake()->randomElement(['individual', 'business']);
        $storeName = fake()->company();

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => str()->slug($storeName).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->optional()->paragraph(),
            'document_number' => $personType === 'individual' ? $this->generateCPF() : $this->generateCNPJ(),
            'person_type' => $personType,
            'company_name' => $personType === 'business' ? fake()->company() : null,
            'trade_name' => $personType === 'business' ? fake()->optional()->company() : null,
            'state_registration' => $personType === 'business' ? fake()->optional()->numerify('###########') : null,
            'business_phone' => fake()->phoneNumber(),
            'business_email' => fake()->companyEmail(),
            'commission_percentage' => fake()->randomFloat(2, 5, 15),
            'mercadopago_account_id' => fake()->optional()->uuid(),
            'status' => 'pending',
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the seller is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'approved_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the seller is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the seller is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the seller is a business.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'person_type' => 'business',
            'document_number' => $this->generateCNPJ(),
            'company_name' => fake()->company(),
            'trade_name' => fake()->optional()->company(),
            'state_registration' => fake()->numerify('###########'),
        ]);
    }

    /**
     * Indicate that the seller is an individual.
     */
    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'person_type' => 'individual',
            'document_number' => $this->generateCPF(),
            'company_name' => null,
            'trade_name' => null,
            'state_registration' => null,
        ]);
    }

    /**
     * Generate a valid CPF number with correct check digits.
     */
    private function generateCPF(): string
    {
        // Generate first 9 digits
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = fake()->numberBetween(0, 9);
        }

        // Calculate first check digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $n[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $n[9] = ($remainder < 2) ? 0 : 11 - $remainder;

        // Calculate second check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $n[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $n[10] = ($remainder < 2) ? 0 : 11 - $remainder;

        // Format CPF
        return sprintf(
            '%d%d%d.%d%d%d.%d%d%d-%d%d',
            $n[0], $n[1], $n[2], $n[3], $n[4], $n[5], $n[6], $n[7], $n[8], $n[9], $n[10]
        );
    }

    /**
     * Generate a valid CNPJ number with correct check digits.
     */
    private function generateCNPJ(): string
    {
        // Generate first 12 digits (8 digits + 4 digits for branch)
        $n = [];
        for ($i = 0; $i < 8; $i++) {
            $n[$i] = fake()->numberBetween(0, 9);
        }
        // Branch number (0001)
        $n[8] = 0;
        $n[9] = 0;
        $n[10] = 0;
        $n[11] = 1;

        // Calculate first check digit
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $n[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $n[12] = ($remainder < 2) ? 0 : 11 - $remainder;

        // Calculate second check digit
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $n[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $n[13] = ($remainder < 2) ? 0 : 11 - $remainder;

        // Format CNPJ
        return sprintf(
            '%d%d.%d%d%d.%d%d%d/%d%d%d%d-%d%d',
            $n[0], $n[1], $n[2], $n[3], $n[4], $n[5], $n[6], $n[7],
            $n[8], $n[9], $n[10], $n[11], $n[12], $n[13]
        );
    }
}
