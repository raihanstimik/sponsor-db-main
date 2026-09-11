<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks;

use App\Filament\Resources\Kontaks\Pages\CreateKontak;
use App\Filament\Resources\Kontaks\Pages\EditKontak;
use App\Filament\Resources\Kontaks\Pages\ListKontaks;
use App\Filament\Resources\Kontaks\Pages\ViewKontak;
use App\Filament\Resources\Kontaks\Schemas\KontakForm;
use App\Filament\Resources\Kontaks\Schemas\KontakInfolist;
use App\Filament\Resources\Kontaks\Tables\KontaksTable;
use App\Models\Kontak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KontakResource extends Resource
{
    protected static ?string $model = Kontak::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?string $navigationLabel = 'Kontak';

    protected static ?string $pluralModelLabel = 'Kontak';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama', 'no_telepon', 'email', 'perusahaan.nama_standar'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Kontak $record */
        return array_filter([
            'Perusahaan' => $record->perusahaan?->nama_standar,
            'No. HP' => $record->no_telepon,
            'Event' => $record->kegiatan?->nama_event,
        ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['perusahaan', 'kegiatan']);
    }

    public static function form(Schema $schema): Schema
    {
        return KontakForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KontaksTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KontakInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKontaks::route('/'),
            'create' => CreateKontak::route('/create'),
            'view' => ViewKontak::route('/{record}'),
            'edit' => EditKontak::route('/{record}/edit'),
        ];
    }
}
