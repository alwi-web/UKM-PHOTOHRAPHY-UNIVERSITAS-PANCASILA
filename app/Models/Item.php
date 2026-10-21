<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'category', 'brand', 'description', 'quantity', 'condition', 'photo'];

    protected function casts(): array
    {
        return [
            'condition' => ItemCondition::class,
            'quantity' => 'integer',
        ];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->loans()->where('status', LoanStatus::Dipinjam);
    }

    /** Jumlah unit yang sedang dipinjam. */
    public function borrowedQuantity(?int $exceptLoanId = null): int
    {
        return (int) $this->activeLoans()
            ->when($exceptLoanId, fn ($q) => $q->whereKeyNot($exceptLoanId))
            ->sum('quantity');
    }

    /** Jumlah unit yang tersedia untuk dipinjam. */
    public function availableQuantity(?int $exceptLoanId = null): int
    {
        return max(0, $this->quantity - $this->borrowedQuantity($exceptLoanId));
    }
}
