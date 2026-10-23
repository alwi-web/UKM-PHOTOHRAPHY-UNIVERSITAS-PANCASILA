<?php

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;

it('menampilkan halaman login terpisah', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Masuk ke panel PhotoUP')
        ->assertSee('Kembali ke website');
});

it('tombol Masuk di website mengarah ke halaman login', function () {
    $this->get('/')->assertSee('href="'.route('login').'"', false);
});

it('pengunjung yang belum login diarahkan ke halaman login saat membuka panel', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

it('login berhasil lalu diarahkan ke panel', function () {
    $user = User::factory()->create(['email' => 'member@ukmfoto.test', 'role' => Role::Member]);

    $this->post('/login', ['email' => 'member@ukmfoto.test', 'password' => 'password'])
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($user);
});

it('menolak password yang salah', function () {
    User::factory()->create(['email' => 'member@ukmfoto.test']);

    $this->from('/login')
        ->post('/login', ['email' => 'member@ukmfoto.test', 'password' => 'salah'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

    $this->assertGuest();
});

it('menolak akun yang belum aktif', function () {
    User::factory()->create(['email' => 'baru@ukmfoto.test', 'status' => UserStatus::Pending]);

    $this->post('/login', ['email' => 'baru@ukmfoto.test', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Akun kamu belum aktif. Hubungi Admin UKM.']);

    $this->assertGuest();
});

it('membatasi percobaan login berulang', function () {
    User::factory()->create(['email' => 'member@ukmfoto.test']);

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => 'member@ukmfoto.test', 'password' => 'salah']);
    }

    $this->post('/login', ['email' => 'member@ukmfoto.test', 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('pengguna yang sudah login langsung diarahkan ke panel', function () {
    loginAs(Role::Member);

    $this->get('/login')->assertRedirect('/admin');
});

it('keluar dari panel kembali ke halaman login', function () {
    loginAs(Role::Member);

    $this->post(route('filament.admin.auth.logout'))->assertRedirect(route('login'));

    $this->assertGuest();
});
