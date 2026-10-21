<?php

use App\Enums\Role;

it('hanya member, kurator, dan admin UKM yang boleh mengunggah karya', function (Role $role, bool $expected) {
    expect($role->canUploadArtwork())->toBe($expected);
})->with([
    [Role::AdminUkm, true],
    [Role::Kurator, true],
    [Role::Member, true],
    [Role::AdminInventaris, false],
    [Role::AnggotaLain, false],
]);

it('setiap role punya label bahasa Indonesia', function () {
    expect(collect(Role::cases())->map->getLabel()->all())
        ->toBe(['Admin UKM', 'Admin Inventaris', 'Kurator', 'Member UKM Fotografi', 'Anggota UKM Lain']);
});
