<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Budi Santoso',
        'npm' => '4122000123',
        'email' => 'budi@example.com',
        'phone' => '081234567890',
        'faculty' => 'Fakultas Teknik',
        'batch_year' => now()->year,
        'role' => 'member',
        'photo' => UploadedFile::fake()->image('budi.jpg'),
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'join_reason' => 'Ingin belajar fotografi',
    ], $overrides);
}

it('menyimpan pendaftar baru dengan status menunggu verifikasi', function () {
    $this->post(route('register'), registrationData())
        ->assertRedirect(route('home').'#daftar')
        ->assertSessionHas('registered');

    $user = User::where('npm', '4122000123')->firstOrFail();

    expect($user->status)->toBe(UserStatus::Pending)
        ->and($user->role)->toBe(Role::Member);
    Storage::disk('public')->assertExists($user->photo);
});

it('menolak NPM yang sudah terdaftar', function () {
    User::factory()->create(['npm' => '4122000123']);

    $this->post(route('register'), registrationData())->assertSessionHasErrors('npm');
});

it('mewajibkan asal UKM untuk anggota UKM lain', function () {
    $this->post(route('register'), registrationData(['role' => 'anggota_lain']))
        ->assertSessionHasErrors('origin_ukm');
});

it('tidak mengizinkan mendaftar sendiri sebagai admin', function () {
    $this->post(route('register'), registrationData(['role' => 'admin_ukm']))
        ->assertSessionHasErrors('role');

    expect(User::count())->toBe(0);
});

it('pendaftar yang belum disetujui tidak bisa masuk panel', function () {
    $user = User::factory()->pending()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('anggota aktif bisa masuk panel', function () {
    loginAs(Role::Member);

    $this->get('/admin')->assertOk();
});
