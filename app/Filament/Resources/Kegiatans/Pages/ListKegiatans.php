<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Pages;

use App\Actions\KategoriKegiatan\CreateKategoriKegiatanAction;
use App\Filament\Resources\Kegiatans\KegiatanResource;
use App\Filament\Resources\Kegiatans\Widgets\KegiatanStatsOverview;
use App\Models\KategoriKegiatan;
use App\Models\Kegiatan;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class ListKegiatans extends ListRecords
{
    protected static string $resource = KegiatanResource::class;

    protected string $view = 'filament.resources.kegiatans.pages.list-kegiatans';

    #[Url(as: 'tab')]
    public string $tab = 'kegiatan';

    public function getTitle(): string
    {
        return 'Kegiatan Kongres & Kategori Medis';
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['kegiatan', 'kategori'], true) ? $tab : 'kegiatan';
    }

    public function mountAction(string $name, array $arguments = [], array $context = []): mixed
    {
        if ($name === 'createKategori') {
            $this->tab = 'kategori';
        }

        return parent::mountAction($name, $arguments, $context);
    }

    public function getTotalKegiatan(): int
    {
        return Kegiatan::count();
    }

    public function getTotalKategori(): int
    {
        return KategoriKegiatan::count();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kegiatan')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->visible(fn (): bool => ((auth()->user()?->can('create', Kegiatan::class) ?? false) || (bool) auth()->user()?->isAdmin()))
                ->extraAttributes([
                    'x-show' => "\$store.kegiatanTab?.tab === 'kegiatan'",
                    'x-cloak' => '',
                    'style' => $this->tab === 'kegiatan' ? '' : 'display: none !important;',
                ]),

            Action::make('createKategori')
                ->label('Tambah Kategori')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->color('primary')
                ->modalHeading('Tambah Kategori Medis Baru')
                ->modalDescription('Buat kategori spesialisasi kedokteran baru secara instan.')
                ->modalWidth('md')
                ->slideOver()
                ->modalSubmitActionLabel('Simpan Kategori')
                ->modalCancelActionLabel('Batal')
                ->visible(fn (): bool => ((auth()->user()?->can('create', KategoriKegiatan::class) ?? false) || (bool) auth()->user()?->isAdmin()))
                ->extraAttributes([
                    'x-show' => "\$store.kegiatanTab?.tab === 'kategori'",
                    'x-cloak' => '',
                    'style' => $this->tab === 'kategori' ? '' : 'display: none !important;',
                ])
                ->schema([
                    TextInput::make('nama_kategori')
                        ->label('Nama Kategori')
                        ->placeholder('contoh: Onkologi & Ginekologi')
                        ->prefixIcon('heroicon-m-tag')
                        ->required()
                        ->unique('kategori_kegiatans', 'nama_kategori')
                        ->maxLength(255),
                    ColorPicker::make('warna')
                        ->label('Warna Indikator')
                        ->helperText('Warna khas untuk badge dan grafik distribusi kategori.')
                        ->default(fn () => \App\Support\KlasifikasiTabel::warnaKategori('')),
                    Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->placeholder('Penjelasan ringkas spesialisasi medis ini...')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    app(CreateKategoriKegiatanAction::class)->execute($data);

                    Notification::make()
                        ->title('Kategori Medis Berhasil Ditambahkan')
                        ->success()
                        ->send();

                    $this->dispatch('refresh-kategori-widget');
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KegiatanStatsOverview::class,
        ];
    }
}