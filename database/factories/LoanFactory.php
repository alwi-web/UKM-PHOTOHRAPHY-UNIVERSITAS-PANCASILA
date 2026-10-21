<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Item;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'user_id' => User::factory(),
            'quantity' => 1,
            'purpose' => fake()->sentence(),
            'status' => LoanStatus::Diajukan,
            'due_at' => now()->addDays(3),
        ];
    }

    public function borrowed(): static
    {
        return $this->state(fn () => [
            'status' => LoanStatus::Dipinjam,
            'borrowed_at' => now()->subDay(),
            'condition_before' => 'baik',
        ]);
    }
}
