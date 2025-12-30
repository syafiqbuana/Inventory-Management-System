<?php

namespace App\Filament\Resources\IncomeResource\Pages;

use App\Filament\Resources\IncomeResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Balance;
use Filament\Notifications\Notification;

class CreateIncome extends CreateRecord
{
    protected static string $resource = IncomeResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
    {
        $balance = Balance::first();

        if ($data['amount'] <= 0) {
            Notification::make()
                ->title('Jumlah pemasukan tidak boleh minus !')
                ->danger()
                ->send();

            $this->halt();
        }

        if (!$balance) {
            Notification::make()
                ->title('Saldo belum dibuat!')
                ->danger()
                ->send();

            $this->halt(); // hentikan proses create
        }

        return $data;
    }

    protected function afterCreate(): void
    {

        
        $income = $this->record; // income yang baru dibuat

        $balance = Balance::first();
        $balance->amount += $income->amount;
        $balance->save();

        Notification::make()
            ->title("Pemasukan berhasil dicatat")
            ->success()
            ->send();
    }
}
