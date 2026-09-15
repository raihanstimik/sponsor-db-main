<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Support\FilamentTableHelper;
use App\Support\KlasifikasiTabel;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Lab404\Impersonate\Services\ImpersonateManager;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table);

        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=18225E&background=E0E7FF&bold=true')
                    ->extraImgAttributes(['alt' => 'Avatar']),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('gray'),

                TextColumn::make('divisi.name')
                    ->label('Divisi')
                    ->placeholder('-')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => KlasifikasiTabel::warnaRole($state))
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Menunggu Approval')
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('joined_at')
                    ->label('Bergabung')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'name'),
                SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Status Akun')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif (Approved)')
                    ->falseLabel('Menunggu Persetujuan'),
            ])
            ->recordUrl(null)
            ->recordAction(ViewAction::class)
            ->recordActions([
                Action::make('quick_approve')
                    ->label('Setujui')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->button()
                    ->size('xs')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Akun Karyawan')
                    ->modalDescription(fn (User $record) => 'Setujui '.$record->name.'? Akun akan aktif dan bisa login ke sistem.')
                    ->visible(fn (User $record) => ! $record->is_active && (bool) auth()->user()?->isAdmin())
                    ->action(function (User $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()->title('Akun '.$record->name.' disetujui')->success()->send();
                    }),

                ViewAction::make()
                    ->label('')
                    ->icon('heroicon-m-eye')
                    ->tooltip('Lihat Detail Akun')
                    ->slideOver()
                    ->modalHeading(fn (User $record): string => 'Detail Akun: '.$record->name)
                    ->modalWidth('md')
                    ->modalCancelAction(fn (Action $action) => $action->label('Tutup'))
                    ->modalSubmitAction(false)
                    ->color('gray'),

                EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->tooltip('Ubah Data Akun')
                    ->color('primary'),

                ActionGroup::make([
                    Action::make('approve')
                        ->label('Setujui Akun')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Akun Karyawan')
                        ->modalDescription(fn (User $record) => 'Setujui '.$record->name.'? Akun akan aktif dan bisa login.')
                        ->visible(fn (User $record) => ! $record->is_active && (bool) auth()->user()?->isAdmin())
                        ->action(function (User $record) {
                            $record->update(['is_active' => true]);
                            Notification::make()->title('Akun '.$record->name.' disetujui')->success()->send();
                        }),

                    Action::make('reject')
                        ->label('Tolak & Hapus')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Akun')
                        ->modalDescription(fn (User $record) => 'Tolak dan hapus calon akun '.$record->name.'?')
                        ->visible(fn (User $record) => ! $record->is_active && (bool) auth()->user()?->isAdmin())
                        ->action(function (User $record) {
                            $record->delete();
                            Notification::make()->title('Akun ditolak & dihapus')->success()->send();
                        }),

                    Action::make('impersonate')
                        ->label('Login Sebagai Karyawan')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Impersonate Karyawan')
                        ->modalDescription(fn (User $record) => 'Login sebagai '.$record->name.'? Anda akan beralih ke sesi karyawan tersebut.')
                        ->visible(fn (User $record) => auth()->user()?->canImpersonate() && $record->canBeImpersonated())
                        ->action(function (User $record) {
                            $manager = app(ImpersonateManager::class);
                            $manager->take(auth()->user(), $record);

                            return redirect()->to('/admin');
                        }),

                    DeleteAction::make()
                        ->hidden(fn (User $record) => $record->id === auth()->id()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('Menu Aksi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $filtered = $records->reject(fn (User $user) => $user->id === auth()->id());
                            $deletedCount = $filtered->count();
                            $filtered->each->delete();

                            if ($records->contains('id', auth()->id())) {
                                Notification::make()
                                    ->title('Akun Anda sendiri dilewati demi keamanan.')
                                    ->warning()
                                    ->send();
                            }

                            if ($deletedCount > 0) {
                                Notification::make()
                                    ->title("{$deletedCount} akun berhasil dihapus.")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->defaultSort('name');
    }
}
