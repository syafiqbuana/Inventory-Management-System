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
        $activePeriod = \App\Models\Period::active();

        foreach ($this->data['usageItems'] as $usageItemData) {
            $item = Item::query()
                ->lockForUpdate()
                ->withSum([
                    'purchaseItems as purchased_qty' => fn ($q) =>
                        $q->whereHas(
                            'purchase',
                            fn ($p) => $p->where('period_id', $activePeriod->id)
                        )
                ], 'qty')
                ->withSum([
                    'usageItems as used_qty' => fn ($q) =>
                        $q->whereHas(
                            'usage',
                            fn ($u) => $u->where('period_id', $activePeriod->id)
                        )
                ], 'qty')
                ->findOrFail($usageItemData['item_id']);

            $availableStock =
                ($item->initial_period_id === $activePeriod->id
                    ? $item->initial_stock
                    : 0)
                + ($item->purchased_qty ?? 0)
                - ($item->used_qty ?? 0);

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