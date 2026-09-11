<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks\Tables;

use App\Filament\Pages\ImportKontaks;
use App\Models\Kegiatan;
use App\Models\Kontak;
use App\Models\Perusahaan;
use App\Services\KontakSmartSearch;
use App\Services\PetaNomorPerusahaan;
use App\Support\FilamentTableHelper;
use App\Support\KlasifikasiTabel;
use App\Support\PhoneNormalizer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\ColumnManagerLayout;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\Paginator as ContractsPaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class KontaksTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table, [25, 50, 100, 250, 500], 50);

        return $table
            // Eager load anti N+1, withCount siap jika perlu agregat
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['perusahaan', 'kegiatan', 'kategoriKegiatan', 'updatedBy']))
            ->searchable(false)
            ->striped()
            ->header(fn (HasTable $livewire): View => view('filament.tables.kontak-summary', [
                'cards' => self::summaryCards($livewire),
                'warnaBaris' => self::warnaBarisAktif($livewire),
            ]))
            ->columnManagerLayout(ColumnManagerLayout::Modal)
            ->deferColumnManager(false)
            ->columnManagerColumns(2)
            ->reorderableColumns()
            ->recordClasses(function (Kontak $record, HasTable $livewire): ?string {
                // Duplikat prioritas danger (admin)
                if (auth()->user()?->isAdmin() && PetaNomorPerusahaan::untukKontak($record, $livewire->petaNomorDipakai()) !== []) {
                    return 'bg-danger-500/10 dark:bg-danger-500/20';
                }

                return self::kelasWarnaBaris(self::warnaEfektifBaris($record));
            })
            ->emptyStateHeading('Belum ada kontak')
            ->emptyStateDescription('Mulai dengan mengimpor file kontak sponsor (Excel/CSV) atau membuat kontak baru satu per satu.')
            ->emptyStateIcon(Heroicon::OutlinedInbox)
            ->emptyStateActions([
                Action::make('importKontak')
                    ->label('Import Data')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->url(ImportKontaks::getUrl())
                    ->button(),
            ])
            ->columns([
                TextColumn::make('No')
                    ->label('No')
                    ->rowIndex()
                    ->size(TextSize::Small)
                    ->alignCenter()
                    ->extraAttributes(['style' => 'min-width: 3rem'])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('perusahaan.nama_standar')
                    ->label('Nama Perusahaan')
                    ->size(TextSize::Small)
                    ->weight('bold')
                    ->color('gray')
                    ->extraAttributes(['class' => 'text-slate-900 dark:text-white'])
                    ->searchable()
                    ->sortable()
                    ->limit(32)
                    ->tooltip(fn (Kontak $record): string => $record->perusahaan?->nama_standar ?? '')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('nama')
                    ->label('PIC')
                    ->size(TextSize::Small)
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(28)
                    ->tooltip(fn (Kontak $record): string => $record->nama ?? '')
                    ->description(fn (Kontak $record): ?string => $record->kategoriKegiatan?->nama_kategori)
                    ->placeholder('(Tanpa Nama)')
                    ->toggleable(),
                TextColumn::make('no_telepon')
                    ->label('No. Telepon')
                    ->size(TextSize::Small)
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon(Heroicon::OutlinedPhone)
                    ->iconPosition(IconPosition::After)
                    ->url(fn (Kontak $record): ?string => filled($record->no_telepon) ? 'tel:'.$record->no_telepon : null)
                    ->extraAttributes(['style' => 'white-space: nowrap'])
                    ->toggleable(),
                TextColumn::make('kegiatan.nama_event')
                    ->label('Kegiatan')
                    ->size(TextSize::Small)
                    ->searchable()
                    ->badge()
                    ->color(fn (Kontak $record): ?string => $record->kegiatan?->warna ?? $record->kategoriKegiatan?->warna)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $arah = $direction === 'desc' ? 'desc' : 'asc';

                        return $query
                            ->leftJoin('kegiatans as sort_kegiatan', 'sort_kegiatan.id', '=', 'kontaks.kegiatan_id')
                            ->leftJoin('kategori_kegiatans as sort_kategori', 'sort_kategori.id', '=', 'sort_kegiatan.kategori_kegiatan_id')
                            ->orderByRaw('coalesce(sort_kegiatan.warna, sort_kategori.warna) '.$arah)
                            ->orderBy('sort_kegiatan.nama_event', $arah);
                    })
                    ->placeholder('-')
                    ->limit(26)
                    ->tooltip(fn (Kontak $record): ?string => $record->kegiatan?->nama_event)
                    ->description(fn (Kontak $record): ?string => $record->kegiatan?->tanggal_mulai?->format('Y'))
                    ->toggleable(),
                TextColumn::make('kategoriKegiatan.nama_kategori')
                    ->label('Kategori')
                    ->size(TextSize::Small)
                    ->searchable()
                    ->badge()
                    ->color(fn (Kontak $record): ?string => $record->kategoriKegiatan?->warna ?? 'primary')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $arah = $direction === 'desc' ? 'desc' : 'asc';

                        return $query
                            ->leftJoin('kategori_kegiatans as sort_kategori', 'sort_kategori.id', '=', 'kontaks.kategori_kegiatan_id')
                            ->orderBy('sort_kategori.warna', $arah)
                            ->orderBy('sort_kategori.nama_kategori', $arah);
                    })
                    ->placeholder('-')
                    ->limit(28)
                    ->tooltip(fn (Kontak $record): ?string => $record->kategoriKegiatan?->nama_kategori)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('klasifikasi_cari')
                    ->label('Cocok pada')
                    ->badge()
                    ->color('primary')
                    ->size(TextSize::ExtraSmall)
                    ->placeholder('-')
                    ->state(function (Kontak $record, HasTable $livewire): ?string {
                        $state = $livewire->getTableFilterState('cari') ?? [];
                        $q = trim((string) ($state['q'] ?? ''));

                        if ($q === '') {
                            return null;
                        }

                        return implode(' + ', app(KontakSmartSearch::class)->matchColumns($record, $q));
                    })
                    ->visible(fn (HasTable $livewire): bool => filled(trim((string) ($livewire->getTableFilterState('cari')['q'] ?? ''))))
                    ->toggleable(),
                TextColumn::make('updatedBy.name')
                    ->label('Diperbarui oleh')
                    ->size(TextSize::ExtraSmall)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('nomor_dipakai_perusahaan_lain')
                    ->label('Nomor Dipakai Perusahaan Lain')
                    ->badge()
                    ->color('danger')
                    ->size(TextSize::ExtraSmall)
                    ->state(fn (Kontak $record, HasTable $livewire): ?array => PetaNomorPerusahaan::untukKontak($record, $livewire->petaNomorDipakai()))
                    ->formatStateUsing(fn (?array $state): ?string => $state ? implode('; ', $state) : null)
                    ->placeholder('-')
                    ->visible(fn (): bool => (bool) auth()->user()?->isAdmin())
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('catatan')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn (Kontak $record): string => $record->catatan ?? '')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('cari')
                    ->label('Pencarian')
                    ->schema([
                        TextInput::make('q')
                            ->label('Kata kunci')
                            ->placeholder('Cari perusahaan, PIC, nomor, kegiatan, kategori...')
                            ->live()
                            ->debounce(600),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => filled(trim((string) ($data['q'] ?? '')))
                        ? app(KontakSmartSearch::class)->applyTo($query, trim((string) $data['q']))
                        : $query),
                SelectFilter::make('kegiatan_id')
                    ->label('Kegiatan')
                    ->relationship('kegiatan', 'nama_event')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->modifyFormFieldUsing(fn (Select $field) => $field->extraAttributes(['class' => 'fi-filter-kegiatan-compact']))
                    ->indicateUsing(function (array $data, array $state): ?array {
                        $vals = $state['values'] ?? $state['value'] ?? $data['values'] ?? $data['value'] ?? [];
                        $vals = is_array($vals) ? array_filter($vals) : array_filter([$vals]);
                        if (blank($vals)) {
                            return null;
                        }

                        return ['Kegiatan: '.count($vals).' terpilih'];
                    }),
                SelectFilter::make('kategori_kegiatan_id')
                    ->label('Kategori')
                    ->relationship('kategoriKegiatan', 'nama_kategori')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->modifyFormFieldUsing(fn (Select $field) => $field->extraAttributes(['class' => 'fi-filter-kategori-compact']))
                    ->indicateUsing(function (array $data, array $state): ?array {
                        $vals = $state['values'] ?? $state['value'] ?? $data['values'] ?? $data['value'] ?? [];
                        $vals = is_array($vals) ? array_filter($vals) : array_filter([$vals]);
                        if (blank($vals)) {
                            return null;
                        }

                        return ['Kategori: '.count($vals).' terpilih'];
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->filtersFormColumns(['sm' => 1, 'md' => 3, 'lg' => 3, 'xl' => 3, '2xl' => 3])
            ->paginated([25, 50, 100, 250, 500])
            ->defaultPaginationPageOption(50)
            ->recordActions([
                Action::make('quick_whatsapp')
                    ->label('Kirim WhatsApp')
                    ->hiddenLabel()
                    ->tooltip('Kirim WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (Kontak $record): string => self::whatsappUrl($record))
                    ->openUrlInNewTab()
                    ->extraAttributes(fn (Kontak $record): array => [
                        'aria-label' => 'Kirim WhatsApp ke '.(filled($record->nama) ? $record->nama : 'kontak'),
                    ])
                    ->visible(fn (Kontak $record): bool => filled($record->no_telepon)),
                ActionGroup::make([
                    ViewAction::make()
                        ->slideOver()
                        ->modalWidth('3xl')
                        ->extraModalFooterActions([
                            Action::make('slideover_whatsapp')
                                ->label('Kirim WhatsApp')
                                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                                ->color('success')
                                ->url(fn (Kontak $record): string => self::whatsappUrl($record))
                                ->openUrlInNewTab()
                                ->visible(fn (Kontak $record): bool => filled($record->no_telepon)),
                        ]),
                    EditAction::make(),
                    Action::make('whatsapp')
                        ->label('Kirim WhatsApp')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->color('success')
                        ->url(fn (Kontak $record): string => self::whatsappUrl($record))
                        ->openUrlInNewTab()
                        ->visible(fn (Kontak $record): bool => filled($record->no_telepon)),
                    DeleteAction::make(),
                ])->icon(Heroicon::OutlinedEllipsisHorizontal)->color('gray')->tooltip('Aksi'),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Ekspor CSV')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->can('export', Kontak::class) ?? false)
                    ->url(fn (HasTable $livewire): string => route('kontaks.export', self::exportParams($livewire))),
            ])
            ->defaultSort('nama');
    }

    /**
     * Warna efektif pewarnaan baris: warna kegiatan, bila kosong mewarisi
     * warna kategorinya. Baris dengan kegiatan/kategori yang sama otomatis
     * berbagi warna yang sama.
     */
    public static function warnaEfektifBaris(Kontak $record): ?string
    {
        return $record->kegiatan?->warna
            ?? $record->kategoriKegiatan?->warna
            ?? ($record->kategoriKegiatan ? KlasifikasiTabel::warnaKategori($record->kategoriKegiatan->nama_kategori) : null);
    }

    /**
     * Kelas CSS baris untuk nilai warna hex (#RRGGBB). Null bila format tidak
     * dikenal agar nilai liar tidak pernah bocor ke stylesheet.
     */
    public static function kelasWarnaBaris(?string $hex): ?string
    {
        if ($hex === null || preg_match('/^#[0-9a-fA-F]{6}$/', $hex) !== 1) {
            return null;
        }

        return 'baris-warna-'.strtolower(substr($hex, 1));
    }

    /**
     * Daftar warna unik dari records halaman aktif (relasi sudah eager-load),
     * dipakai header tabel untuk menghasilkan aturan <style> pewarnaan baris.
     *
     * @return array<int, string>
     */
    public static function warnaBarisAktif(HasTable $livewire): array
    {
        $records = $livewire->getTableRecords();

        $items = collect($records instanceof ContractsPaginator ? $records->items() : $records);

        return $items
            ->map(fn ($record): ?string => self::warnaEfektifBaris($record))
            ->filter(fn (?string $hex): bool => $hex !== null && preg_match('/^#[0-9a-fA-F]{6}$/', $hex) === 1)
            ->map(fn (string $hex): string => strtolower($hex))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Ringkasan yang mengikuti filter aktif pada tabel. Ditampilkan sebagai
     * strip statistik di atas tabel (bukan footer bawaan Filament).
     *
     * @return array<int, array{key: string, label: string, icon: string, color: string, count: int}>
     */
    public static function summaryCards(HasTable $livewire): array
    {
        $query = $livewire->getFilteredTableQuery();

        if (! $query) {
            return [];
        }

        // Satu query agregat menggantikan lima COUNT terpisah.
        // Satu query agregat kontak untuk total dan validitas nomor.
        $ringkas = (clone $query)
            ->selectRaw('count(*) as total, sum(case when status_format_valid = 1 then 1 else 0 end) as valid')
            ->first();

        if ($ringkas === null) {
            return [];
        }

        $totalPerusahaan = Perusahaan::count();
        $totalKegiatan = Kegiatan::count();

        return [
            [
                'key' => 'total',
                'label' => 'Total kontak',
                'icon' => 'heroicon-o-users',
                'color' => '#18225E',
                'count' => (int) $ringkas->total,
            ],
            [
                'key' => 'valid',
                'label' => 'Nomor HP valid',
                'icon' => 'heroicon-o-phone',
                'color' => '#10B981',
                'count' => (int) $ringkas->valid,
            ],
            [
                'key' => 'perusahaan',
                'label' => 'Perusahaan',
                'icon' => 'heroicon-o-building-office-2',
                'color' => '#EA7C1A',
                'count' => $totalPerusahaan,
            ],
            [
                'key' => 'kegiatan',
                'label' => 'Kegiatan / Event',
                'icon' => 'heroicon-o-calendar-days',
                'color' => '#0284C7',
                'count' => $totalKegiatan,
            ],
        ];
    }

    protected static function exportParams(HasTable $livewire): array
    {
        $params = [];

        $q = trim((string) (data_get($livewire->getTableFilterState('cari'), 'q') ?? ''));
        if ($q !== '') {
            $params['q'] = $q;
        }

        foreach ([
            'kegiatan_id' => 'kegiatan_id',
            'kategori_kegiatan_id' => 'kategori_kegiatan_id',
        ] as $queryKey => $filterName) {
            $raw = data_get($livewire->getTableFilterState($filterName), 'values') ?? data_get($livewire->getTableFilterState($filterName), 'value') ?? null;
            if (is_array($raw)) {
                $filtered = array_values(array_filter($raw, fn ($v) => $v !== '' && $v !== null));
                if ($filtered !== []) {
                    $params[$queryKey] = $filtered;
                }
            } else {
                $value = (string) ($raw ?? '');
                if ($value !== '') {
                    $params[$queryKey] = $value;
                }
            }
        }

        return $params;
    }

    public static function whatsappUrl(Kontak $record): string
    {
        $nama = trim((string) ($record->nama ?? ''));
        $sapaan = $nama !== '' ? "Halo Bapak/Ibu {$nama}, " : 'Halo Bapak/Ibu, ';
        $event = $record->kegiatan?->nama_event;
        $pesan = $sapaan.($event ? "saya dari tim sponsorship terkait kegiatan {$event}." : 'saya dari tim sponsorship.');

        return PhoneNormalizer::whatsappUrl($record->no_telepon, $pesan);
    }
}
