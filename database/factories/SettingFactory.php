<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['string', 'integer', 'boolean', 'json']);
        $key = fake()->unique()->words(2, true);

        return [
            'key' => str_replace(' ', '_', $key),
            'value' => $this->generateValueByType($type),
            'type' => $type,
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Generate a value based on the type.
     */
    private function generateValueByType(string $type): string
    {
        return match ($type) {
            'string' => fake()->sentence(),
            'integer' => (string) fake()->numberBetween(1, 100),
            'boolean' => fake()->boolean() ? 'true' : 'false',
            'json' => json_encode(['key' => fake()->word(), 'value' => fake()->word()]),
            default => fake()->sentence(),
        };
    }
}
