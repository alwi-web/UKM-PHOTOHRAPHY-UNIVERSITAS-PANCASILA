<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** Profil UKM (sejarah, visi & misi, struktur organisasi, kontak). Hanya ada satu baris. */
class OrganizationProfile extends Model
{
    protected $fillable = [
        'name', 'tagline', 'about', 'history', 'vision', 'missions', 'core_members', 'divisions',
        'address', 'email', 'instagram', 'tiktok', 'phone', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'missions' => 'array',
            'core_members' => 'array',
            'divisions' => 'array',
        ];
    }

    /** Ambil profil; dibuat dari isi awal di config/ukm.php jika belum ada. */
    public static function current(): self
    {
        return static::query()->first() ?? static::create(config('ukm.profile_defaults'));
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return array<int, string> paragraf sejarah */
    public function historyParagraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', (string) $this->history))));
    }

    public static function photoUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
