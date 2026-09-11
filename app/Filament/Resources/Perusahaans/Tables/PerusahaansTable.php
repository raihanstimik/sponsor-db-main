<?php

declare(strict_types=1);

namespace App\Filament\Resources\Perusahaans\Tables;

use App\Models\Perusahaan;
use App\Support\FilamentTableHelper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class PerusahaansTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table);

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['updatedBy']))
            ->columns([
                TextColumn::make('nama_standar')
                    ->label('Perusahaan Sponsor Baku')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(function (Perusahaan $record): string {
                        $inisial = e($record->inisial);
                        $nama = e($record->nama_standar);

                        return <<<HTML
                        <div class="flex items-center gap-3 py-1">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#18225E] to-[#1E294B] text-white flex items-center justify-center font-bold text-sm shadow-sm flex-shrink-0 tracking-wider ring-1 ring-orange-500/20">
                                {$inisial}
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-slate-900 dark:text-white truncate">{$nama}</span>
                                    <span class="inline-flex items-center text-orange-500" title="Entitas Baku Terverifikasi">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                    </span>
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 truncate">Sponsor Kongres Medis</span>
                            </div>
                        </div>
                        HTML;
                    }),

                TextColumn::make('industri')
                    ->label('Bidang Industri')
                    ->badge()
                    ->color(fn (?string $state): string => match (true) {
                        str_contains(mb_strtolower((string) $state), 'farmasi') => 'warning',
                        str_contains(mb_strtolower((string) $state), 'alat')
                            || str_contains(mb_strtolower((string) $state), 'alkes')
                            || str_contains(mb_strtolower((string) $state), 'laser') => 'info',
                        str_contains(mb_strtolower((string) $state), 'nutrisi')
                            || str_contains(mb_strtolower((string) $state), 'gizi') => 'success',
                        str_contains(mb_strtolower((string) $state), 'estetik') => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('Umum')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kontaks_count')
                    ->label('PIC Terdaftar')
                    ->counts('kontaks')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state): string => $state.' PIC')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('latest_event')
                    ->label('Event Terakhir')
                    ->state(function (Perusahaan $record): ?string {
                        $latest = $record->kontaks()->with('kegiatan')->whereNotNull('kegiatan_id')->latest('id')->first();

                        return $latest?->kegiatan?->nama_event;
                    })
                    ->placeholder('-')
                    ->limit(24)
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Pembaruan')
                    ->description(fn (Perusahaan $record): ?string => $record->updatedBy?->name ? 'Oleh '.$record->updatedBy->name : null)
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('industri')
                    ->label('Bidang Industri')
                    ->options([
                        'Farmasi & Suplemen' => 'Farmasi & Suplemen',
                        'Alat Kesehatan & Diagnostik' => 'Alat Kesehatan & Diagnostik',
                        'Nutrisi & Gizi Klinis' => 'Nutrisi & Gizi Klinis',
                        'Estetika & Laser Medis' => 'Estetika & Laser Medis',
                        'Rumah Sakit & Layanan Kesehatan' => 'Rumah Sakit & Layanan Kesehatan',
                    ]),
                Filter::make('mitra_utama')
                    ->label('Mitra Utama (≥3 PIC)')
                    ->query(fn (Builder $query): Builder => $query->has('kontaks', '>=', 3)),
            ])
            ->paginated([15, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->recordUrl(null)
            ->recordAction(ViewAction::class)
            ->recordActions([
                Action::make('quick_pic')
                    ->label('')
                    ->tooltip('Daftar PIC Terhubung')
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->color('success')
                    ->modalHeading(fn (Perusahaan $record): string => 'Daftar PIC: '.$record->nama_standar)
                    ->modalWidth('xl')
                    ->modalContent(fn (Perusahaan $record): View => view('filament.modals.perusahaan-pic-modal', ['record' => $record]))
                    ->modalContent(fn (Perusahaan $record): View => view('filament.modals.perusahaan-pic-modal', [
                        'record' => $record,
                        'kontaks' => $record->kontaksForDetailModal(),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                ViewAction::make()
                    ->label('')
                    ->tooltip('Lihat Overview Perusahaan')
                    ->slideOver()
                    ->color('primary'),

                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->isAdmin()),
                ])
                    ->icon(Heroicon::OutlinedEllipsisHorizontal)
                    ->color('gray')
                    ->tooltip('Aksi Lainnya'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => (bool) auth()->user()?->isAdmin()),
                ]),
            ])
            ->defaultSort('nama_standar');
    }
}
