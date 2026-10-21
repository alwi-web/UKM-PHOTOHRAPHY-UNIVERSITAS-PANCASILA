<?php

namespace App\Models;

use App\Enums\ItemCondition;
use App\Enums\LoanStatus;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'user_id', 'quantity', 'purpose', 'status', 'borrowed_at', 'due_at',
        'returned_at', 'condition_before', 'condition_after', 'condition_notes', 'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'condition_before' => ItemCondition::class,
            'condition_after' => ItemCondition::class,
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'quantity' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /** Admin inventaris menyerahkan barang ke peminjam. */
    public function handOver(User $admin, ItemCondition $conditionBefore): void
    {
        if ($this->status !== LoanStatus::Diajukan) {
            throw new DomainException('Hanya peminjaman berstatus "Diajukan" yang bisa diserahkan.');
        }

        if ($this->quantity > $this->item->availableQuantity()) {
            throw new DomainException('Stok barang tidak mencukupi.');
        }

        $this->update([
            'status' => LoanStatus::Dipinjam,
            'borrowed_at' => now(),
            'condition_before' => $conditionBefore,
            'handled_by' => $admin->id,
        ]);
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        if ($this->status !== LoanStatus::Diajukan) {
            throw new DomainException('Hanya peminjaman berstatus "Diajukan" yang bisa ditolak.');
        }

        $this->update([
            'status' => LoanStatus::Ditolak,
            'condition_notes' => $notes,
            'handled_by' => $admin->id,
        ]);
    }

    /** Barang dikembalikan; kondisi barang di inventaris ikut diperbarui. */
    public function markReturned(User $admin, ItemCondition $conditionAfter, ?string $notes = null): void
    {
        if ($this->status !== LoanStatus::Dipinjam) {
            throw new DomainException('Hanya barang yang sedang dipinjam yang bisa dikembalikan.');
        }

        $this->update([
            'status' => LoanStatus::Dikembalikan,
            'returned_at' => now(),
            'condition_after' => $conditionAfter,
            'condition_notes' => $notes,
            'handled_by' => $admin->id,
        ]);

        if ($conditionAfter !== ItemCondition::Baik) {
            $this->item->update(['condition' => $conditionAfter]);
        }
    }
}
