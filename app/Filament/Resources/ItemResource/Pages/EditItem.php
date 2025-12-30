<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['total_stock'] = $this->record->total_stock;
        
        return $data;
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['initial_stock']) && $data['initial_stock'] < 0) {
            Notification::make()
                ->warning()
                ->title('Stok awal tidak boleh negatif!')
                ->send();
            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Item berhasil diperbarui';
    }
}