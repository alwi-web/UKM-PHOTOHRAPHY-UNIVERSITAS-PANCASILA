<?php

namespace Database\Factories;

use App\Enums\ItemCondition;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('ITM-###'),
            'name' => fake()->randomElement(['Kamera Canon EOS 90D', 'Kamera Sony A6400', 'Lensa 50mm f/1.8', 'Tripod Takara', 'Flash Godox TT600']),
            'category' => fake()->randomElement(['Kamera', 'Lensa', 'Tripod', 'Lighting']),
            'brand' => fake()->company(),
            'quantity' => fake()->numberBetween(1, 5),
            'condition' => ItemCondition::Baik,
        ];
    }
}
