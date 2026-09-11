<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans;

use App\Filament\Resources\Kegiatans\Pages\EditKegiatan;
use App\Filament\Resources\Kegiatans\Pages\ListKegiatans;
use App\Filament\Resources\Kegiatans\Pages\ViewKegiatan;
use App\Filament\Resources\Kegiatans\Schemas\KegiatanForm;
use App\Filament\Resources\Kegiatans\Schemas\KegiatanInfolist;
use App\Filament\Resources\Kegiatans\Tables\KegiatansTable;
use App\Models\Kegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class KegiatanResource extends Resource
{
    protected static ?string $model = Kegiatan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'nama_event';

    protected static ?string $navigationLabel = 'Kegiatan & Kategori';

    protected static ?string $pluralModelLabel = 'Kegiatan & Kategori';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_event', 'venue', 'kategoriKegiatan.nama_kategori'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Kegiatan $record */
        return array_filter([
            'Kategori' => $record->kategoriKegiatan?->nama_kategori,
            'Venue' => $record->venue,
        ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('kategoriKegiatan');
    }

    public static function form(Schema $schema): Schema
    {
        return KegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KegiatansTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KegiatanInfolist::configure($schema);
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
            'index' => ListKegiatans::route('/'),
            'view' => ViewKegiatan::route('/{record}'),
            'edit' => EditKegiatan::route('/{record}/edit'),
        ];
    }
}
