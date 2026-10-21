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
            'user_id' => User::factory(),
            'borrower_name' => fake()->name(),
            'borrower_npm' => fake()->numerify('41########'),
            'borrower_unit' => 'UKM Jurnalistik',
            'purpose' => fake()->sentence(),
            'status' => LoanStatus::Diajukan,
            'borrowed_at' => now()->addDay(),
            'due_at' => now()->addDays(3),
        ];
    }

    public function borrowed(): static
    {
        return $this->state(fn () => [
            'status' => LoanStatus::Dipinjam,
            'borrowed_at' => now()->subDay(),
        ]);
    }

    /** Tambahkan satu barang ke peminjaman. */
    public function withItem(?Item $item = null, int $quantity = 1): static
    {
        return $this->afterCreating(fn (Loan $loan) => $loan->loanItems()->create([
            'item_id' => ($item ?? Item::factory()->create())->id,
            'quantity' => $quantity,
        ]));
    }
}
