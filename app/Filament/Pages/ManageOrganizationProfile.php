<?php

namespace App\Filament\Pages;

use App\Models\OrganizationProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/** Admin UKM mengubah profil, sejarah, visi & misi, dan struktur organisasi yang tampil di website. */
class ManageOrganizationProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|UnitEnum|null $navigationGroup = 'Manajemen User';

    protected static ?string $navigationLabel = 'Profil UKM';

    protected static ?string $title = 'Profil UKM';

    protected static ?string $slug = 'profil-ukm';

    protected static ?int $navigationSort = 3;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdminUkm() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(OrganizationProfile::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Profil')
                            ->icon(Heroicon::OutlinedIdentification)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Organisasi')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('tagline')
                                    ->label('Slogan')
                                    ->helperText('Tampil di bagian paling atas halaman depan.')
                                    ->maxLength(200),
                                Textarea::make('about')
                                    ->label('Deskripsi Singkat')
                                    ->helperText('Paragraf pembuka bagian "Tentang Kami".')
                                    ->rows(3)
                                    ->maxLength(1000),
                                Section::make('Kontak')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('address')->label('Alamat')->columnSpanFull()->maxLength(255),
                                        TextInput::make('email')->email()->maxLength(255),
                                        TextInput::make('phone')->label('Telepon')->maxLength(50),
                                        TextInput::make('instagram')->label('Instagram')->prefix('@')->maxLength(100),
                                        TextInput::make('tiktok')->label('TikTok')->prefix('@')->maxLength(100),
                                    ]),
                            ]),
                        Tab::make('Sejarah')
                            ->icon(Heroicon::OutlinedBookOpen)
                            ->schema([
                                Textarea::make('history')
                                    ->label('Sejarah')
                                    ->helperText('Pisahkan paragraf dengan satu baris kosong.')
                                    ->rows(14)
                                    ->required(),
                            ]),
                        Tab::make('Visi & Misi')
                            ->icon(Heroicon::OutlinedFlag)
                            ->schema([
                                Textarea::make('vision')
                                    ->label('Visi')
                                    ->rows(3)
                                    ->required(),
                                Repeater::make('missions')
                                    ->label('Misi')
                                    ->simple(
                                        TextInput::make('mission')->required()->maxLength(500),
                                    )
                                    ->addActionLabel('Tambah misi')
                                    ->reorderable()
                                    ->minItems(1),
                            ]),
                        Tab::make('Struktur Organisasi')
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->schema([
                                Repeater::make('core_members')
                                    ->label('Pengurus Inti')
                                    ->helperText('Urutan menentukan posisi di bagan website: pertama paling atas (Ketua Umum), kedua tepat di bawahnya, sisanya berjajar. Seret untuk mengubah urutan.')
                                    ->schema([
                                        TextInput::make('position')->label('Jabatan')->required()->maxLength(100),
                                        TextInput::make('name')->label('Nama')->maxLength(100),
                                        FileUpload::make('photo')
                                            ->label('Foto')
                                            ->image()
                                            ->avatar()
                                            ->disk('public')
                                            ->directory('organization')
                                            ->maxSize(2048),
                                    ])
                                    ->columns(3)
                                    ->itemLabel(fn (array $state) => $state['position'] ?? null)
                                    ->addActionLabel('Tambah jabatan')
                                    ->reorderable()
                                    ->collapsible(),
                                Repeater::make('divisions')
                                    ->label('Divisi')
                                    ->helperText('Semua divisi tampil berjajar di baris paling bawah bagan.')
                                    ->schema([
                                        TextInput::make('name')->label('Nama Divisi')->required()->maxLength(100),
                                        Textarea::make('description')->label('Deskripsi')->rows(2)->maxLength(500),
                                        Repeater::make('members')
                                            ->label('Anggota Divisi')
                                            ->helperText('Foto, nama, dan NPM tampil di website di bawah kotak divisi.')
                                            ->schema([
                                                FileUpload::make('photo')
                                                    ->label('Foto')
                                                    ->image()
                                                    ->avatar()
                                                    ->disk('public')
                                                    ->directory('organization')
                                                    ->maxSize(2048),
                                                TextInput::make('name')->label('Nama')->required()->maxLength(100),
                                                TextInput::make('npm')
                                                    ->label('NPM')
                                                    ->required()
                                                    ->regex('/^\d{6,20}$/')
                                                    ->validationMessages(['regex' => 'NPM harus berupa 6–20 digit angka.'])
                                                    ->maxLength(20),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(0)
                                            ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                            ->addActionLabel('Tambah anggota')
                                            ->reorderable()
                                            ->collapsible(),
                                    ])
                                    ->itemLabel(fn (array $state) => isset($state['name'])
                                        ? 'Divisi '.$state['name'].' ('.count($state['members'] ?? []).' anggota)'
                                        : null)
                                    ->addActionLabel('Tambah divisi')
                                    ->reorderable()
                                    ->collapsible(),
                            ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Simpan')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                        Action::make('preview')
                            ->label('Lihat di website')
                            ->color('gray')
                            ->url(route('home').'#tentang', shouldOpenInNewTab: true),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        OrganizationProfile::current()->update([
            ...$data,
            'updated_by' => auth()->id(),
        ]);

        Notification::make()->title('Profil UKM disimpan.')->success()->send();
    }
}
