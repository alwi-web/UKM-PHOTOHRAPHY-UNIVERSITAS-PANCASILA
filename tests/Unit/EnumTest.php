<?php

use App\Enums\Role;

it('mengatur hak akses tiap role', function (Role $role, bool $inventory, bool $borrow, bool $upload, bool $curate) {
    expect($role->canManageInventory())->toBe($inventory)
        ->and($role->canBorrow())->toBe($borrow)
        ->and($role->canUploadArtwork())->toBe($upload)
        ->and($role->canCurate())->toBe($curate);
})->with([
    //                       inventaris  pinjam  unggah  kurasi
    'Admin UKM' => [Role::AdminUkm, true, true, true, true],
    'Admin Inventaris' => [Role::AdminInventaris, true, true, false, false],
    'Kurator' => [Role::Kurator, false, false, false, true],
    'Member' => [Role::Member, false, true, true, false],
    'Anggota UKM lain' => [Role::AnggotaLain, false, true, false, false],
]);

it('setiap role punya label bahasa Indonesia', function () {
    expect(collect(Role::cases())->map->getLabel()->all())
        ->toBe(['Admin UKM', 'Admin Inventaris', 'Kurator', 'Member UKM Fotografi', 'Anggota UKM Lain']);
});
