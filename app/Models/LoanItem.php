<?php

namespace App\Models;

use App\Enums\ItemCondition;
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

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
