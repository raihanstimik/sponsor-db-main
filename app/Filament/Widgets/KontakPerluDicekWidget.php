<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Kontaks\KontakResource;
use App\Filament\Resources\Kontaks\Schemas\KontakInfolist;
use App\Models\Kontak;
use App\Support\PhoneNormalizer;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class KontakPerluDicekWidget extends TableWidget
{
    protected static ?int $sort = -96;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Kontak PIC Terbaru')
            ->description('5 kontak PIC terbaru yang terdaftar dalam sistem')
            ->query(fn (): Builder => Kontak::query()
                ->with(['perusahaan', 'kegiatan', 'kategoriKegiatan'])
                ->orderByDesc('id')
                ->limit(5))
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('perusahaan.nama_standar')
                    ->label('Perusahaan')
                    ->size(TextSize::Small)
                    ->weight('bold')
                    ->limit(24)
                    ->placeholder('-'),
                TextColumn::make('nama')
                    ->label('PIC')
                    ->size(TextSize::Small)
                    ->weight('medium')
                    ->placeholder('(Tanpa Nama)')
                    ->limit(24),
                TextColumn::make('no_telepon')
                    ->label('No. Telepon')
                    ->size(TextSize::Small)
                    ->copyable()
                    ->icon(Heroicon::OutlinedPhone)
                    ->iconPosition(IconPosition::After),
                TextColumn::make('kegiatan.nama_event')
                    ->label('Kegiatan')
                    ->size(TextSize::Small)
                    ->badge()
                    ->color(fn (Kontak $record): ?string => $record->kegiatan?->warna ?? $record->kategoriKegiatan?->warna)
                    ->placeholder('-')
                    ->limit(22),
            ])
            ->headerActions([
                Action::make('lihatSemua')
                    ->label('Lihat Semua')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->color('gray')
                    ->url(KontakResource::getUrl()),
            ])
            ->recordUrl(null)
            ->recordAction(ViewAction::class)
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->modalWidth('3xl')
                    ->schema(fn (Schema $schema): Schema => KontakInfolist::configure($schema)),
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (Kontak $record): string => self::whatsappUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Kontak $record): bool => filled($record->no_telepon)),
            ])
            ->emptyStateHeading('Belum ada kontak')
            ->emptyStateDescription('Kontak PIC yang baru ditambahkan akan muncul di sini.')
            ->emptyStateIcon(Heroicon::OutlinedUsers);
    }

    protected static function whatsappUrl(Kontak $record): string
    {
        return PhoneNormalizer::whatsappUrl($record->no_telepon);
    }
}
