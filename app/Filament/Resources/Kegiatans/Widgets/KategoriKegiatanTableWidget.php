<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kegiatans\Widgets;

use App\Filament\Resources\KategoriKegiatans\Tables\KategoriKegiatansTable;
use App\Models\KategoriKegiatan;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class KategoriKegiatanTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    #[On('refresh-kategori-widget')]
    public function refreshWidget(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $table = KategoriKegiatansTable::configure($table);

        return $table
            ->query(fn (): Builder => KategoriKegiatan::query()->withCount('kegiatans'))
            ->heading('Kelola Kategori Spesialisasi')
            ->description('Daftar spesialisasi medis dan palet warna indikator untuk pengelompokan sponsor');
    }
}
