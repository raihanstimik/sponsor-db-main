<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Kontaks\KontakResource;
use App\Models\Kontak;
use App\Support\PhoneNormalizer;
use Filament\Actions\Action;
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
            ->heading(fn (): string => 'Kontak Baru & Menunggu Verifikasi ('.self::jumlahMenunggu().' Menunggu Dicek)')
            ->description('PIC terbaru hasil impor yang perlu dicek manual')
            // Eager load anti N+1; 5 baris terbaru, tanpa paginasi ala Stitch
            ->query(fn (): Builder => Kontak::query()
                ->with(['perusahaan', 'kegiatan', 'kategoriKegiatan'])
                ->where('status_verifikasi', 'perlu_dicek')
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
                TextColumn::make('status_verifikasi')
                    ->label('Status')
                    ->badge()
                    ->size(TextSize::ExtraSmall)
                    ->color('warning'),
            ])
            ->headerActions([
                Action::make('lihatSemua')
                    ->label('Lihat Semua')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->color('gray')
                    ->url(KontakResource::getUrl()),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('success')
                    ->url(fn (Kontak $record): string => self::whatsappUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Kontak $record): bool => filled($record->no_telepon)),
            ])
            ->emptyStateHeading('Tidak ada kontak menunggu verifikasi')
            ->emptyStateDescription('Semua kontak sudah terverifikasi atau non-aktif.')
            ->emptyStateIcon(Heroicon::OutlinedCheckBadge);
    }

    protected static function jumlahMenunggu(): int
    {
        return Kontak::query()->where('status_verifikasi', 'perlu_dicek')->count();
    }

    protected static function whatsappUrl(Kontak $record): string
    {
        $phone = PhoneNormalizer::normalize((string) $record->no_telepon);

        return $phone === '' ? '#' : 'https://wa.me/'.$phone;
    }
}
