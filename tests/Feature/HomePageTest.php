<?php

use App\Enums\EventStatus;
use App\Models\Artwork;
use App\Models\Event;
use App\Models\OrganizationProfile;

it('menampilkan hanya karya yang dipilih kurator', function () {
    Artwork::factory()->published()->create(['title' => 'Karya Terpilih']);
    Artwork::factory()->create(['title' => 'Karya Belum Dikurasi']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Karya Terpilih')
        ->assertDontSee('Karya Belum Dikurasi');
});

it('menampilkan acara mendatang yang sudah dipublikasikan', function () {
    Event::factory()->create(['title' => 'Workshop Lightroom']);
    Event::factory()->create(['title' => 'Rapat Internal', 'status' => EventStatus::Draft]);

    $this->get('/')
        ->assertSee('Workshop Lightroom')
        ->assertDontSee('Rapat Internal');
});

it('mencatat jumlah dilihat untuk karya yang tampil', function () {
    $artwork = Artwork::factory()->published()->create();

    $this->post(route('artworks.view', $artwork))->assertOk();

    expect($artwork->fresh()->views)->toBe(1);
});

it('tidak mencatat view untuk karya yang belum tampil', function () {
    $artwork = Artwork::factory()->create();

    $this->post(route('artworks.view', $artwork))->assertNotFound();
});

it('menampilkan sejarah, visi misi, dan bagan struktur BPH di bagian Profil', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="#profil"', false)
        ->assertSeeInOrder(['Profil PhotoUP 94', 'Sejarah PhotoUP 94', 'Visi', 'Misi', 'Struktur BPH PhotoUP', 'Ketua Umum', 'Wakil Ketua', 'Sekretaris', 'Bendahara'])
        ->assertSeeInOrder([
            'Divisi Penelitian dan Pengembangan',
            'Divisi Hunting',
            'Divisi Pameran',
            'Divisi Inventaris',
            'Divisi Humas Internal dan Eksternal',
        ]);
});

it('di HP setiap bagian punya tombol buka-tutup', function () {
    $this->get('/')
        ->assertSee('aria-controls="profil-body"', false)
        ->assertSee('aria-controls="galeri-body"', false)
        ->assertSee('aria-controls="kegiatan-body"', false)
        ->assertSee('aria-controls="kontak-body"', false);
});

it('formulir pendaftaran tidak lagi ada di halaman depan, tombol Daftar menuju halaman terpisah', function () {
    $this->get('/')
        ->assertDontSee('id="registerForm"', false)
        ->assertDontSee('aria-controls="daftar-body"', false)
        ->assertSee('href="'.route('register.show').'"', false);
});

it('link TikTok di bagian kontak mengarah ke akun TikTok UKM', function () {
    OrganizationProfile::current()->update(['tiktok' => '@ukmfotografi.up']);

    $this->get('/')
        ->assertOk()
        ->assertSee('href="https://www.tiktok.com/@ukmfotografi.up"', false)
        ->assertDontSee('ltrim(', false);
});
