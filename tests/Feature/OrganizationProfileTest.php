<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Filament\Pages\ManageOrganizationProfile;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

it('membuat profil dari isi awal saat pertama kali dibuka', function () {
    $profile = OrganizationProfile::current();

    expect($profile->name)->toBe('PhotoUP 94')
        ->and(collect($profile->core_members)->pluck('position')->all())->toBe(['Ketua Umum', 'Wakil Ketua', 'Sekretaris', 'Bendahara'])
        ->and(OrganizationProfile::count())->toBe(1);

    OrganizationProfile::current();
    expect(OrganizationProfile::count())->toBe(1);
});

it('admin UKM mengubah sejarah, visi misi, dan memilih pengurus dari akun anggota', function () {
    $admin = loginAs(Role::AdminUkm);
    $ketua = User::factory()->create(['name' => 'Rizky Pratama', 'npm' => '4122000001']);
    [$dimas, $intan] = User::factory(2)->create();

    livewire(ManageOrganizationProfile::class)
        ->fillForm([
            'name' => 'PhotoUP 94',
            'history' => "PhotoUP 94 berdiri pada tahun 1994.\n\nSejak itu rutin mengadakan pameran tahunan.",
            'vision' => 'Menjadi komunitas fotografi terbaik.',
            // repeater "simple" menyimpan state internal sebagai item {mission: ...}
            'missions' => [['mission' => 'Mengadakan kelas rutin.'], ['mission' => 'Mengadakan pameran tahunan.']],
            'core_members' => [
                ['position' => 'Ketua Umum', 'user_id' => $ketua->id],
                ['position' => 'Wakil Ketua', 'user_id' => null],
            ],
            'divisions' => [
                // anggota divisi ditambahkan satu per satu lewat "Tambah anggota"
                ['name' => 'Hunting', 'description' => 'Hunting foto setiap bulan.', 'member_ids' => [['user_id' => $dimas->id], ['user_id' => $intan->id]]],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified('Profil UKM disimpan.');

    $profile = OrganizationProfile::sole();
    expect($profile->historyParagraphs())->toBe([
        'PhotoUP 94 berdiri pada tahun 1994.',
        'Sejak itu rutin mengadakan pameran tahunan.',
    ])
        ->and($profile->missions)->toBe(['Mengadakan kelas rutin.', 'Mengadakan pameran tahunan.'])
        ->and($profile->core_members[0]['user_id'])->toBe($ketua->id)
        ->and(array_map('intval', $profile->divisions[0]['member_ids']))->toBe([$dimas->id, $intan->id])
        ->and($profile->updated_by)->toBe($admin->id);
});

it('anggota yang sama tidak bisa ditambahkan dua kali ke satu divisi', function () {
    loginAs(Role::AdminUkm);
    $dimas = User::factory()->create();

    [$intan] = User::factory(1)->create();
    $form = fn (array $memberIds) => [
        'name' => 'PhotoUP 94',
        'history' => 'Sejarah.',
        'vision' => 'Visi.',
        'missions' => [['mission' => 'Misi.']],
        'divisions' => [['name' => 'Hunting', 'description' => null, 'member_ids' => array_map(fn ($id) => ['user_id' => $id], $memberIds)]],
    ];

    livewire(ManageOrganizationProfile::class)
        ->fillForm($form([$dimas->id, $dimas->id]))
        ->call('save')
        ->assertHasFormErrors()
        ->assertNotNotified('Profil UKM disimpan.');

    livewire(ManageOrganizationProfile::class)
        ->fillForm($form([$dimas->id, $intan->id]))
        ->call('save')
        ->assertHasNoFormErrors();
});

it('foto, nama, dan NPM di bagan diambil dari akun anggota', function () {
    Storage::fake('public');
    $wakil = User::factory()->create([
        'name' => 'Putri Ramadhani',
        'npm' => '4122000002',
        'photo' => UploadedFile::fake()->image('putri.jpg')->store('users', 'public'),
    ]);
    $dimas = User::factory()->create(['name' => 'Dimas', 'npm' => '4123000111']);
    $intan = User::factory()->create(['name' => 'Intan', 'npm' => '4123000222']);

    OrganizationProfile::current()->update([
        'core_members' => [
            ['position' => 'Ketua Umum', 'user_id' => null],
            ['position' => 'Wakil Ketua', 'user_id' => $wakil->id],
        ],
        'divisions' => [['name' => 'Hunting', 'description' => null, 'member_ids' => [$dimas->id, $intan->id]]],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Ketua Umum', 'Wakil Ketua', 'Putri Ramadhani', '4122000002'])
        ->assertSee(Storage::disk('public')->url($wakil->photo))
        ->assertSeeInOrder(['Divisi Hunting', 'Dimas', '4123000111', 'Intan', '4123000222']);

    // perubahan di akun anggota langsung terbawa ke bagan
    $wakil->update(['name' => 'Putri R.', 'npm' => '4122000099']);

    $this->get('/')->assertSeeInOrder(['Wakil Ketua', 'Putri R.', '4122000099']);
});

it('akun yang nonaktif atau sudah dihapus tidak tampil di bagan', function () {
    $nonaktif = User::factory()->create(['name' => 'Akun Nonaktif', 'status' => UserStatus::Inactive]);
    $dihapus = User::factory()->create(['name' => 'Akun Dihapus']);
    $aktif = User::factory()->create(['name' => 'Akun Aktif']);

    OrganizationProfile::current()->update([
        'core_members' => [['position' => 'Ketua Umum', 'user_id' => $nonaktif->id]],
        'divisions' => [['name' => 'Pameran', 'description' => null, 'member_ids' => [$dihapus->id, $aktif->id]]],
    ]);
    $dihapus->delete();

    $levels = OrganizationProfile::sole()->chartLevels();

    expect($levels[0][0]['person'])->toBeNull()
        ->and(array_column(last($levels)[0]['members'], 'name'))->toBe(['Akun Aktif']);
});

it('pilihan pengurus hanya berisi Member UKM yang aktif', function () {
    $member = User::factory()->create(['name' => 'Member Aktif']);
    $others = collect([Role::AdminUkm, Role::AdminInventaris, Role::Kurator, Role::AnggotaLain])
        ->map(fn (Role $role) => User::factory()->role($role)->create());
    $pending = User::factory()->pending()->create(['name' => 'Belum Aktif']);

    expect(array_keys(OrganizationProfile::memberOptions()))->toBe([$member->id])
        ->and($pending->id)->not->toBeIn(array_keys(OrganizationProfile::memberOptions()));
});

it('akun yang role-nya bukan Member lagi tidak tampil di bagan', function () {
    $user = User::factory()->create(['name' => 'Dulu Member']);
    OrganizationProfile::current()->update([
        'core_members' => [['position' => 'Ketua Umum', 'user_id' => $user->id]],
        'divisions' => [],
    ]);

    $user->update(['role' => Role::Kurator]);

    expect(OrganizationProfile::sole()->chartLevels()[0][0]['person'])->toBeNull();
});

it('menyusun bagan dengan Ketua Umum paling atas dan divisi di baris terbawah', function () {
    $levels = OrganizationProfile::current()->chartLevels();

    expect(array_map(fn ($nodes) => array_column($nodes, 'title'), $levels))->toBe([
        ['Ketua Umum'],
        ['Wakil Ketua'],
        ['Sekretaris', 'Bendahara'],
        [
            'Divisi Penelitian dan Pengembangan',
            'Divisi Hunting',
            'Divisi Pameran',
            'Divisi Inventaris',
            'Divisi Humas Internal dan Eksternal',
        ],
    ]);

    $this->get('/')->assertSee('chart-top', false);
});

it('sejarah dan visi wajib diisi', function () {
    loginAs(Role::AdminUkm);

    livewire(ManageOrganizationProfile::class)
        ->fillForm(['history' => '', 'vision' => ''])
        ->call('save')
        ->assertHasFormErrors(['history' => 'required', 'vision' => 'required']);
});

it('hanya admin UKM yang bisa membuka halaman profil', function (Role $role) {
    loginAs($role);

    $this->get(ManageOrganizationProfile::getUrl())->assertForbidden();
})->with([Role::AdminInventaris, Role::Member]);

it('kurator dan anggota UKM lain yang membuka halaman profil diarahkan ke panelnya', function (Role $role) {
    loginAs($role);

    $this->get(ManageOrganizationProfile::getUrl())->assertRedirect('/'.$role->panel());
})->with([Role::Kurator, Role::AnggotaLain]);
