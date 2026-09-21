<?php

declare(strict_types=1);

namespace App\Filament\Resources\Kontaks\Pages;

use App\Filament\Resources\Kontaks\KontakResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Icons\Heroicon;

class CreateKontak extends CreateRecord
{
    protected static string $resource = KontakResource::class;

    protected static ?string $title = 'Tambah Kontak Baru';

    protected static ?string $breadcrumb = 'Tambah';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = auth()->id();

        if (! empty($data['kegiatan_id'])) {
            $kegiatan = \App\Models\Kegiatan::find($data['kegiatan_id']);
            if ($kegiatan && empty($data['kategori_kegiatan_id'])) {
                $data['kategori_kegiatan_id'] = $kegiatan->kategori_kegiatan_id;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->kegiatan_id && $this->record->kegiatans()->count() === 0) {
            $this->record->kegiatans()->syncWithoutDetaching([$this->record->kegiatan_id]);
        }

        $firstKegiatan = $this->record->kegiatans()->first()
            ?? ($this->record->kegiatan_id ? \App\Models\Kegiatan::find($this->record->kegiatan_id) : null);

        if ($firstKegiatan) {
            $updates = [];
            if ($this->record->kegiatan_id !== $firstKegiatan->id) {
                $updates['kegiatan_id'] = $firstKegiatan->id;
            }
            if (! $this->record->kategori_kegiatan_id && $firstKegiatan->kategori_kegiatan_id) {
                $updates['kategori_kegiatan_id'] = $firstKegiatan->kategori_kegiatan_id;
            }
            if ($updates !== []) {
                $this->record->updateQuietly($updates);
            }
        }

        if (auth()->user()) {
            app(\App\Services\AppNotificationService::class)->notifyKontakBaru(
                auth()->user(),
                $this->record
            );
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Kontak Berhasil Ditambahkan')
            ->body('Data kontak PIC sponsorship telah berhasil disimpan.')
            ->success();
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan Kontak')
            ->icon(Heroicon::OutlinedCheck);
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Simpan & Tambah Lainnya')
            ->icon(Heroicon::OutlinedPlus);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
