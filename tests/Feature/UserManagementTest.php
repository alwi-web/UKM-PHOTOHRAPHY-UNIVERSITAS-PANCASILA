<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('admin UKM menambah user dan mengatur role', function () {
    loginAs(Role::AdminUkm);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Sari Admin Alat',
            'email' => 'sari@example.com',
            'nim' => '4121000999',
            'role' => Role::AdminInventaris->value,
            'status' => UserStatus::Active->value,
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'sari@example.com')->sole()->role)->toBe(Role::AdminInventaris);
});

it('admin UKM menyetujui pendaftar baru', function () {
    loginAs(Role::AdminUkm);
    $pendaftar = User::factory()->pending()->create();

    livewire(ListUsers::class)->callAction(TestAction::make('approve')->table($pendaftar));

    expect($pendaftar->fresh()->status)->toBe(UserStatus::Active);
});

it('admin UKM menunjuk anggota sebagai kurator', function () {
    loginAs(Role::AdminUkm);
    $member = User::factory()->create();

    livewire(ListUsers::class)->callAction(TestAction::make('makeCurator')->table($member));

    expect($member->fresh()->role)->toBe(Role::Kurator);
});

it('selain admin UKM tidak bisa membuka manajemen user', function (Role $role) {
    loginAs($role);

    $this->get(ListUsers::getUrl())->assertForbidden();
})->with([Role::AdminInventaris, Role::Kurator, Role::Member, Role::AnggotaLain]);
