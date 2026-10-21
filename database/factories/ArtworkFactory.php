<?php

namespace Database\Factories;

use App\Enums\ArtworkStatus;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artwork>
 */
class ArtworkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'caption' => fake()->sentence(12),
            'image' => 'https://picsum.photos/seed/'.fake()->unique()->word().'/800/'.fake()->randomElement([600, 800, 1000]),
            'status' => ArtworkStatus::Pending,
            'views' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ArtworkStatus::Approved,
            'is_featured' => true,
            'curated_at' => now(),
        ]);
    }
}
