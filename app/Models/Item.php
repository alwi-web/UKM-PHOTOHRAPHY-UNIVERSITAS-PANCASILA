<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
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

    /** Jumlah unit yang tersedia saat ini (fisik ada di sekretariat). */
    public function availableQuantity(?int $exceptLoanId = null): int
    {
        return max(0, $this->quantity - $this->borrowedQuantity($exceptLoanId));
    }

    /** Jumlah unit yang sudah dipesan/dipinjam orang lain pada rentang tanggal tersebut. */
    public function reservedQuantityBetween(CarbonInterface $from, CarbonInterface $to, ?int $exceptLoanId = null): int
    {
        return (int) $this->loanItems()->overlapping($from, $to, $exceptLoanId)->sum('quantity');
    }

    /** Jumlah unit yang masih bisa dipinjam pada rentang tanggal tersebut. */
    public function availableQuantityBetween(CarbonInterface $from, CarbonInterface $to, ?int $exceptLoanId = null): int
    {
        return max(0, $this->quantity - $this->reservedQuantityBetween($from, $to, $exceptLoanId));
    }

    /**
     * Peminjaman lain yang bentrok dengan rentang tanggal tersebut.
     *
     * @return Collection<int, Loan>
     */
    public function conflictingLoans(CarbonInterface $from, CarbonInterface $to, ?int $exceptLoanId = null): Collection
    {
        return Loan::query()
            ->whereHas('loanItems', fn ($q) => $q->where('item_id', $this->id)->overlapping($from, $to, $exceptLoanId))
            ->with(['loanItems' => fn ($q) => $q->where('item_id', $this->id)])
            ->orderBy('borrowed_at')
            ->get();
    }
}
