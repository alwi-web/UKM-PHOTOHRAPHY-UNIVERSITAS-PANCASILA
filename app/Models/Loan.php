<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'borrower_name', 'borrower_npm', 'borrower_unit', 'purpose', 'status',
        'borrowed_at', 'due_at', 'returned_at', 'notes', 'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === LoanStatus::Dipinjam && $this->due_at->isPast();
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->where('status', LoanStatus::Dipinjam)->where('due_at', '<', now());
    }

    /** Ringkasan barang, contoh: "Canon EOS 90D (1), Tripod (2)". */
    public function itemSummary(): string
    {
        return $this->loanItems->map(fn (LoanItem $li) => "{$li->item->name} ({$li->quantity})")->join(', ');
    }

    /**
     * Admin inventaris menyerahkan barang ke peminjam.
     *
     * @param  array<int, ItemCondition|string>  $conditions  kondisi awal per id loan_item
     */
    public function handOver(User $admin, array $conditions = []): void
    {
        if ($this->status !== LoanStatus::Diajukan) {
            throw new DomainException('Hanya peminjaman berstatus "Diajukan" yang bisa diserahkan.');
        }

        DB::transaction(function () use ($admin, $conditions) {
            foreach ($this->loanItems as $loanItem) {
                if ($loanItem->quantity > $loanItem->item->availableQuantity()) {
                    throw new DomainException("Stok {$loanItem->item->name} tidak mencukupi.");
                }

                $loanItem->update([
                    'condition_before' => $conditions[$loanItem->id] ?? $loanItem->item->condition,
                ]);
            }

            $this->update([
                'status' => LoanStatus::Dipinjam,
                'handled_by' => $admin->id,
            ]);
        });
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        if ($this->status !== LoanStatus::Diajukan) {
            throw new DomainException('Hanya peminjaman berstatus "Diajukan" yang bisa ditolak.');
        }

        $this->update([
            'status' => LoanStatus::Ditolak,
            'notes' => $notes,
            'handled_by' => $admin->id,
        ]);
    }

    /**
     * Barang dikembalikan; kondisi barang di inventaris ikut diperbarui jika berubah.
     *
     * @param  array<int, ItemCondition|string>  $conditions  kondisi akhir per id loan_item
     */
    public function markReturned(User $admin, array $conditions = [], ?string $notes = null): void
    {
        if ($this->status !== LoanStatus::Dipinjam) {
            throw new DomainException('Hanya barang yang sedang dipinjam yang bisa dikembalikan.');
        }

        DB::transaction(function () use ($admin, $conditions, $notes) {
            foreach ($this->loanItems as $loanItem) {
                $after = ItemCondition::from($this->conditionValue($conditions[$loanItem->id] ?? ItemCondition::Baik));
                $loanItem->update(['condition_after' => $after]);

                if ($after !== ItemCondition::Baik) {
                    $loanItem->item->update(['condition' => $after]);
                }
            }

            $this->update([
                'status' => LoanStatus::Dikembalikan,
                'returned_at' => now(),
                'notes' => $notes,
                'handled_by' => $admin->id,
            ]);
        });
    }

    private function conditionValue(ItemCondition|string $condition): string
    {
        return $condition instanceof ItemCondition ? $condition->value : $condition;
    }
}
