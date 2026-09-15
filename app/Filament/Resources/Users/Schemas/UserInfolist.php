<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Kartu Profil & Detail Pengguna Terpadu (Simetris & Kompak)
                ViewEntry::make('profile_card')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->view('filament.infolists.entries.user-profile-header'),
            ]);
    }
}
