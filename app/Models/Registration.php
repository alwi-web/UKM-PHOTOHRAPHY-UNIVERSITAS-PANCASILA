<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'npm', 'faculty', 'phone', 'status', 'notes', 'user_id', 'processed_by'];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** Link chat WhatsApp, nomor 08xx diubah ke format internasional 628xx. */
    public function whatsappUrl(): string
    {
        $number = preg_replace('/\D/', '', $this->phone);
        $number = preg_replace('/^0/', '62', $number);

        return "https://wa.me/{$number}";
    }

    public function markContacted(User $admin): void
    {
        $this->update(['status' => RegistrationStatus::Dihubungi, 'processed_by' => $admin->id]);
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        $this->update(['status' => RegistrationStatus::Ditolak, 'notes' => $notes, 'processed_by' => $admin->id]);
    }

    /** Pendaftar lulus kaderisasi: dibuatkan akun Member UKM Fotografi. */
    public function accept(User $admin, string $email, string $password): User
    {
        if ($this->status === RegistrationStatus::Diterima) {
            throw new DomainException('Pendaftar ini sudah diterima.');
        }

        return DB::transaction(function () use ($admin, $email, $password) {
            $user = User::create([
                'name' => $this->name,
                'npm' => $this->npm,
                'faculty' => $this->faculty,
                'phone' => $this->phone,
                'email' => $email,
                'password' => $password,
                'role' => Role::Member,
                'status' => UserStatus::Active,
            ]);

            $this->update([
                'status' => RegistrationStatus::Diterima,
                'user_id' => $user->id,
                'processed_by' => $admin->id,
            ]);

            return $user;
        });
    }
}
