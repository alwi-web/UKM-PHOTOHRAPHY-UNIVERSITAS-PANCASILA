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

    /**
     * Tingkat-tingkat bagan struktur organisasi, dari atas ke bawah:
     * pengurus inti pertama (Ketua Umum), pengurus inti kedua, sisa pengurus inti berjajar,
     * lalu semua divisi di baris paling bawah.
     *
     * @return array<int, array<int, array{title: string, name: ?string, photo: ?string, note: ?string, members: array<int, array{name: string, npm: ?string, photo: ?string}>}>>
     */
    public function chartLevels(): array
    {
        $core = collect($this->core_members ?? [])->map(fn (array $m) => [
            'title' => $m['position'],
            'name' => $m['name'] ?? null,
            'photo' => static::photoUrl($m['photo'] ?? null),
            'note' => null,
            'members' => [],
        ]);

        $divisions = collect($this->divisions ?? [])->map(fn (array $d) => [
            'title' => 'Divisi '.$d['name'],
            'name' => null,
            'photo' => null,
            'note' => $d['description'] ?? null,
            'members' => collect($d['members'] ?? [])->map(fn (array $member) => [
                'name' => $member['name'],
                'npm' => $member['npm'] ?? null,
                'photo' => static::photoUrl($member['photo'] ?? null),
            ])->values()->all(),
        ]);

        return array_values(array_filter([
            $core->take(1)->values()->all(),
            $core->slice(1, 1)->values()->all(),
            $core->slice(2)->values()->all(),
            $divisions->all(),
        ]));
    }

    public static function photoUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
