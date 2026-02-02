<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUsage extends EditRecord
{
    protected static string $resource = UsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (DeleteAction $action) {
                    if ($this->record->period->is_closed) {
                        Notification::make()
                            ->title('Tidak bisa menghapus')
                            ->body('Periode sudah ditutup. Pembelian tidak dapat dihapus.')
                            ->danger()
                            ->send();
                        $action->halt();
                    }
                }),
        ];
    }

    protected function beforeSave()
    {
        if ($this->record->period->is_closed) {
            Notification::make()
                ->title('Tidak bisa mengubah')
                ->body('Periode sudah ditutup. Penggunaan tidak dapat diubah.')
                ->danger()
                ->send();
            $this->halt();
        }
    }
}
