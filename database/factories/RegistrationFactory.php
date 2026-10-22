<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'npm' => fake()->unique()->numerify('41########'),
            'faculty' => fake()->randomElement(config('ukm.faculties')),
            'phone' => fake()->numerify('08##########'),
            'status' => RegistrationStatus::Baru,
        ];
    }
}
