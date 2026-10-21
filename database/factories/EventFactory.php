<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 week', '+2 months');

        return [
            'title' => 'Hunting Foto '.fake()->city(),
            'type' => fake()->randomElement(EventType::cases()),
            'description' => fake()->paragraph(),
            'location' => fake()->address(),
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+4 hours'),
            'quota' => 30,
            'status' => EventStatus::Published,
            'registration_open' => true,
        ];
    }
}
