<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Spatie\Permission\Models\Role;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Peran / Hak Akses')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    TextEntry::make('name')
                        ->label('Nama Peran')
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'admin' => 'primary',
                            'karyawan' => 'gray',
                            default => 'warning',
                        }),

                    Grid::make(2)
                        ->schema([
                            TextEntry::make('permissions_count')
                                ->label('Total Hak Akses')
                                ->state(fn (Role $record): string => $record->permissions()->count().' Permission')
                                ->badge()
                                ->color('primary'),

                            TextEntry::make('users_count')
                                ->label('Jumlah Pengguna Terkait')
                                ->state(fn (Role $record): string => $record->users()->count().' Akun')
                                ->badge()
                                ->color('success'),
                        ]),
                ]),

            Section::make('Daftar Hak Akses (Permissions)')
                ->icon('heroicon-o-key')
                ->collapsible()
                ->schema([
                    TextEntry::make('permissions.name')
                        ->label('')
                        ->badge()
                        ->color('gray')
                        ->placeholder('Tidak ada hak akses khusus yang ditetapkan.'),
                ]),
        ]);
    }
}
