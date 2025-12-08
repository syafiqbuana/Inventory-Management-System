<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Balance;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
 // Tambahkan ini jika ingin memastikan transaksi

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $balance = Balance::first();

        if (!$balance) {
            Notification::make()->danger()->title('Saldo belum dibuat!')->send();
            $this->halt();
        }
        $total = floatval($data['total_amount'] ?? 0);

        if ($balance ->amount < $total) {
            Notification::make()->danger()
                ->title('Saldo tidak mencukupi untuk pembelian ini!')
                ->body("Saldo saat ini: {$balance->amount}, tetapi total pembelian adalah: {$total}.")
                ->send();
            $this->halt();
        }
        if ($total <= 0) {
            Notification::make()->warning()
                ->title('Total pembelian harus lebih dari 0!')
                ->send();
            $this->halt();
        }
        return $data;

    }

    protected function afterCreate(): void
    {
        // $this->record sekarang memiliki total_amount yang benar
        $totalAmount = $this->record->total_amount; 

        // Kurangi Saldo
        $balance = Balance::first();
        $balance->decrement('amount', $totalAmount);
        // Pastikan totalAmount adalah angka positif sebelum dikurangi
        if ($totalAmount > 0) {
            // Opsional: Notifikasi Sukses Saldo
            Notification::make()->success()
                ->title('Pembelian Berhasil')
                ->body("Pembelian seharga {$totalAmount} berhasil, saldo berkurang.")
                ->send();
        } else {
             // Opsional: Notifikasi jika total 0 (walaupun seharusnya sudah dihentikan di mutate)
             Notification::make()->warning()
                ->title('Pembelian tersimpan, tetapi Total 0')
                ->send();
                $this->halt();
        }
    }
}