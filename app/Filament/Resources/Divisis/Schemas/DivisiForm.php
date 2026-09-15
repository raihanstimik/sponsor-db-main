<?php

declare(strict_types=1);

namespace App\Filament\Resources\Divisis\Schemas;

use App\Models\Divisi;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DivisiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nama Divisi')
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'divisis', column: 'name', ignorable: fn (?Divisi $record) => $record)
                    ->placeholder('contoh: IT & Multimedia, Keuangan, dsb.')
                    ->prefixIcon('heroicon-m-building-office-2')
                    ->autofocus(),

                Textarea::make('description')
                    ->label('Deskripsi & Tugas Pokok')
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('Tuliskan ringkasan fungsi, tanggung jawab, atau cakupan kerja divisi ini...')
                    ->columnSpanFull(),
            ]);
    }
}

