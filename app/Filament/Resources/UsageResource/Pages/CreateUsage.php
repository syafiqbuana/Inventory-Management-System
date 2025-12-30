<?php

namespace App\Filament\Resources\UsageResource\Pages;

use App\Filament\Resources\UsageResource;
use App\Models\Usage;
use App\Models\Item; 
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateUsage extends CreateRecord
{
    protected static string $resource = UsageResource::class;
    protected array $usagesData = [];


    protected function checkStockAvailability(array $usagesData): void
    {
        foreach ($usagesData as $usageItem) {
            $itemId = $usageItem['item_id'];
            $qtyUsed = (int) $usageItem['qty'];

            if ($qtyUsed <= 0) {
                continue;
            }

            $item = Item::find($itemId);

            if (!$item) {
                Notification::make()->danger()->title('Barang tidak ditemukan.')->send();
                $this->halt();
            }


            $totalStockAvailable = $item->total_stock; 

            // Logika Pencegahan: Jika stok kurang
            if ($totalStockAvailable < $qtyUsed) {

                $itemName = $item->name ?? "ID {$itemId}";

                Notification::make()
                    ->title('Gagal Membuat Penggunaan')
                    ->body(__("Stok untuk item '{$itemName}' tidak mencukupi. Dibutuhkan: {$qtyUsed}, Tersedia: {$totalStockAvailable}."))
                    ->danger()
                    ->send();

                $this->halt();
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->usagesData = $data['usages'] ?? [];

        // 1. Panggil validasi stok. 
        $this->checkStockAvailability($this->usagesData);

        // 2. Lanjutkan logika untuk membuat record placeholder pertama
        if (!empty($this->usagesData)) {
            $data['item_id'] = $this->usagesData[0]['item_id'];
            $data['qty'] = $this->usagesData[0]['qty'];
            $data['used_for'] = $this->usagesData[0]['used_for'];
        } else {
             // Handle jika repeater kosong
             Notification::make()
                 ->title('Error Input')
                 ->body('Minimal harus ada satu item yang dicatat penggunaannya.')
                 ->danger()
                 ->send();
             $this->halt();
        }

        unset($data['usages']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $placeholderRecord = $this->getRecord();

        DB::transaction(function () use ($placeholderRecord) {

            $firstItemProcessed = false;

            // Proses record Usage pertama dan buat record Usage tambahan
            foreach ($this->usagesData as $usageItem) {

                if (!$firstItemProcessed) {
                    // Update record placeholder pertama
                    $placeholderRecord->item_id = $usageItem['item_id'];
                    $placeholderRecord->qty = $usageItem['qty'];
                    $placeholderRecord->used_for = $usageItem['used_for'];
                    $placeholderRecord->save();

                    $firstItemProcessed = true;

                } else {
                    // Buat record Usage baru
                    Usage::create([
                        'item_id' => $usageItem['item_id'],
                        'qty' => $usageItem['qty'],
                        'used_for' => $usageItem['used_for'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // LOGIKA PENGURANGAN STOK FIFO/handleStockDeduction() DIHAPUS 
            // karena tidak lagi relevan dengan logika stok dinamis.
        });

        // Notifikasi Sukses
        Notification::make()
            ->title('Penggunaan Berhasil')
            ->body('Catatan penggunaan berhasil disimpan. Stok telah diperbarui secara otomatis.')
            ->success()
            ->send();
    }
    


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}