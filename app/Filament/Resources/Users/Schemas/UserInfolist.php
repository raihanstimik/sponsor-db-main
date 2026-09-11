<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Support\KlasifikasiTabel;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profil Pengguna')
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Grid::make(['default' => 1, 'sm' => 12])
                        ->schema([
                            ImageEntry::make('avatar_url')
                                ->label('')
                                ->circular()
                                ->disk('public')
                                ->columnSpan(['default' => 12, 'sm' => 3]),

                            Grid::make(1)
                                ->schema([
                                    TextEntry::make('name')
                                        ->label('Nama Lengkap')
                                        ->weight(FontWeight::Bold)
                                        ->size(TextSize::Large)
                                        ->color('primary'),
                                    TextEntry::make('email')
                                        ->label('Email')
                                        ->copyable()
                                        ->icon('heroicon-o-envelope'),
                                ])
                                ->columnSpan(['default' => 12, 'sm' => 9]),
                        ]),
                ]),

            Section::make('Akses & Organisasi')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    TextEntry::make('divisi.name')
                        ->label('Divisi Kerja')
                        ->placeholder('-')
                        ->badge()
                        ->color('gray'),

                    TextEntry::make('roles.name')
                        ->label('Peran / Role')
                        ->badge()
                        ->color(fn (string $state): string => KlasifikasiTabel::warnaRole($state)),

                    IconEntry::make('is_active')
                        ->label('Status Akun Aktif')
                        ->boolean(),

                    TextEntry::make('created_at')
                        ->label('Terdaftar Sejak')
                        ->dateTime('d F Y, H:i'),
                ]),
        ]);
    }
}
