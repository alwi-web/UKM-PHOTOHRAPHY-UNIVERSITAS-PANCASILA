<?php

use App\Enums\EventStatus;
use App\Models\Artwork;
use App\Models\Event;
use App\Models\OrganizationProfile;
use App\Models\User;

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

it('beranda menampilkan profil singkat dengan tombol ke halaman Profil', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['Profil PhotoUP 94', 'UKM Fotografi adalah wadah', 'Selengkapnya'])
        ->assertSee('href="'.route('profile').'"', false)
        ->assertDontSee('Struktur BPH PhotoUP');
});

it('halaman Profil menampilkan sejarah, visi misi, dan bagan struktur BPH', function () {
    $this->get('/profil')
        ->assertOk()
        ->assertSeeInOrder(['Profil PhotoUP 94', 'Sejarah PhotoUP 94', 'Visi', 'Misi', 'Struktur BPH PhotoUP', 'Ketua Umum', 'Wakil Ketua', 'Sekretaris', 'Bendahara'])
        ->assertSeeInOrder([
            'Divisi Penelitian dan Pengembangan',
            'Divisi Hunting',
            'Divisi Pameran',
            'Divisi Inventaris',
            'Divisi Humas Internal dan Eksternal',
        ]);
});

it('di HP ada menu ringkas berupa tombol ke setiap halaman', function () {
    $this->get('/')
        ->assertSee('class="quick-menu', false)
        ->assertSeeInOrder(['Menu cepat', 'href="'.route('profile').'"', 'href="'.route('gallery').'"', 'href="#kegiatan"', 'href="#kontak"'], false);
});

it('slider atas memakai karya terbaru bila belum ada nominasi mingguan', function () {
    Artwork::factory()->published()->create(['title' => 'Karya Paling Baru', 'curated_at' => now()]);
    Artwork::factory()->published()->create(['title' => 'Karya Lama', 'curated_at' => now()->subWeek()]);

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['data-slider', 'Karya Paling Baru', 'Karya Lama', 'slider-dot'], false)
        ->assertSee('Mengabadikan Cerita')
        ->assertDontSee('credit-badge', false);
});

it('beranda menampilkan maksimal lima karya terbaru dengan tautan Lihat Semua ke Galeri', function () {
    Artwork::factory(7)->published()->create();

    $html = $this->get('/')->assertOk()->assertSee('Karya Terbaru')->assertSee('Lihat Semua')->getContent();

    expect(substr_count($html, 'class="gallery-item"'))->toBe(5)
        ->and($html)->toContain('href="'.route('gallery').'"');
});

it('halaman Galeri menampilkan semua karya terkurasi dan bisa dicari', function () {
    $owner = User::factory()->create(['name' => 'Rizky Fotografer']);
    Artwork::factory()->published()->create(['title' => 'Senja di Danau Kampus']);
    Artwork::factory()->published()->for($owner)->create(['title' => 'Pasar Pagi']);
    Artwork::factory()->create(['title' => 'Belum Dikurasi']);

    $this->get('/galeri')->assertOk()
        ->assertSee('Senja di Danau Kampus')->assertSee('Pasar Pagi')->assertDontSee('Belum Dikurasi');

    $this->get('/galeri?q=senja')->assertOk()
        ->assertSee('Senja di Danau Kampus')->assertDontSee('Pasar Pagi')->assertSee('1 karya untuk');

    // bisa juga mencari nama fotografer
    $this->get('/galeri?q=rizky')->assertOk()
        ->assertSee('Pasar Pagi')->assertDontSee('Senja di Danau Kampus');

    $this->get('/galeri?q=tidakada')->assertOk()->assertSee('Tidak ada karya yang cocok');
});

it('formulir pendaftaran tidak lagi ada di halaman depan, tombol Daftar menuju halaman terpisah', function () {
    $this->get('/')
        ->assertDontSee('id="registerForm"', false)
        ->assertSee('href="'.route('register.show').'"', false);
});

it('link TikTok di bagian kontak mengarah ke akun TikTok UKM', function () {
    OrganizationProfile::current()->update(['tiktok' => '@ukmfotografi.up']);

    $this->get('/')
        ->assertOk()
        ->assertSee('href="https://www.tiktok.com/@ukmfotografi.up"', false)
        ->assertDontSee('ltrim(', false);
});

it('ikon mengambang di bagian profil menuju bagian Profil, Visi, Misi, dan Struktural', function () {
    $this->get('/')->assertOk()->assertSeeInOrder([
        'href="'.route('profile').'#sejarah"', 'Profil',
        'href="'.route('profile').'#visi"', 'Visi',
        'href="'.route('profile').'#misi"', 'Misi',
        'href="'.route('profile').'#struktur"', 'Struktural',
    ], false);

    $this->get('/profil')->assertOk()
        ->assertSeeInOrder(['id="sejarah"', 'id="visi"', 'id="misi"', 'id="struktur"'], false);
});
