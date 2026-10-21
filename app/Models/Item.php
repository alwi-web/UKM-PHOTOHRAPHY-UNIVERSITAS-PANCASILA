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

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    /** Jumlah unit yang sedang dipinjam (sudah diserahkan, belum kembali). */
    public function borrowedQuantity(?int $exceptLoanId = null): int
    {
        return (int) $this->loanItems()
            ->whereHas('loan', fn ($q) => $q->where('status', LoanStatus::Dipinjam)
                ->when($exceptLoanId, fn ($q) => $q->whereKeyNot($exceptLoanId)))
            ->sum('quantity');
    }

    /** Jumlah unit yang tersedia untuk dipinjam. */
    public function availableQuantity(?int $exceptLoanId = null): int
    {
        return max(0, $this->quantity - $this->borrowedQuantity($exceptLoanId));
    }
}
