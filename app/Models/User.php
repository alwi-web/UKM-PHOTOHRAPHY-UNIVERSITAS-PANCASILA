<?php

namespace App\Models;

use App\Enums\Role;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'npm',
        'email',
        'phone',
        'faculty',
        'batch_year',
        'origin_ukm',
        'photo',
        'role',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'status' => UserStatus::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdminUkm(): bool
    {
        return $this->role === Role::AdminUkm;
    }

    /** Fakultas, atau asal UKM untuk anggota UKM lain. */
    public function unit(): ?string
    {
        return $this->role === Role::AnggotaLain ? ($this->origin_ukm ?: $this->faculty) : $this->faculty;
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)->withPivot('status')->withTimestamps();
    }
}
