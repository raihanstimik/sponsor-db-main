<?php

declare(strict_types=1);

namespace App\Filament\Resources\Divisis\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class DivisiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ViewEntry::make('divisi_card')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->view('filament.infolists.entries.divisi-card'),
            ]);
    }
}

