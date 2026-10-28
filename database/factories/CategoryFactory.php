<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Landscape', 'Portrait', 'Human Interest', 'Street', 'Arsitektur', 'Makro', 'Event']);

        return ['name' => $name, 'slug' => str($name)->slug()];
    }
}
