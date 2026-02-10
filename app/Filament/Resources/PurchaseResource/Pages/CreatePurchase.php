<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\Purchase;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [];
    }
    public function savePurchaseItems()
    {
        $data = $this->form->getRawState();

        // Cek apakah tab pertama memiliki data
        if (blank($data['purchaseItems'] ?? [])) {
            Notification::make()
                ->danger()
                ->title('Isi minimal satu item pengadaan di Tab Pengadaan Barang')
                ->send();
            return;
        }

        // Validasi: pastikan semua item memiliki item_id
        $validItems = collect($data['purchaseItems'])
            ->filter(fn($item) => filled($item['item_id']))
            ->values();

        if ($validItems->isEmpty()) {
            Notification::make()
                ->danger()
                ->title('Isi minimal satu item pengadaan yang valid')
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            // Simpan purchase untuk tab pertama
            $purchase = Purchase::create([
                'note' => $data['note'] ?? '-',
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $data['total_amount'] ?? 0,
            ]);

            // Simpan item-item dari tab pertama
            foreach ($validItems as $item) {
                $purchase->purchaseItems()->create($item);
            }

            DB::commit();

            Notification::make()
                ->success()
                ->title('Pengadaan barang berhasil dicatat')
                ->send();

            // Reset form atau redirect
            $this->redirect($this->getRedirectUrl());

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->danger()
                ->title('Gagal menyimpan pengadaan')
                ->body($e->getMessage())
                ->send();
        }
    }
    public function saveNewPurchaseItems()
    {
        $raw = $this->form->getRawState();

        // Cek apakah tab kedua memiliki data
        $items = collect($raw['extraPurchaseItems'] ?? [])
            ->filter(fn($item) => filled($item['item_id']))
            ->values();

        if ($items->isEmpty()) {
            Notification::make()
                ->danger()
                ->title('Isi minimal satu item pengadaan di Tab Pengadaan Barang Baru')
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            // Simpan purchase untuk tab kedua
            $purchase = Purchase::create([
                'note' => $raw['note_tab2'] ?? '-',
                'purchase_date' => $raw['purchase_date'] ?? now(),
                'total_amount' => $raw['total_amount_tab2'] ?? 0,
            ]);

            // Simpan item-item dari tab kedua dan update price item master
            foreach ($items as $item) {
                $purchase->purchaseItems()->create($item);
                
                // Update item master untuk barang baru
                $itemModel = Item::find($item['item_id']);
                if ($itemModel) {
                    // Jika ini pengadaan pertama (price = 0)
                    // HANYA update price, JANGAN update initial_stock
                    if ($itemModel->price == 0) {
                        $itemModel->update([
                            'price' => $item['unit_price'], // Set price dari unit_price pengadaan
                        ]);
                    }
                    // Jika price sudah ada, tidak perlu update apapun
                    // Stock akan otomatis terhitung dari purchased_qty melalui relasi
                }
            }

            DB::commit();

            Notification::make()
                ->success()
                ->title('Pengadaan barang baru berhasil dicatat')
                ->body('Dapat dibeli ulang melalui Tab Pengadaan Barang')
                ->send();

            // Reset form atau redirect
            $this->redirect($this->getRedirectUrl());

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->danger()
                ->title('Gagal menyimpan pengadaan barang baru')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function isNewItemPurchaseTabActive(array $data): bool
    {
        return collect($data['extraPurchaseItems'] ?? [])
            ->contains(fn($item) => filled($item['item_id']));
    }
}