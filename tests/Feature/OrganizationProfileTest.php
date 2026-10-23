<?php

use App\Enums\Role;
use App\Filament\Pages\ManageOrganizationProfile;
use App\Models\OrganizationProfile;

use function Pest\Livewire\livewire;

it('membuat profil dari isi awal saat pertama kali dibuka', function () {
    $profile = OrganizationProfile::current();

    expect($profile->name)->toBe('PhotoUP 94')
        ->and(collect($profile->core_members)->pluck('position')->all())->toBe(['Ketua Umum', 'Wakil Ketua', 'Sekretaris', 'Bendahara'])
        ->and(OrganizationProfile::count())->toBe(1);

    OrganizationProfile::current();
    expect(OrganizationProfile::count())->toBe(1);
});

it('admin UKM mengubah sejarah, visi misi, dan struktur organisasi', function () {
    $admin = loginAs(Role::AdminUkm);

    livewire(ManageOrganizationProfile::class)
        ->fillForm([
            'name' => 'PhotoUP 94',
            'history' => "PhotoUP 94 berdiri pada tahun 1994.\n\nSejak itu rutin mengadakan pameran tahunan.",
            'vision' => 'Menjadi komunitas fotografi terbaik.',
            // repeater "simple" menyimpan state internal sebagai item {mission: ...}
            'missions' => [['mission' => 'Mengadakan kelas rutin.'], ['mission' => 'Mengadakan pameran tahunan.']],
            'core_members' => [
                ['position' => 'Ketua', 'name' => 'Rizky Pratama', 'photo' => null],
                ['position' => 'Bendahara', 'name' => 'Sari', 'photo' => null],
            ],
            'divisions' => [
                ['name' => 'Hunting', 'coordinator' => 'Dimas', 'description' => 'Hunting foto setiap bulan.'],
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
        ->and($profile->core_members[0]['name'])->toBe('Rizky Pratama')
        ->and($profile->updated_by)->toBe($admin->id);
});

it('perubahan profil langsung tampil di halaman depan', function () {
    OrganizationProfile::current()->update([
        'history' => "Paragraf pertama sejarah.\n\nParagraf kedua sejarah.",
        'vision' => 'Visi baru PhotoUP.',
        'missions' => ['Misi satu.', 'Misi dua.'],
        'core_members' => [['position' => 'Ketua', 'name' => 'Rizky Pratama', 'photo' => null]],
        'divisions' => [['name' => 'Hunting', 'coordinator' => 'Dimas', 'description' => null]],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Sejarah PhotoUP 94', 'Paragraf pertama sejarah.', 'Paragraf kedua sejarah.'])
        ->assertSeeInOrder(['Visi baru PhotoUP.', 'Misi satu.', 'Misi dua.'])
        ->assertSeeInOrder(['Struktur Organisasi', 'Ketua', 'Rizky Pratama'])
        ->assertSeeInOrder(['Divisi Hunting', 'Dimas'])
        ->assertDontSee('Wakil Ketua');
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
})->with([Role::AdminInventaris, Role::Kurator, Role::Member, Role::AnggotaLain]);

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
});

it('urutan pengurus inti menentukan posisi di bagan', function () {
    OrganizationProfile::current()->update([
        'core_members' => [
            ['position' => 'Ketua Umum', 'name' => 'Rizky', 'photo' => null],
            ['position' => 'Sekretaris', 'name' => null, 'photo' => null],
        ],
        'divisions' => [],
    ]);

    $levels = OrganizationProfile::sole()->chartLevels();

    expect($levels)->toHaveCount(2)
        ->and($levels[0][0])->toMatchArray(['title' => 'Ketua Umum', 'name' => 'Rizky'])
        ->and($levels[1][0]['title'])->toBe('Sekretaris');

    $this->get('/')->assertSee('chart-top', false);
});
