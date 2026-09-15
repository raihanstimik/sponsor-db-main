<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Manajemen Akun';

    public function getBreadcrumbs(): array
    {
        return [
            UserResource::getUrl() => 'Manajemen Akun',
            'Daftar',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Akun')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        $pendingCount = User::query()->where('is_active', false)->count();

        return [
            'semua' => Tab::make('Semua Akun'),
            'aktif' => Tab::make('Aktif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'pending' => Tab::make('Menunggu Persetujuan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('warning'),
        ];
    }
}
