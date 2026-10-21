<?php

namespace App\Models;

use App\Enums\ArtworkStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'caption', 'image', 'instagram_url', 'tiktok_url',
        'status', 'is_featured', 'curator_note', 'curated_by', 'curated_at', 'views',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArtworkStatus::class,
            'is_featured' => 'boolean',
            'curated_at' => 'datetime',
            'views' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function curator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'curated_by');
    }

    /** Karya yang tampil di website: lolos kurasi dan dipilih kurator. */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ArtworkStatus::Approved)->where('is_featured', true);
    }

    public function imageUrl(): string
    {
        return str_starts_with($this->image, 'http') ? $this->image : Storage::disk('public')->url($this->image);
    }

    public function curate(User $curator, ArtworkStatus $status, bool $featured = false, ?string $note = null): void
    {
        $this->update([
            'status' => $status,
            'is_featured' => $status === ArtworkStatus::Approved && $featured,
            'curator_note' => $note,
            'curated_by' => $curator->id,
            'curated_at' => now(),
        ]);
    }
}
