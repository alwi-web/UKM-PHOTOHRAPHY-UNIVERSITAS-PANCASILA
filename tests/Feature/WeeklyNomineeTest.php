<?php

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Models\Artwork;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

/*
 * Nominasi foto mingguan: kurator/admin UKM menominasikan karya yang lolos kurasi,
 * lalu karya tampil di carousel pada bagian galeri website selama minggu tersebut.
 */

beforeEach(fn () => Carbon::setTestNow('2026-10-28 10:00')); // Rabu; minggu ini mulai Senin 26 Okt

it('menominasikan karya untuk minggu ini dan ikut tampil di website', function () {
    $artwork = Artwork::factory()->create(['status' => ArtworkStatus::Approved, 'is_featured' => false]);

    $artwork->nominate();

    expect($artwork->fresh()->nominated_week->toDateString())->toBe('2026-10-26')
        ->and($artwork->fresh()->is_featured)->toBeTrue()
        ->and($artwork->fresh()->isNominatedThisWeek())->toBeTrue();
});

it('karya yang belum lolos kurasi tidak bisa dinominasikan', function () {
    $artwork = Artwork::factory()->create(['status' => ArtworkStatus::Pending]);

    expect(fn () => $artwork->nominate())->toThrow(DomainException::class);
});

it('karya yang ditolak saat kurasi ulang keluar dari nominasi', function () {
    $artwork = Artwork::factory()->published()->create();
    $artwork->nominate();

    $artwork->curate(loginAs(Role::Kurator), ArtworkStatus::Rejected);

    expect($artwork->fresh()->nominated_week)->toBeNull();
});

it('website menampilkan nominasi minggu ini, atau minggu terakhir yang punya nominasi', function () {
    $lama = Artwork::factory()->published()->create(['title' => 'Nominasi Minggu Lalu', 'nominated_week' => '2026-10-19']);
    Artwork::factory()->published()->create(['title' => 'Belum Waktunya', 'nominated_week' => '2026-11-02']);
    Artwork::factory()->create(['status' => ArtworkStatus::Pending, 'nominated_week' => '2026-10-26']);

    // belum ada nominasi minggu ini → pakai minggu lalu
    $result = Artwork::weeklyNominees();
    expect($result['week']->toDateString())->toBe('2026-10-19')
        ->and($result['artworks']->pluck('id')->all())->toBe([$lama->id]);

    $baru = Artwork::factory()->published()->create(['title' => 'Nominasi Minggu Ini', 'nominated_week' => '2026-10-26']);
    $result = Artwork::weeklyNominees();
    expect($result['week']->toDateString())->toBe('2026-10-26')
        ->and($result['artworks']->pluck('id')->all())->toBe([$baru->id]);
});

it('slider di atas halaman depan menampilkan nominasi foto mingguan', function () {
    Artwork::factory()->published()->create(['title' => 'Senja di Danau Kampus', 'nominated_week' => '2026-10-26']);
    Artwork::factory()->published()->create(['title' => 'Pedagang Pagi', 'nominated_week' => '2026-10-26']);
    Artwork::factory()->published()->create(['title' => 'Bukan Nominasi']);

    $html = $this->get('/')->assertOk()
        ->assertSee('Nominasi Foto Mingguan')
        ->assertSeeInOrder(['data-slider', 'Senja di Danau Kampus', 'slider-dot'], false)
        ->getContent();

    // slider hanya berisi nominasi (2 foto)
    expect(preg_match_all('/class="slide( is-active)?"/', $html))->toBe(2);
});

it('tanpa nominasi, slider tetap tampil tanpa label nominasi', function () {
    Artwork::factory()->published()->create();

    $this->get('/')->assertOk()->assertSee('data-slider', false)->assertDontSee('credit-badge', false);
});

it('kurator menominasikan dan membatalkan nominasi dari panel', function () {
    loginAs(Role::Kurator);
    $artwork = Artwork::factory()->published()->create();

    livewire(ListArtworks::class, ['activeTab' => 'semua'])
        ->callAction(TestAction::make('nominate')->table($artwork))
        ->assertNotified('Karya dinominasikan sebagai foto minggu ini.');
    expect($artwork->fresh()->isNominatedThisWeek())->toBeTrue();

    livewire(ListArtworks::class, ['activeTab' => 'semua'])
        ->callAction(TestAction::make('cancelNomination')->table($artwork));
    expect($artwork->fresh()->nominated_week)->toBeNull();
});

it('tombol nominasi hanya untuk karya lolos kurasi dan hanya untuk kurator/admin UKM', function () {
    $pending = Artwork::factory()->create(['status' => ArtworkStatus::Pending]);
    $approved = Artwork::factory()->published()->create();

    loginAs(Role::AdminUkm);
    livewire(ListArtworks::class)
        ->assertActionVisible(TestAction::make('nominate')->table($approved))
        ->assertActionHidden(TestAction::make('nominate')->table($pending));

    $member = loginAs(Role::Member);
    $mine = Artwork::factory()->published()->for($member)->create();
    livewire(ListArtworks::class)->assertActionHidden(TestAction::make('nominate')->table($mine));
});

it('tab Nominasi Minggu Ini hanya berisi nominasi minggu ini', function () {
    loginAs(Role::Kurator);
    $ini = Artwork::factory()->published()->create(['nominated_week' => '2026-10-26']);
    $lalu = Artwork::factory()->published()->create(['nominated_week' => '2026-10-19']);

    livewire(ListArtworks::class, ['activeTab' => 'nominasi'])
        ->assertCanSeeTableRecords([$ini])
        ->assertCanNotSeeTableRecords([$lalu]);
});
