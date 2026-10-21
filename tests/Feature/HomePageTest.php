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
