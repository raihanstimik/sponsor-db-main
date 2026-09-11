<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Tables;

use App\Support\FilamentTableHelper;
use App\Support\KlasifikasiTabel;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// app/Filament/Resources/Roles/Tables/RolesTable.php
class RolesTable
{
    public static function configure(Table $table): Table
    {
        FilamentTableHelper::applyDefaultPresets($table);

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Peran')
                    ->searchable()
                    ->weight('medium')
                    ->badge()
                    ->color(fn (string $state): string => KlasifikasiTabel::warnaRole($state)),
                TextColumn::make('permissions_count')
                    ->label('Jumlah Hak')
                    ->counts('permissions')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => ! in_array($record->name, ['admin', 'karyawan'])),
            ])
            ->defaultSort('name');
    }
}
