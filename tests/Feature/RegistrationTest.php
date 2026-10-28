<?php

use App\Enums\RegistrationStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Models\Registration;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Budi Santoso',
        'npm' => '4122000123',
        'faculty' => 'Fakultas Teknik',
        'phone' => '0812-3456-7890',
        'not_cadre_yet' => '1',
    ], $overrides);
}

describe('halaman daftar terpisah', function () {
    it('hanya meminta nama, NPM, fakultas, dan nomor WhatsApp', function () {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Daftar Calon Anggota')
            ->assertSee('Kembali ke website')
            ->assertDontSee('Pendaftaran Kaderisasi')
            ->assertSee('Saya belum pernah mendaftar di PhotoUP.')
            ->assertSee('name="name"', false)
            ->assertSee('name="npm"', false)
            ->assertSee('name="faculty"', false)
            ->assertSee('name="phone"', false)
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="password"', false)
            ->assertDontSee('name="photo"', false);
    });

    it('menampilkan pilihan tujuh fakultas Universitas Pancasila', function () {
        $this->get('/daftar')->assertSeeInOrder([
            'Fakultas Ekonomi &amp; Bisnis',
            'Fakultas Farmasi',
            'Fakultas Hukum',
            'Fakultas Teknik',
            'Fakultas Pariwisata',
            'Fakultas Psikologi',
            'Fakultas Ilmu Komunikasi',
        ], false);

        expect(config('ukm.faculties'))->toHaveCount(7);
    });

    it('menerima fakultas dengan tanda & dan menolak fakultas yang tidak ada di daftar', function () {
        $this->post(route('register'), registrationData(['faculty' => 'Fakultas Ekonomi & Bisnis']))
            ->assertSessionHasNoErrors();

        $this->post(route('register'), registrationData(['npm' => '4122000999', 'faculty' => 'Fakultas Teknologi Industri']))
            ->assertSessionHasErrors('faculty');
    });

    it('formulir daftar dan login tidak memakai teks contoh di dalam isian', function () {
        $this->get('/daftar')->assertDontSee('placeholder=', false);
        $this->get('/login')->assertDontSee('placeholder=', false);
    });

    it('menyimpan pendaftar calon anggota baru', function () {
        $this->post(route('register'), registrationData())
            ->assertRedirect(route('register.show'))
            ->assertSessionHas('registered');

        $this->get('/daftar')
            ->assertSee('Pendaftaran terkirim!')
            ->assertDontSee('id="registerForm"', false);

        $registration = Registration::sole();
        expect($registration->name)->toBe('Budi Santoso')
            ->and($registration->npm)->toBe('4122000123')
            ->and($registration->faculty)->toBe('Fakultas Teknik')
            ->and($registration->phone)->toBe('081234567890')
            ->and($registration->status)->toBe(RegistrationStatus::Baru)
            ->and(User::count())->toBe(0);
    });

    it('kembali ke halaman daftar dengan pesan kesalahan bila isian tidak valid', function () {
        $this->from('/daftar')
            ->post(route('register'), registrationData(['phone' => '123']))
            ->assertRedirect('/daftar')
            ->assertSessionHasErrors('phone');

        $this->followingRedirects()
            ->from('/daftar')
            ->post(route('register'), registrationData(['phone' => '123']))
            ->assertSee('Periksa kembali isian yang ditandai merah.')
            ->assertSee('Nomor WhatsApp tidak valid');
    });

    it('wajib menyatakan belum pernah mendaftar di PhotoUP', function () {
        $this->post(route('register'), registrationData(['not_cadre_yet' => null]))
            ->assertSessionHasErrors(['not_cadre_yet' => 'Pendaftaran ini khusus mahasiswa yang belum pernah mendaftar di PhotoUP.']);

        expect(Registration::count())->toBe(0);
    });

    it('menolak NPM yang sudah menjadi anggota', function () {
        User::factory()->create(['npm' => '4122000123']);

        $this->post(route('register'), registrationData())->assertSessionHasErrors('npm');
    });

    it('menolak NPM yang sudah pernah mendaftar', function () {
        Registration::factory()->create(['npm' => '4122000123']);

        $this->post(route('register'), registrationData())->assertSessionHasErrors('npm');
    });

    it('memvalidasi format isian', function (array $data, string $field) {
        $this->post(route('register'), registrationData($data))->assertSessionHasErrors($field);
    })->with([
        'NPM bukan angka' => [['npm' => '41A2'], 'npm'],
        'nomor WhatsApp tidak valid' => [['phone' => '12345'], 'phone'],
        'fakultas tidak dikenal' => [['faculty' => 'Fakultas Sihir'], 'faculty'],
        'nama kosong' => [['name' => ''], 'name'],
    ]);
});

