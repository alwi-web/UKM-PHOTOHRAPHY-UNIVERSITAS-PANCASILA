<?php

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\PendingArtworks;
use App\Models\Artwork;

use function Pest\Livewire\livewire;

/*
 * Matriks akses menu panel per role.
 * Admin UKM: semua. Admin Inventaris: barang & peminjaman. Kurator: kurasi karya.
 * Member: unggah karya & pinjam barang. Anggota UKM lain: pinjam barang.
 * Kurator membuka panel /kurator, Anggota UKM lain membuka panel /anggota, sisanya /admin.
 */
$pages = [
    'barang' => '/items',
    'tambah barang' => '/items/create',
    'peminjaman' => '/loans',
    'ajukan peminjaman' => '/loans/create',
    'karya' => '/artworks',
    'unggah karya' => '/artworks/create',
    'kategori' => '/categories',
    'acara' => '/events',
    'user' => '/users',
    'pendaftar calon anggota' => '/registrations',
    'profil ukm' => '/profil-ukm',
    'laporan kegiatan' => '/event-report',
    'insight karya' => '/artwork-insight',
];

$allowed = [
    Role::AdminUkm->value => array_keys($pages),
    Role::AdminInventaris->value => ['barang', 'tambah barang', 'peminjaman', 'ajukan peminjaman'],
    Role::Kurator->value => ['karya'],
    Role::Member->value => ['barang', 'peminjaman', 'ajukan peminjaman', 'karya', 'unggah karya'],
    Role::AnggotaLain->value => ['barang', 'peminjaman', 'ajukan peminjaman'],
];

$cases = [];
foreach (Role::cases() as $role) {
    foreach ($pages as $name => $url) {
        $cases["{$role->getLabel()} → {$name}"] = [$role, $url, in_array($name, $allowed[$role->value], true)];
    }
}

it('membatasi menu sesuai role', function (Role $role, string $url, bool $canOpen) {
    loginAs($role);

    $status = $this->get('/'.$role->panel().$url)->getStatusCode();

    // menu yang tidak diizinkan ditolak (403) atau memang tidak ada di panel role tersebut (404)
    $canOpen ? expect($status)->toBe(200) : expect($status)->toBeIn([403, 404]);
})->with($cases);

it('setiap role punya panel sendiri', function (Role $role, string $panel) {
    expect($role->panel())->toBe($panel);
})->with([
    [Role::AdminUkm, 'admin'],
    [Role::AdminInventaris, 'admin'],
    [Role::Member, 'admin'],
    [Role::Kurator, 'kurator'],
    [Role::AnggotaLain, 'anggota'],
]);

it('membuka panel role lain diarahkan ke panel sendiri', function (Role $role, string $url, string $target) {
    loginAs($role);

    $this->get($url)->assertRedirect($target);
})->with([
    'kurator ke /admin' => [Role::Kurator, '/admin', '/kurator'],
    'kurator ke /anggota' => [Role::Kurator, '/anggota/items', '/kurator'],
    'anggota UKM lain ke /admin' => [Role::AnggotaLain, '/admin/items', '/anggota'],
    'anggota UKM lain ke /kurator' => [Role::AnggotaLain, '/kurator', '/anggota'],
    'admin UKM ke /kurator' => [Role::AdminUkm, '/kurator/artworks', '/admin'],
    'member ke /anggota' => [Role::Member, '/anggota', '/admin'],
]);

it('dasbor panel kurator menampilkan antrean kurasi', function () {
    loginAs(Role::Kurator);
    $pending = Artwork::factory()->create(['title' => 'Senja di Danau Kenanga', 'status' => ArtworkStatus::Pending]);
    Artwork::factory()->create(['title' => 'Sudah Dikurasi', 'status' => ArtworkStatus::Approved]);

    $this->get('/kurator')->assertOk()->assertSee('Panel Kurator');

    livewire(PendingArtworks::class)
        ->assertCanSeeTableRecords([$pending])
        ->assertCountTableRecords(1);
});

it('dasbor panel anggota UKM lain menampilkan barang dan peminjaman', function () {
    loginAs(Role::AnggotaLain);

    $this->get('/anggota')->assertOk()->assertSee('Panel Anggota UKM Lain')
        ->assertSeeLivewire(DashboardStats::class);
});
