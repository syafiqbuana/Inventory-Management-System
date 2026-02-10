<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;


class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;
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

    protected function beforeSave(){
        if($this->record->period->is_closed){
            Notification::make()
                ->title('Tidak bisa mengubah')
                ->body('Periode sudah ditutup. Pembelian tidak dapat diubah.')
                ->danger()
                ->send();
            $this->halt();
        }
    }
}