describe('pengelolaan oleh admin UKM', function () {
    it('menampilkan link WhatsApp dalam format internasional', function () {
        $registration = Registration::factory()->make(['phone' => '081234567890']);

        expect($registration->whatsappUrl())->toBe('https://wa.me/6281234567890');
    });

    it('menandai pendaftar sudah dihubungi', function () {
        loginAs(Role::AdminUkm);
        $registration = Registration::factory()->create();

        livewire(ListRegistrations::class)->callAction(TestAction::make('contacted')->table($registration));

        expect($registration->fresh()->status)->toBe(RegistrationStatus::Dihubungi);
    });

    it('menerima pendaftar dan membuatkan akun member', function () {
        loginAs(Role::AdminUkm);
        $registration = Registration::factory()->create(['name' => 'Sari', 'npm' => '4123000777', 'faculty' => 'Fakultas Hukum']);

        livewire(ListRegistrations::class)
            ->callAction(TestAction::make('accept')->table($registration), data: [
                'email' => 'sari@example.com',
                'password' => 'rahasia123',
            ])
            ->assertHasNoFormErrors();

        $user = User::where('email', 'sari@example.com')->sole();
        expect($user->role)->toBe(Role::Member)
            ->and($user->status)->toBe(UserStatus::Active)
            ->and($user->npm)->toBe('4123000777')
            ->and($user->faculty)->toBe('Fakultas Hukum')
            ->and(Hash::check('rahasia123', $user->password))->toBeTrue()
            ->and($registration->fresh()->status)->toBe(RegistrationStatus::Diterima)
            ->and($registration->fresh()->user_id)->toBe($user->id);
    });

    it('menolak email yang sudah dipakai akun lain saat menerima pendaftar', function () {
        loginAs(Role::AdminUkm);
        User::factory()->create(['email' => 'dipakai@example.com']);
        $registration = Registration::factory()->create();

        livewire(ListRegistrations::class)
            ->callAction(TestAction::make('accept')->table($registration), data: [
                'email' => 'dipakai@example.com',
                'password' => 'rahasia123',
            ])
            ->assertHasFormErrors(['email' => 'Email ini sudah dipakai akun lain.']);

        expect($registration->fresh()->status)->toBe(RegistrationStatus::Baru);
    });

    it('akun hasil penerimaan bisa masuk panel', function () {
        $admin = User::factory()->role(Role::AdminUkm)->create();
        $user = Registration::factory()->create()->accept($admin, 'baru@example.com', 'rahasia123');

        $this->actingAs($user)->get('/admin')->assertOk();
    });

    it('menolak pendaftar dengan catatan', function () {
        loginAs(Role::AdminUkm);
        $registration = Registration::factory()->create();

        livewire(ListRegistrations::class)
            ->callAction(TestAction::make('reject')->table($registration), data: ['notes' => 'Sudah ikut kaderisasi tahun lalu']);

        expect($registration->fresh()->status)->toBe(RegistrationStatus::Ditolak)
            ->and($registration->fresh()->notes)->toBe('Sudah ikut kaderisasi tahun lalu');
    });

    it('pendaftar yang sudah diterima tidak bisa diterima dua kali', function () {
        $admin = User::factory()->role(Role::AdminUkm)->create();
        $registration = Registration::factory()->create();
        $registration->accept($admin, 'satu@example.com', 'rahasia123');

        expect(fn () => $registration->accept($admin, 'dua@example.com', 'rahasia123'))->toThrow(DomainException::class);
    });
});

it('user nonaktif atau belum aktif tidak bisa masuk panel', function () {
    $user = User::factory()->pending()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});
