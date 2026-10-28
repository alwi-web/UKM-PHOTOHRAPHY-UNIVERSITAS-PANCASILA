<?php

namespace App\Models;

use App\Enums\ArtworkStatus;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'caption', 'aperture', 'shutter_speed', 'iso', 'exposure_notes', 'image', 'instagram_url', 'tiktok_url',
        'status', 'is_featured', 'nominated_week', 'curator_note', 'curated_by', 'curated_at', 'views',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArtworkStatus::class,
            'is_featured' => 'boolean',
            'nominated_week' => 'date',
            'curated_at' => 'datetime',
            'views' => 'integer',
            'iso' => 'integer',
        ];
    }

    /** Ringkasan segitiga exposure, contoh: "f/1.8 · 1/250s · ISO 100". */
    public function exposureSummary(): ?string
    {
        $parts = array_filter([
            $this->aperture,
            $this->shutter_speed ? rtrim($this->shutter_speed, 's').'s' : null,
            $this->iso ? "ISO {$this->iso}" : null,
        ]);

        return $parts ? implode(' · ', $parts) : null;
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
        $shown = $status === ArtworkStatus::Approved && $featured;

        $this->update([
            'status' => $status,
            'is_featured' => $shown,
            // karya yang ditolak atau tidak tampil di website tidak bisa tetap jadi nominasi
            'nominated_week' => $shown ? $this->nominated_week : null,
            'curator_note' => $note,
            'curated_by' => $curator->id,
            'curated_at' => now(),
        ]);
    }

    /** Senin dari minggu tanggal tersebut (minggu dihitung Senin–Minggu). */
    public static function weekOf(?CarbonInterface $date = null): CarbonInterface
    {
        return ($date ?? now())->copy()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();
    }

    public function isNominatedThisWeek(): bool
    {
        return $this->nominated_week?->isSameDay(static::weekOf()) ?? false;
    }

    /** Nominasikan sebagai foto mingguan. Karya otomatis ikut tampil di galeri website. */
    public function nominate(?CarbonInterface $week = null): void
    {
        if ($this->status !== ArtworkStatus::Approved) {
            throw new DomainException('Hanya karya yang lolos kurasi yang bisa dinominasikan.');
        }

        $this->update(['nominated_week' => static::weekOf($week), 'is_featured' => true]);
    }

    public function cancelNomination(): void
    {
        $this->update(['nominated_week' => null]);
    }

    /**
     * Nominasi foto mingguan untuk website: nominasi minggu ini, atau minggu terakhir
     * yang punya nominasi supaya carousel tidak kosong di awal minggu.
     *
     * @return array{week: ?CarbonInterface, artworks: Collection<int, Artwork>}
     */
    public static function weeklyNominees(): array
    {
        $week = static::published()
            ->whereNotNull('nominated_week')
            ->whereDate('nominated_week', '<=', static::weekOf()->toDateString())
            ->max('nominated_week');

        if (! $week) {
            return ['week' => null, 'artworks' => new Collection];
        }

        $week = static::weekOf(Carbon::parse($week));

        return [
            'week' => $week,
            'artworks' => static::published()->with(['user', 'category'])
                ->whereDate('nominated_week', $week->toDateString())
                ->latest('curated_at')
                ->get(),
        ];
    }
}
