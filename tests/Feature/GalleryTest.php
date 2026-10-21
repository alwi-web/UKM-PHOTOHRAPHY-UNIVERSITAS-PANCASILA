<?php

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Filament\Pages\ArtworkInsight;
use App\Filament\Resources\Artworks\Pages\CreateArtwork;
use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Models\Artwork;
use App\Models\Category;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

it('member mengunggah karya dan statusnya menunggu kurasi', function () {
    Storage::fake('public');
    $member = loginAs(Role::Member);
    $category = Category::factory()->create();

    livewire(CreateArtwork::class)
        ->fillForm([
            'title' => 'Senja di Kampus',
            'category_id' => $category->id,
            'caption' => 'Diambil dengan 50mm f/1.8',
            'image' => UploadedFile::fake()->image('senja.jpg'),
            'instagram_url' => 'https://www.instagram.com/p/abc123/',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $artwork = Artwork::sole();
    expect($artwork->user_id)->toBe($member->id)
        ->and($artwork->status)->toBe(ArtworkStatus::Pending)
        ->and($artwork->is_featured)->toBeFalse();
});

it('anggota UKM lain tidak bisa mengunggah karya', function () {
    loginAs(Role::AnggotaLain);

    $this->get(CreateArtwork::getUrl())->assertForbidden();
});

it('kurator meloloskan karya dan memilihnya untuk tampil di website', function () {
    $kurator = loginAs(Role::Kurator);
    $artwork = Artwork::factory()->create();

    livewire(ListArtworks::class)
        ->callAction(TestAction::make('curate')->table($artwork), data: [
            'status' => ArtworkStatus::Approved->value,
            'is_featured' => true,
            'curator_note' => 'Komposisi bagus',
        ])
        ->assertHasNoFormErrors();

    $artwork->refresh();
    expect($artwork->status)->toBe(ArtworkStatus::Approved)
        ->and($artwork->is_featured)->toBeTrue()
        ->and($artwork->curated_by)->toBe($kurator->id)
        ->and(Artwork::published()->pluck('id')->all())->toBe([$artwork->id]);
});

it('karya yang ditolak tidak pernah tampil di website', function () {
    $kurator = loginAs(Role::Kurator);
    $artwork = Artwork::factory()->create();

    $artwork->curate($kurator, ArtworkStatus::Rejected, featured: true);

    expect($artwork->fresh()->is_featured)->toBeFalse();
});

it('member hanya melihat karyanya sendiri dan tidak bisa mengkurasi', function () {
    $member = loginAs(Role::Member);
    $mine = Artwork::factory()->for($member)->create();
    $others = Artwork::factory()->create();

    livewire(ListArtworks::class)
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$others])
        ->assertActionHidden(TestAction::make('curate')->table($mine));
});

it('halaman insight karya bisa dibuka kurator, tidak oleh member', function () {
    Artwork::factory(3)->published()->create();

    loginAs(Role::Kurator);
    $this->get(ArtworkInsight::getUrl())->assertOk()->assertSee('Insight Karya');

    loginAs(Role::Member);
    $this->get(ArtworkInsight::getUrl())->assertForbidden();
});
