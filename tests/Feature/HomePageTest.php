<?php

use App\Enums\EventStatus;
use App\Models\Artwork;
use App\Models\Event;

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

it('menampilkan sejarah dan bagan struktur organisasi di bagian Tentang Kami', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Sejarah PhotoUP 94')
        ->assertSeeInOrder(['Struktur Organisasi', 'Ketua Umum', 'Wakil Ketua', 'Sekretaris', 'Bendahara'])
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
        ->assertSee('aria-controls="tentang-body"', false)
        ->assertSee('aria-controls="galeri-body"', false)
        ->assertSee('aria-controls="kegiatan-body"', false)
        ->assertSee('aria-controls="daftar-body"', false)
        ->assertSee('aria-controls="kontak-body"', false);
});

it('bagian pendaftaran langsung terbuka saat formulir gagal dikirim', function () {
    $this->post(route('register'), [])->assertSessionHasErrors();

    $this->followingRedirects()
        ->post(route('register'), [])
        ->assertSee('id="daftar" class="section is-open"', false);
});

it('menampilkan angka ringkas karya, agenda, dan anggota aktif', function () {
    Artwork::factory()->published()->count(2)->create();

    $this->get('/')
        ->assertOk()
        ->assertSeeInOrder(['2', 'Karya terkurasi', 'Agenda mendatang', 'Anggota aktif']);
});
