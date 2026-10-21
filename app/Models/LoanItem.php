<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanItem extends Model
{
    protected $fillable = ['loan_id', 'item_id', 'quantity', 'condition_before', 'condition_after'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'condition_before' => ItemCondition::class,
            'condition_after' => ItemCondition::class,
        ];
    }

    /**
     * Baris peminjaman yang memakai barang pada rentang [from, to).
     * Yang dihitung: pengajuan yang belum diproses dan barang yang sedang dipinjam.
     * Barang yang terlambat dikembalikan dianggap tetap terpakai sampai benar-benar kembali.
     */
    public function scopeOverlapping(Builder $query, CarbonInterface $from, CarbonInterface $to, ?int $exceptLoanId = null): void
    {
        $query->whereHas('loan', fn (Builder $loan) => $loan
            ->whereIn('status', [LoanStatus::Diajukan, LoanStatus::Dipinjam])
            ->when($exceptLoanId, fn (Builder $q) => $q->whereKeyNot($exceptLoanId))
            ->where('borrowed_at', '<', $to)
            ->where(fn (Builder $q) => $q
                ->where('due_at', '>', $from)
                ->orWhere(fn (Builder $overdue) => $overdue
                    ->where('status', LoanStatus::Dipinjam)
                    ->where('due_at', '<', now()))));
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
