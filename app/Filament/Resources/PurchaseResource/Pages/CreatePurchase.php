<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Balance;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

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

        if ($balance->amount < $total) {
            Notification::make()->danger()
                ->title('Saldo tidak mencukupi untuk pembelian ini!')
                ->body("Saldo saat ini: Rp" . number_format($balance->amount, 0, ',', '.') . ", tetapi total pembelian adalah: Rp" . number_format($total, 0, ',', '.') . ".")
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
        DB::transaction(function () {

            $totalAmount = $this->record->total_amount;

            if ($totalAmount > 0) {
                $balance = Balance::first();
                $balance->decrement('amount', $totalAmount);

                Notification::make()->success()
                    ->title('Pembelian Berhasil')
                    ->body("Pembelian seharga Rp" . number_format($totalAmount, 0, ',', '.') . " berhasil, saldo berkurang.")
                    ->send();
            } else {
                Notification::make()->warning()
                    ->title('Pembelian tersimpan, tetapi Total 0')
                    ->send();
            }
        });
    }
}