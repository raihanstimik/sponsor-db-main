<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks\Pages;

use App\Filament\Resources\Kontaks\KontakResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditKontak extends EditRecord
{
    protected static string $resource = KontakResource::class;

    protected static ?string $title = 'Ubah Data Kontak';

    protected static ?string $breadcrumb = 'Ubah';

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterSave(): void
    {
        $firstKegiatanId = $this->record->kegiatans()->first()?->id;
        if ($this->record->kegiatan_id !== $firstKegiatanId) {
            $this->record->updateQuietly(['kegiatan_id' => $firstKegiatanId]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Kontak Diperbarui')
            ->body('Perubahan informasi kontak telah berhasil disimpan.')
            ->success();
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan Perubahan')
            ->icon(Heroicon::OutlinedCheck);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus Kontak')
                ->modalHeading('Hapus Data Kontak Sponsor')
                ->modalDescription('Apakah Anda yakin ingin menghapus kontak ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal')
                ->successNotificationTitle('Kontak berhasil dihapus'),
        ];
    }
}
