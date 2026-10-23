<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * Akun yang bisa dipilih Admin UKM untuk struktur organisasi:
     * anggota PhotoUP yang aktif (bukan anggota UKM lain).
     */
    public static function eligibleMembers(): Builder
    {
        return User::query()
            ->where('status', UserStatus::Active)
            ->whereIn('role', collect(Role::cases())->filter->isPhotoUpMember()->map->value->all())
            ->orderBy('name');
    }

    /** @return array<int, string> pilihan untuk form: "Nama (NPM)" */
    public static function memberOptions(): array
    {
        return static::eligibleMembers()->get()
            ->mapWithKeys(fn (User $u) => [$u->id => $u->npm ? "{$u->name} ({$u->npm})" : $u->name])
            ->all();
    }

    /**
     * Tingkat-tingkat bagan struktur organisasi, dari atas ke bawah:
     * pengurus inti pertama (Ketua Umum), pengurus inti kedua, sisa pengurus inti berjajar,
     * lalu semua divisi di baris paling bawah. Foto, nama, dan NPM diambil dari akun anggota;
     * akun yang sudah tidak aktif atau dihapus tidak ditampilkan.
     *
     * @return array<int, array<int, array{title: string, person: ?array{name: string, npm: ?string, photo: ?string}, note: ?string, members: array<int, array{name: string, npm: ?string, photo: ?string}>}>>
     */
    public function chartLevels(): array
    {
        $core = collect($this->core_members ?? []);
        $divisions = collect($this->divisions ?? []);

        $ids = $core->pluck('user_id')
            ->merge($divisions->pluck('member_ids')->flatten())
            ->filter()
            ->unique();

        $people = static::eligibleMembers()->whereKey($ids)->get()->keyBy('id')
            ->map(fn (User $u) => [
                'name' => $u->name,
                'npm' => $u->npm,
                'photo' => $u->getFilamentAvatarUrl(),
            ]);

        $coreNodes = $core->map(fn (array $m) => [
            'title' => $m['position'],
            'person' => $people->get($m['user_id'] ?? null),
            'note' => null,
            'members' => [],
        ]);

        $divisionNodes = $divisions->map(fn (array $d) => [
            'title' => 'Divisi '.$d['name'],
            'person' => null,
            'note' => $d['description'] ?? null,
            'members' => collect($d['member_ids'] ?? [])
                ->map(fn ($id) => $people->get($id))
                ->filter()
                ->values()
                ->all(),
        ]);

        return array_values(array_filter([
            $coreNodes->take(1)->values()->all(),
            $coreNodes->slice(1, 1)->values()->all(),
            $coreNodes->slice(2)->values()->all(),
            $divisionNodes->all(),
        ]));
    }
}
