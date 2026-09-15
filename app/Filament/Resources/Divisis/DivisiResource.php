<?php

declare(strict_types=1);

namespace App\Filament\Resources\Divisis;

use App\Filament\Resources\Divisis\Pages\ListDivisis;
use App\Filament\Resources\Divisis\Schemas\DivisiForm;
use App\Filament\Resources\Divisis\Schemas\DivisiInfolist;
use App\Filament\Resources\Divisis\Tables\DivisisTable;
use App\Models\Divisi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DivisiResource extends Resource
{
    protected static ?string $model = Divisi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Manajemen Divisi';

    protected static ?string $pluralModelLabel = 'Manajemen Divisi';

    protected static ?string $modelLabel = 'Divisi';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return DivisiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DivisisTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DivisiInfolist::configure($schema);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDivisis::route('/'),
        ];
    }
}

