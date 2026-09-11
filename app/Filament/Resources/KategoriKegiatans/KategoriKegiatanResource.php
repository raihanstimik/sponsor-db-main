<?php

declare(strict_types=1);

namespace App\Filament\Resources\KategoriKegiatans;

use App\Filament\Resources\KategoriKegiatans\Pages\CreateKategoriKegiatan;
use App\Filament\Resources\KategoriKegiatans\Pages\EditKategoriKegiatan;
use App\Filament\Resources\KategoriKegiatans\Pages\ListKategoriKegiatans;
use App\Filament\Resources\KategoriKegiatans\Schemas\KategoriKegiatanForm;
use App\Filament\Resources\KategoriKegiatans\Schemas\KategoriKegiatanInfolist;
use App\Filament\Resources\KategoriKegiatans\Tables\KategoriKegiatansTable;
use App\Filament\Resources\Kegiatans\KegiatanResource;
use App\Models\KategoriKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class KategoriKegiatanResource extends Resource
{
    protected static ?string $model = KategoriKegiatan::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $recordTitleAttribute = 'nama_kategori';

    protected static ?string $navigationLabel = 'Kategori Kegiatan';

    protected static ?string $pluralModelLabel = 'Kategori Kegiatan';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return KegiatanResource::getUrl('index', ['tab' => 'kategori']);
    }

    public static function form(Schema $schema): Schema
    {
        return KategoriKegiatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KategoriKegiatansTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KategoriKegiatanInfolist::configure($schema);
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
            'index' => ListKategoriKegiatans::route('/'),
            'create' => CreateKategoriKegiatan::route('/create'),
            'edit' => EditKategoriKegiatan::route('/{record}/edit'),
        ];
    }
}
