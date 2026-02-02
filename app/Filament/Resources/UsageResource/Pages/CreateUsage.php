<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class CreateUsage extends CreateRecord
{
    protected static string $resource = UsageResource::class;

    /**
     * Redirect setelah create
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Mutate data sebelum disimpan ke database
     */



    /**
     * Hook setelah data dibuat
     */



    protected function beforeCreate(): void
    {
        DB::transaction(function () {
            foreach ($this->data['usageItems'] as $usageItemData) {
                $item = Item::query()
                    ->withSum('purchaseItems', 'qty')
                    ->withSum('usageItems', 'qty')
                    ->lockForUpdate() 
                    ->find($usageItemData['item_id']);

                $availableStock =
                    $item->initial_stock
                    + ($item->purchase_items_sum_qty ?? 0)
                    - ($item->usage_items_sum_qty ?? 0);

                if ($usageItemData['qty'] > $availableStock) {
                    Notification::make()
                        ->title('Stok Tidak Cukup')
                        ->body("Stok {$item->name} hanya tersedia {$availableStock}")
                        ->danger()
                        ->send();

                    $this->halt();
                }
            }
        });
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data penggunaan berhasil ditambahkan';
    }
}