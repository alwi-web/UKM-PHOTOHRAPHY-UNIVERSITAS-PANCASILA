<?php

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Filament\Resources\Artworks\Pages\CreateArtwork;
use App\Filament\Resources\Artworks\Pages\EditArtwork;
use App\Filament\Resources\Artworks\Pages\ListArtworks;
use App\Models\Artwork;
use App\Models\Category;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

it('member mengunggah foto lengkap dengan segitiga exposure', function () {
    Storage::fake('public');
    $member = loginAs(Role::Member);
    $category = Category::factory()->create();

    livewire(CreateArtwork::class)
        ->fillForm([
            'title' => 'Senja di Kampus',
            'category_id' => $category->id,
            'caption' => 'Matahari terbenam di danau kampus',
            'aperture' => 'f/8',
            'shutter_speed' => '1/250',
            'iso' => 100,
            'exposure_notes' => 'f/8 agar seluruh lanskap tajam, ISO 100 karena cahaya masih cukup.',
            'image' => UploadedFile::fake()->image('senja.jpg'),
            'instagram_url' => 'https://www.instagram.com/p/abc123/',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $artwork = Artwork::sole();
    expect($artwork->user_id)->toBe($member->id)
        ->and($artwork->status)->toBe(ArtworkStatus::Pending)
        ->and($artwork->is_featured)->toBeFalse()
        ->and($artwork->exposureSummary())->toBe('f/8 · 1/250s · ISO 100');
});

it('segitiga exposure wajib diisi', function () {
    loginAs(Role::Member);

    livewire(CreateArtwork::class)
        ->fillForm(['title' => 'Tanpa exposure'])
        ->call('create')
        ->assertHasFormErrors(['aperture', 'shutter_speed', 'iso', 'exposure_notes']);
});

it('member melihat karya mana yang lulus kurasi beserta catatan kurator', function () {
    $member = loginAs(Role::Member);
    $lulus = Artwork::factory()->for($member)->published()->create(['curator_note' => 'Komposisi bagus']);
    $menunggu = Artwork::factory()->for($member)->create();
    $milikOrangLain = Artwork::factory()->published()->create();

    livewire(ListArtworks::class)
        ->assertCanSeeTableRecords([$lulus, $menunggu])
        ->assertCanNotSeeTableRecords([$milikOrangLain])
        ->assertTableColumnStateSet('curator_note', 'Komposisi bagus', $lulus)
        ->set('activeTab', 'approved')
        ->assertCanSeeTableRecords([$lulus])
        ->assertCanNotSeeTableRecords([$menunggu]);
});

it('member tidak bisa mengubah karya yang sudah dikurasi', function () {
    $member = loginAs(Role::Member);
    $artwork = Artwork::factory()->for($member)->published()->create();

    $this->get(EditArtwork::getUrl(['record' => $artwork]))->assertForbidden();
});

it('kurator meloloskan karya dan menampilkannya di website', function () {
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

it('kurator hanya mengkurasi, tidak mengunggah atau mengubah karya', function () {
    loginAs(Role::Kurator);
    $artwork = Artwork::factory()->create();

    livewire(ListArtworks::class)
        ->assertActionVisible(TestAction::make('curate')->table($artwork))
        ->assertActionHidden(TestAction::make('edit')->table($artwork))
        ->assertActionHidden(TestAction::make('delete')->table($artwork));
    $this->get(CreateArtwork::getUrl())->assertForbidden();
});

it('karya yang ditolak tidak pernah tampil di website', function () {
    $kurator = loginAs(Role::Kurator);
    $artwork = Artwork::factory()->create();

    $artwork->curate($kurator, ArtworkStatus::Rejected, featured: true);

    expect($artwork->fresh()->is_featured)->toBeFalse();
});

it('member tidak bisa mengkurasi', function () {
    $member = loginAs(Role::Member);
    $mine = Artwork::factory()->for($member)->create();

    livewire(ListArtworks::class)->assertActionHidden(TestAction::make('curate')->table($mine));
});
