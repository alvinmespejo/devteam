<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->numberBetween(1, 1000),
            'name' => fake()->name(),
            'category' => fake()->words(3, true),
            'description' => fake()->text(),
            'image' => fake()->imageUrl(),
            'price' => fake()->randomFloat(2, 10, 500),
            'rating' => fake()->randomFloat(2, 1, 5),
            'count' => fake()->numberBetween(500, 1000)
        ];
    }
}
