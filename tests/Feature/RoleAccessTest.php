<?php

use App\Enums\Role;

/*
 * Matriks akses menu panel per role.
 * Admin UKM: semua. Admin Inventaris: barang & peminjaman. Kurator: kurasi karya.
 * Member: unggah karya. Anggota UKM lain: pinjam barang.
 */
$pages = [
    'barang' => '/admin/items',
    'tambah barang' => '/admin/items/create',
    'peminjaman' => '/admin/loans',
    'ajukan peminjaman' => '/admin/loans/create',
    'karya' => '/admin/artworks',
    'unggah karya' => '/admin/artworks/create',
    'kategori' => '/admin/categories',
    'acara' => '/admin/events',
    'user' => '/admin/users',
    'pendaftar kaderisasi' => '/admin/registrations',
    'laporan kegiatan' => '/admin/event-report',
    'insight karya' => '/admin/artwork-insight',
];

$allowed = [
    Role::AdminUkm->value => array_keys($pages),
    Role::AdminInventaris->value => ['barang', 'tambah barang', 'peminjaman', 'ajukan peminjaman'],
    Role::Kurator->value => ['karya'],
    Role::Member->value => ['karya', 'unggah karya'],
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

    $this->get($url)->assertStatus($canOpen ? 200 : 403);
})->with($cases);
